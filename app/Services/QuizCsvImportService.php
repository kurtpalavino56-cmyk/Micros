<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Validation\ValidationException;

class QuizCsvImportService
{
    private const HEADERS = ['type', 'question', 'choice_a', 'choice_b', 'choice_c', 'choice_d', 'correct_answer', 'points'];

    private const QUIK_STAK_HEADER = '# QUIK STAK v1';

    private const QUIK_STAK_ANSWER_KEY = '=== ANSWER KEY ===';

    public function __construct(private QuizManagementService $quizManagement) {}

    /** @return array{valid:list<array{row:int,question:array<string,mixed>}>,duplicates:list<array{row:int,reason:string,question:array<string,mixed>}>,errors:list<array{row:int,reason:string}>,points_normalized:int,pool_size:int,replace_allowed:bool} */
    public function preview(Quiz $quiz, string $contents, bool $quikStak = false): array
    {
        if (str_starts_with($contents, "\xEF\xBB\xBF")) {
            $contents = substr($contents, 3);
        }
        if (trim($contents) === '') {
            throw ValidationException::withMessages(['file' => $quikStak ? 'The QUIK STAK file is empty.' : 'The CSV file is empty.']);
        }
        if (! mb_check_encoding($contents, 'UTF-8')) {
            throw ValidationException::withMessages(['file' => 'The uploaded file must use UTF-8 encoding.']);
        }

        $firstLine = preg_split('/\r\n|\r|\n/', $contents, 2)[0] ?? '';
        if ($quikStak || trim($firstLine) === self::QUIK_STAK_HEADER) {
            return $this->previewQuikStak($quiz, $contents);
        }

        $delimiter = $this->detectDelimiter($contents);
        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw ValidationException::withMessages(['file' => 'The CSV file could not be read.']);
        }
        fwrite($stream, $contents);
        rewind($stream);
        $headers = false;
        $rowNumber = 0;
        while (($headers = fgetcsv($stream, null, $delimiter, '"', '')) !== false) {
            $rowNumber++;
            // Notes ("#...") and blank rows may sit above the header row.
            $isBlank = count(array_filter($headers, fn ($cell): bool => trim((string) $cell) !== '')) === 0;
            if (! $isBlank && ! str_starts_with(trim((string) ($headers[0] ?? '')), '#')) {
                break;
            }
        }
        $headers = array_map(fn ($header): string => mb_strtolower(trim((string) $header)), $headers ?: []);
        $missingHeaders = array_diff(self::HEADERS, $headers);
        if ($missingHeaders !== []) {
            fclose($stream);
            throw ValidationException::withMessages(['file' => 'CSV is missing required columns: '.implode(', ', $missingHeaders).'.']);
        }
        $indices = array_flip($headers);

        $existingQuestions = $quiz->questions()->pluck('question')
            ->map(fn ($question): string => $this->normalizeQuestion((string) $question))
            ->all();
        $seenQuestions = array_fill_keys($existingQuestions, true);
        $valid = [];
        $duplicates = [];
        $errors = [];
        $pointsNormalized = 0;
        $dataRows = 0;

