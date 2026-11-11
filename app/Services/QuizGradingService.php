<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Support\Collection;

class QuizGradingService
{
    /**
     * Grade the submitted answers against the quiz questions stored on the server.
     *
     * The client may submit only question IDs and selected option letters. A
     * client-supplied score is intentionally ignored because it is not evidence
     * of what the student actually answered.
     *
     * $questions is the set the student was served (see
     * QuizAttemptService::presentationFor()). When omitted, the quiz's
     * default set is used, which for a one-set quiz is every question.
     *
     * Choice shuffling never affects grading: the student submits the
     * ORIGINAL position letter of the chosen option, whatever its position
     * on screen, and it is compared with the original position of
     * correct_answer below.
     *
     * @param  array<string, mixed>  $result
     * @param  Collection<int, QuizQuestion>|null  $questions
     * @return array{correct:int,total:int,earned_points:int,possible_points:int,score:int,passed:bool,answers:array<string,array{answer:string|null,correct:bool,correct_answer:string|null}>}
     */
    public function grade(Quiz $quiz, array $result, ?Collection $questions = null): array
    {
        $questions = ($questions ?? $quiz->questionsForSet())->values();
        $total = $questions->count();
        $submitted = is_array($result['answers'] ?? null) ? $result['answers'] : [];
        $correct = 0;
        $earnedPoints = 0;
        $possiblePoints = 0;
        $answers = [];

        foreach ($questions as $question) {
            $points = $question->effectivePoints();
            $possiblePoints += $points;
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
            if (($question->type ?? null) === 'Identification') {
                $selectedAnswer = trim((string) ($submitted[$questionId] ?? ''));
                $isCorrect = $selectedAnswer !== ''
                    && mb_strtolower(preg_replace('/\s+/', ' ', $selectedAnswer))
                        === mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $question->correct_answer)));
                $selected = $selectedAnswer;
            } else {
                $isCorrect = $selected !== null && $correctLetter !== null && $selected === $correctLetter;
            }

            if ($isCorrect) {
                $correct++;
                $earnedPoints += $points;
            }

            $answers[$questionId] = [
                'answer' => $selected,
                'correct' => $isCorrect,
                'correct_answer' => $correctLetter,
            ];
        }

        $score = $possiblePoints > 0 ? (int) round(($earnedPoints / $possiblePoints) * 100) : 0;

        return [
            'correct' => $correct,
            'total' => $total,
            'earned_points' => $earnedPoints,
            'possible_points' => $possiblePoints,
            'score' => $score,
            'passed' => $score >= (int) $quiz->passing_score,
            'answers' => $answers,
        ];
    }
}
