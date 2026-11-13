<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Validation\ValidationException;

class QuizCsvImportService
{
    private const HEADERS = ['type', 'question', 'choice_a', 'choice_b', 'choice_c', 'choice_d', 'correct_answer', 'points'];

    public function __construct(private QuizManagementService $quizManagement) {}

    /** @return array{valid:list<array{row:int,question:array<string,mixed>}>,duplicates:list<array{row:int,reason:string,question:array<string,mixed>}>,errors:list<array{row:int,reason:string}>,points_normalized:int,pool_size:int,replace_allowed:bool} */
    public function preview(Quiz $quiz, string $contents): array
    {
        if (str_starts_with($contents, "\xEF\xBB\xBF")) {
            $contents = substr($contents, 3);
        }
        if (trim($contents) === '') {
            throw ValidationException::withMessages(['file' => 'The CSV file is empty.']);
        }
        if (! mb_check_encoding($contents, 'UTF-8')) {
            throw ValidationException::withMessages(['file' => 'The CSV file must use UTF-8 encoding.']);
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
            if (! str_starts_with(trim((string) ($headers[0] ?? '')), '#')) {
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

        $existingQuestions = $quiz->questions()->pluck('question')->map(fn ($question): string => $this->normalizeQuestion((string) $question))->all();
        $seen = array_fill_keys($existingQuestions, true);
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
            $normalizedText = $this->normalizeQuestion($prepared['question']);
            if (isset($seen[$normalizedText])) {
                $reason = in_array($normalizedText, $existingQuestions, true)
                    ? 'Question matches text already in this quiz.'
                    : 'Question duplicates an earlier row in this file.';
                $duplicates[] = ['row' => $rowNumber, 'reason' => $reason, 'question' => $prepared];

                continue;
            }

            $seen[$normalizedText] = true;
            $valid[] = ['row' => $rowNumber, 'question' => $prepared];
        }

        fclose($stream);
        if ($dataRows === 0) {
            throw ValidationException::withMessages(['file' => 'The CSV file has a header but no data rows.']);
        }

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