        while (($cells = fgetcsv($stream, null, $delimiter, '"', '')) !== false) {
            $rowNumber++;
            if (str_starts_with(trim((string) ($cells[0] ?? '')), '#')) {
                continue;
            }
            $cells = array_map(fn ($cell): string => trim((string) $cell), $cells);
            if ($cells === [] || count(array_filter($cells, fn ($cell): bool => $cell !== '')) === 0) {
                continue;
            }
            $dataRows++;
            if ($dataRows > 500) {
                fclose($stream);
                throw ValidationException::withMessages(['file' => 'CSV files may contain no more than 500 data rows.']);
            }
            $row = [];
            foreach (self::HEADERS as $header) {
                $row[$header] = trim((string) ($cells[$indices[$header]] ?? ''));
            }

            $formulaCell = $this->formulaInjectionCell($row);
            if ($formulaCell !== null) {
                $reason = $formulaCell === 'correct_answer' && str_starts_with($row[$formulaCell], '-')
                    ? 'Answers starting with "-" must be rephrased to avoid spreadsheet formula injection.'
                    : 'Formula-like value is not allowed in '.$formulaCell.'.';
                $errors[] = ['row' => $rowNumber, 'reason' => $reason];

                continue;
            }

            $type = $this->normalizeType($row['type']);
            if ($type === null) {
                $errors[] = ['row' => $rowNumber, 'reason' => 'Type must be multiple_choice, true_false, or identification.'];

                continue;
            }
            if ($row['question'] === '') {
                $errors[] = ['row' => $rowNumber, 'reason' => 'Question is required.'];

                continue;
            }
            if ($row['correct_answer'] === '') {
                $errors[] = ['row' => $rowNumber, 'reason' => 'Correct answer is required.'];

                continue;
            }

            $points = $this->normalizePoints($row['points']);
            if ($points === null) {
                $errors[] = ['row' => $rowNumber, 'reason' => 'Points must be a whole number.'];

                continue;
            }
            if ($row['points'] !== '' && (int) $row['points'] < 1) {
                $pointsNormalized++;
            }

            $choices = [$row['choice_a'], $row['choice_b'], $row['choice_c'], $row['choice_d']];
            $rawQuestion = ['text' => $row['question'], 'type' => $type, 'points' => $points];
            if ($type === 'Multiple Choice') {
                $blankSeen = false;
                $gapFound = false;
                foreach ($choices as $choice) {
                    if ($choice === '') {
                        $blankSeen = true;
                    } elseif ($blankSeen) {
                        $gapFound = true;
                    }
                }
                if ($gapFound) {
                    $errors[] = ['row' => $rowNumber, 'reason' => 'Choice columns cannot have a blank between filled choices.'];

                    continue;
                }
                $filledChoices = array_values(array_filter($choices, fn ($choice): bool => $choice !== ''));
                if (count($filledChoices) < 2) {
                    $errors[] = ['row' => $rowNumber, 'reason' => 'Multiple choice questions need at least two non-blank choices.'];

                    continue;
                }
                $answer = $row['correct_answer'];
                if (preg_match('/^[A-D]$/i', $answer) === 1) {
                    $correctIndex = ord(mb_strtoupper($answer)) - ord('A');
                    if (($choices[$correctIndex] ?? '') === '') {
                        $errors[] = ['row' => $rowNumber, 'reason' => 'Correct answer letter must refer to a filled choice.'];

                        continue;
                    }
                } else {
                    $correctIndex = array_search($answer, $choices, true);
                    if ($correctIndex === false) {
                        $errors[] = ['row' => $rowNumber, 'reason' => 'Correct answer must match a choice exactly or use its A-D letter.'];

                        continue;
                    }
                }
                $rawQuestion['choices'] = $choices;
                $rawQuestion['correct'] = $correctIndex;
            } elseif ($type === 'True or False') {
                $answer = mb_strtolower($row['correct_answer']);
                if (! in_array($answer, ['true', 'false'], true)) {
                    $errors[] = ['row' => $rowNumber, 'reason' => 'True/false answer must be True or False.'];

                    continue;
                }
                $rawQuestion['tf_correct'] = $answer === 'true' ? 0 : 1;
            } else {
                $rawQuestion['answer'] = $row['correct_answer'];
            }

            $parsed = $this->quizManagement->parseImportedQuestion($rawQuestion);
            if ($parsed === null || $parsed['error'] !== null) {
                $errors[] = ['row' => $rowNumber, 'reason' => $parsed['error'] ?? 'Question could not be parsed.'];

                continue;
            }
            $prepared = [
                'question' => $parsed['question'],
                'type' => $parsed['type'],
                'points' => $points,
                'options' => array_values($parsed['options']),
                'correct_answer' => $parsed['correct_answer'],
            ];
            $this->appendPreparedQuestion($prepared, $rowNumber, $existingQuestions, $seenQuestions, $valid, $duplicates);
        }

        fclose($stream);
        if ($dataRows === 0) {
            throw ValidationException::withMessages(['file' => 'The CSV file has a header but no data rows.']);
        }

