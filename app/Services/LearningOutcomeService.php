<?php

namespace App\Services;

use App\Models\Course;
use App\Models\LearningOutcome;

class LearningOutcomeService
{
    /** @param list<array{description: string, competency_unit_id?: int|null}> $outcomes */
    public function sync(Course $course, array $outcomes): void
    {
        $outcomes = collect($outcomes)
            ->map(fn (array $outcome): array => [
                'description' => trim((string) ($outcome['description'] ?? '')),
                'competency_unit_id' => filled($outcome['competency_unit_id'] ?? null)
                    ? (int) $outcome['competency_unit_id']
                    : null,
            ])
            ->filter(fn (array $outcome): bool => $outcome['description'] !== '')
            ->values();

        $existing = $course->learningOutcomes()->get()->keyBy('code');

        foreach ($outcomes as $index => $data) {
            $code = 'LO'.($index + 1);
            $outcome = $existing->get($code) ?? new LearningOutcome;
            $outcome->course_id = $course->id;
            $outcome->code = $code;
            $outcome->description = $data['description'];
            $outcome->competency_unit_id = $data['competency_unit_id'];
            $outcome->order = $index + 1;
            $outcome->save();
            $existing->forget($code);
        }

        // Outcomes absent from the submitted list were explicitly removed by
        // faculty; delete those rows and their competency mappings together.
        $existing->each(fn (LearningOutcome $outcome) => $outcome->delete());
    }
}
