<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Quiz;
use Illuminate\Http\Request;

class QuizManagementService
{
    public function save(Request $request, Course $course, CourseModule $module): bool
    {
        $passing = (int) $request->input('passing_score', 0);
        if ($passing < 1 || $passing > 100) {
            $passing = 75;
        }

        $questions = $this->parseQuestions($request->input('questions', []));
        $quiz = Quiz::updateOrCreate(
            ['module_id' => $module->id],
            [
                'course_id' => $course->id,
                'title' => trim($request->input('quiz_title', '')) ?: 'Untitled Quiz',
                'passing_score' => $passing,
                'attempts' => $request->input('attempts'),
                'time_limit' => (int) $request->input('time_limit', 0),
                'instructions' => trim($request->input('instructions', '')),
                'is_active' => true,
            ]
        );

        $existing = $quiz->questions()->orderBy('id')->get()
            ->map(fn ($question): array => [
                'question' => (string) $question->question,
                'type' => (string) $question->type,
                'points' => (int) $question->points,
                'options' => array_values((array) ($question->options ?? [])),
                'correct_answer' => $question->correct_answer,
            ])->all();
        $incoming = array_map(fn (array $question): array => [
            'question' => (string) $question['question'],
            'type' => (string) $question['type'],
            'points' => (int) $question['points'],
            'options' => array_values((array) $question['options']),
            'correct_answer' => $question['correct_answer'],
        ], $questions);
        $questionsChanged = $existing !== $incoming;

        $quiz->questions()->delete();
        foreach ($questions as $question) {
            $quiz->questions()->create($question);
        }

        if ($questionsChanged || ! $quiz->questions_changed_at) {
            $quiz->questions_changed_at = now();
        }

        $quiz->save();
        $quiz->touch();

        return $questionsChanged;
    }

    public function delete(CourseModule $module): ?string
    {
        $quiz = $module->quiz;
        if (! $quiz) {
            return null;
        }

        $title = $quiz->title;
        $quiz->delete();

        return $title;
    }

    /** @param array<int, array<string, mixed>> $rawQuestions */
    private function parseQuestions(array $rawQuestions): array
    {
        $questions = [];
        foreach ($rawQuestions as $rawQuestion) {
            $text = trim($rawQuestion['text'] ?? '');
            if ($text === '') {
                continue;
            }

            $type = $rawQuestion['type'] ?? 'Multiple Choice';
            if ($type === 'Identification') {
                $choices = [];
                $correct = trim($rawQuestion['answer'] ?? '');
            } elseif ($type === 'True or False') {
                $choices = ['True', 'False'];
                $correct = isset($rawQuestion['tf_correct']) && $rawQuestion['tf_correct'] !== null
                    ? ($choices[(int) $rawQuestion['tf_correct']] ?? null)
                    : null;
            } else {
                $choices = array_values(array_filter(array_map('trim', $rawQuestion['choices'] ?? [])));
                $correct = isset($rawQuestion['correct']) && $rawQuestion['correct'] !== null && $rawQuestion['correct'] !== ''
                    ? ($choices[(int) $rawQuestion['correct']] ?? null)
                    : null;
            }

            $questions[] = [
                'question' => $text,
                'type' => $type,
                'points' => (int) ($rawQuestion['points'] ?? 0),
                'options' => $choices,
                'correct_answer' => $correct,
            ];
        }

        return $questions;
    }
}
