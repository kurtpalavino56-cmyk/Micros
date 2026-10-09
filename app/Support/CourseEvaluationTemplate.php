<?php

namespace App\Support;

class CourseEvaluationTemplate
{
    public const MINIMUM_REVIEWS_FOR_RATING = 3;

    /** @return array{overall: string, course: array<string, string>, platform: array<string, string>} */
    public static function questions(): array
    {
        return [
            'overall' => 'Overall, how would you rate this course?',
            'course' => [
                'materials' => 'The learning materials were relevant and easy to understand.',
                'instructions' => 'The instructions were clear.',
                'assessments' => 'Activities and assessments supported my learning.',
                'skills' => 'I gained useful knowledge or skills.',
                'recommend' => 'I would recommend this course.',
            ],
            'platform' => [
                'navigation' => 'The platform was easy to navigate.',
                'progress' => 'Platform features helped me track my progress.',
            ],
        ];
    }
}