        return $this->previewResult($quiz, $valid, $duplicates, $errors, $pointsNormalized);
    }

    /** @return array{valid:list<array{row:int,question:array<string,mixed>}>,duplicates:list<array{row:int,reason:string,question:array<string,mixed>}>,errors:list<array{row:int,reason:string}>,points_normalized:int,pool_size:int,replace_allowed:bool} */
    private function previewQuikStak(Quiz $quiz, string $contents): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $contents) ?: [];
        if (trim($lines[0] ?? '') !== self::QUIK_STAK_HEADER) {
            throw ValidationException::withMessages(['file' => 'A QUIK STAK .txt file must start with "# QUIK STAK v1".']);
        }

        $answerKeyMarkers = [];
        foreach ($lines as $index => $line) {
            if (trim($line) === self::QUIK_STAK_ANSWER_KEY) {
                $answerKeyMarkers[] = $index;
            }
        }
        if ($answerKeyMarkers === []) {
            throw ValidationException::withMessages(['file' => 'QUIK STAK is missing the "'.self::QUIK_STAK_ANSWER_KEY.'" line.']);
        }
        if (count($answerKeyMarkers) > 1) {
            throw ValidationException::withMessages(['file' => 'QUIK STAK must contain only one "'.self::QUIK_STAK_ANSWER_KEY.'" line.']);
        }

        $answerKeyIndex = $answerKeyMarkers[0];
        $questionBlocks = [];
        $currentBlock = [];
        for ($index = 1; $index < $answerKeyIndex; $index++) {
            $line = trim($lines[$index]);
            if ($line === '') {
                if ($currentBlock !== []) {
                    $questionBlocks[] = $currentBlock;
                    $currentBlock = [];
                }

                continue;
            }
            $currentBlock[] = ['line' => $index + 1, 'text' => $line];
        }
        if ($currentBlock !== []) {
            $questionBlocks[] = $currentBlock;
        }
        if ($questionBlocks === []) {
            throw ValidationException::withMessages(['file' => 'QUIK STAK must contain at least one numbered question before the answer key.']);
        }
        if (count($questionBlocks) > 500) {
            throw ValidationException::withMessages(['file' => 'QUIK STAK files may contain no more than 500 questions.']);
        }

        $questions = [];
        $errors = [];
        foreach ($questionBlocks as $blockIndex => $block) {
            $questionLine = $block[0];
            if (preg_match('/^(\d+)\.\s+(.+)$/u', $questionLine['text'], $matches) !== 1) {
                $errors[] = 'Line '.$questionLine['line'].' must start a question using "'.($blockIndex + 1).'. Question text".';

                continue;
            }

            $questionNumber = (int) $matches[1];
            if ($questionNumber !== $blockIndex + 1) {
                $errors[] = 'Line '.$questionLine['line'].' numbers questions sequentially starting at 1; expected question '.($blockIndex + 1).'.';

                continue;
            }

            $choices = [];
            foreach (array_slice($block, 1) as $choiceLine) {
                if (preg_match('/^([a-z])\)\s+(.+)$/iu', $choiceLine['text'], $choiceMatches) !== 1) {
                    $errors[] = 'Line '.$choiceLine['line'].' must be a lettered choice directly under question '.$questionNumber.'.';

                    continue;
                }

                $expectedChoice = chr(ord('a') + count($choices));
                $choiceLetter = mb_strtolower($choiceMatches[1]);
                if ($choiceLetter !== $expectedChoice) {
                    $errors[] = 'Line '.$choiceLine['line'].' must use choice label '.$expectedChoice.') for question '.$questionNumber.'.';

                    continue;
                }

                $choices[] = trim($choiceMatches[2]);
            }
            if ($choices !== [] && count($choices) < 2) {
                $errors[] = 'Question '.$questionNumber.' must have at least two choices.';
            }

            $questions[$questionNumber] = [
                'text' => trim($matches[2]),
                'choices' => $choices,
                'line' => $questionLine['line'],
            ];
        }

        $answerKey = [];
        $answerKeyLineCount = 0;
        for ($index = $answerKeyIndex + 1; $index < count($lines); $index++) {
            $line = trim($lines[$index]);
            if ($line === '') {
                continue;
            }
            $answerKeyLineCount++;

            if (preg_match('/^(\d+)\.\s*(.+)$/u', $line, $matches) !== 1) {
                $errors[] = 'Answer key line '.($index + 1).' must use "number. answer" or "number. answer @points".';

                continue;
            }

            $questionNumber = (int) $matches[1];
            if (! isset($questions[$questionNumber])) {
                $errors[] = 'Answer key line '.($index + 1).' refers to question '.$questionNumber.', but no such question exists.';

                continue;
            }
            if (isset($answerKey[$questionNumber])) {
                $errors[] = 'Answer key contains more than one entry for question '.$questionNumber.'.';

                continue;
            }

            $answerAndPoints = trim($matches[2]);
            $answer = $answerAndPoints;
            $points = '1';
            if (preg_match('/^(.*?)\s+@([^\s]+)$/u', $answerAndPoints, $pointsMatches) === 1) {
                $answer = trim($pointsMatches[1]);
                $points = trim($pointsMatches[2]);
            }
            if ($answer === '') {
                $errors[] = 'Answer key line '.($index + 1).' is missing the answer for question '.$questionNumber.'.';

                continue;
            }

            $normalizedPoints = $this->normalizePoints($points);
            if ($normalizedPoints === null) {
                $errors[] = 'Answer key line '.($index + 1).' points must be a whole number.';

                continue;
            }
            $answerKey[$questionNumber] = [
                'answer' => $answer,
                'points' => $normalizedPoints,
                'points_normalized' => (int) $points < 1,
                'line' => $index + 1,
            ];
        }

        if ($answerKeyLineCount !== count($questions)) {
            $errors[] = 'QUIK STAK has '.count($questions).' questions but '.$answerKeyLineCount.' answer-key lines.';
        }
        foreach (array_keys($questions) as $questionNumber) {
            if (! isset($answerKey[$questionNumber])) {
                $errors[] = 'The answer key is missing an entry for question '.$questionNumber.'.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['file' => implode(' ', $errors)]);
        }

        $valid = [];
        $duplicates = [];
        $pointsNormalized = 0;
        $existingQuestions = $quiz->questions()->pluck('question')
            ->map(fn ($existingQuestion): string => $this->normalizeQuestion((string) $existingQuestion))
            ->all();
        $seenQuestions = array_fill_keys($existingQuestions, true);
        foreach ($questions as $questionNumber => $question) {
            $key = $answerKey[$questionNumber];
            if ($key['points_normalized']) {
                $pointsNormalized++;
            }

            $rawQuestion = [
                'text' => $question['text'],
                'points' => $key['points'],
            ];
            if ($question['choices'] !== []) {
                $answerLetter = mb_strtolower($key['answer']);
                $choiceIndex = preg_match('/^[a-z]$/u', $answerLetter) === 1
                    ? ord($answerLetter) - ord('a')
                    : -1;
                if (! isset($question['choices'][$choiceIndex])) {
                    $lastChoice = chr(ord('a') + count($question['choices']) - 1);
                    $errors[] = 'Answer key line '.$key['line'].' answer must be a choice letter from a to '.$lastChoice.' for question '.$questionNumber.'.';

                    continue;
                }
                $rawQuestion['type'] = 'Multiple Choice';
                $rawQuestion['choices'] = $question['choices'];
                $rawQuestion['correct'] = $choiceIndex;
            } elseif (in_array(mb_strtoupper($key['answer']), ['TRUE', 'FALSE'], true)) {
                $rawQuestion['type'] = 'True or False';
                $rawQuestion['tf_correct'] = mb_strtoupper($key['answer']) === 'TRUE' ? 0 : 1;
            } else {
                $rawQuestion['type'] = 'Identification';
                $rawQuestion['answer'] = $key['answer'];
            }

            $parsed = $this->quizManagement->parseImportedQuestion($rawQuestion);
            if ($parsed === null || $parsed['error'] !== null) {
                $errors[] = 'Question '.$questionNumber.' could not be parsed: '.($parsed['error'] ?? 'unknown error').'.';

                continue;
            }
            $prepared = [
                'question' => $parsed['question'],
                'type' => $parsed['type'],
                'points' => $key['points'],
                'options' => array_values($parsed['options']),
                'correct_answer' => $parsed['correct_answer'],
            ];
            $this->appendPreparedQuestion($prepared, $question['line'], $existingQuestions, $seenQuestions, $valid, $duplicates);
        }
        if ($errors !== []) {
            throw ValidationException::withMessages(['file' => implode(' ', $errors)]);
        }

        return $this->previewResult($quiz, $valid, $duplicates, [], $pointsNormalized);
    }

    /**
     * @param  array<string, mixed>  $prepared
     * @param  list<string>  $existingQuestions
     * @param  list<array{row:int,question:array<string,mixed>}>  $valid
     * @param  list<array{row:int,reason:string,question:array<string,mixed>}>  $duplicates
     */
    private function appendPreparedQuestion(array $prepared, int $row, array $existingQuestions, array &$seenQuestions, array &$valid, array &$duplicates): void
    {
        $normalizedText = $this->normalizeQuestion($prepared['question']);
        if (isset($seenQuestions[$normalizedText])) {
            $reason = in_array($normalizedText, $existingQuestions, true)
                ? 'Question matches text already in this quiz.'
                : 'Question duplicates an earlier row in this file.';
            $duplicates[] = ['row' => $row, 'reason' => $reason, 'question' => $prepared];

            return;
        }

        $seenQuestions[$normalizedText] = true;
        $valid[] = ['row' => $row, 'question' => $prepared];
    }

    /**
     * @param  list<array{row:int,question:array<string,mixed>}>  $valid
     * @param  list<array{row:int,reason:string,question:array<string,mixed>}>  $duplicates
     * @param  list<array{row:int,reason:string}>  $errors
     * @return array{valid:list<array{row:int,question:array<string,mixed>}>,duplicates:list<array{row:int,reason:string,question:array<string,mixed>}>,errors:list<array{row:int,reason:string}>,points_normalized:int,pool_size:int,replace_allowed:bool}
     */
    private function previewResult(Quiz $quiz, array $valid, array $duplicates, array $errors, int $pointsNormalized): array
    {
        return [
            'valid' => $valid,
            'duplicates' => $duplicates,
            'errors' => $errors,
            'points_normalized' => $pointsNormalized,
            'pool_size' => $quiz->questions()->count(),
            'replace_allowed' => ! QuizAttempt::query()->where('quiz_id', $quiz->id)->exists(),
        ];
    }

    private function detectDelimiter(string $contents): string
    {
        // Detect from the header row, not a leading "# note" line: a note is
        // a single cell, so it would tie and wrongly fall back to commas for
        // a file a spreadsheet re-saved with semicolons.
        $line = $contents;
        foreach (preg_split('/\r\n|\r|\n/', $contents) ?: [] as $candidate) {
            $firstCell = ltrim($candidate, " \t\"");
            if (trim($candidate) !== '' && ! str_starts_with($firstCell, '#')) {
                $line = $candidate;
                break;
            }
        }
        $commaCount = count(str_getcsv($line, ',', '"', ''));
        $semicolonCount = count(str_getcsv($line, ';', '"', ''));

        return $semicolonCount > $commaCount ? ';' : ',';
    }

    /** @param array<string, string> $row */
    private function formulaInjectionCell(array $row): ?string
    {
        foreach ($row as $column => $value) {
            if ($value === '') {
                continue;
            }
            if ($column === 'points' && str_starts_with($value, '-')) {
                continue;
            }
            if (preg_match('/^[=+@]/u', $value) === 1) {
                return $column;
            }
            if (str_starts_with($value, '-')) {
                return $column;
            }
        }

        return null;
    }

    private function normalizeType(string $type): ?string
    {
        $key = str_replace([' ', '-'], '_', mb_strtolower(trim($type)));

        return match ($key) {
            'multiple_choice' => 'Multiple Choice',
            'true_false', 'true_or_false' => 'True or False',
            'identification' => 'Identification',
            default => null,
        };
    }

    private function normalizeQuestion(string $question): string
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim($question)) ?? trim($question));
    }

    private function normalizePoints(string $points): ?int
    {
        if ($points === '') {
            return 1;
        }
        if (filter_var($points, FILTER_VALIDATE_INT) === false) {
            return null;
        }

        $points = (int) $points;

        return $points < 1 ? 1 : $points;
    }
}
