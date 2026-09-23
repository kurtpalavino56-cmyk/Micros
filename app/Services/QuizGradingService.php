<?php

namespace App\Services;

use App\Models\Quiz;

class QuizGradingService
{
    /**
     * Grade the submitted answers against the quiz questions stored on the server.
     *
     * The client may submit only question IDs and selected option letters. A
     * client-supplied score is intentionally ignored because it is not evidence
     * of what the student actually answered.
     *
     * @param array<string, mixed> $result
     * @return array{correct:int,total:int,score:int,passed:bool,answers:array<string,array{answer:string|null,correct:bool,correct_answer:string|null}>}
     */
    public function grade(Quiz $quiz, array $result): array
    {
        $questions = $quiz->questions->values();
        $total = $questions->count();
        $submitted = is_array($result['answers'] ?? null) ? $result['answers'] : [];
        $correct = 0;
        $answers = [];

        foreach ($questions as $question) {
            $questionId = (string) $question->id;
            $selected = isset($submitted[$questionId])
                ? strtoupper(trim((string) $submitted[$questionId]))
                : null;

            $options = collect($question->options ?? [])->values();
            $correctPosition = $question->correct_answer === null
                ? false
                : $options->search($question->correct_answer, true);
            $correctLetter = $correctPosition === false
                ? null
                : chr(65 + (int) $correctPosition);
            $isCorrect = $selected !== null && $correctLetter !== null && $selected === $correctLetter;

            if ($isCorrect) {
                $correct++;
            }

            $answers[$questionId] = [
                'answer' => $selected,
                'correct' => $isCorrect,
                'correct_answer' => $correctLetter,
            ];
        }

        $score = $total > 0 ? (int) round(($correct / $total) * 100) : 0;

        return [
            'correct' => $correct,
            'total' => $total,
            'score' => $score,
            'passed' => $score >= (int) $quiz->passing_score,
            'answers' => $answers,
        ];
    }
}
