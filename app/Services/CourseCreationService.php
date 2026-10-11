<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Course;
use App\Models\User;
use App\Support\RichTextSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseCreationService
{
    public function create(Request $request, array $data, User $author): Course
    {
        $title = trim($data['title'] ?? '') ?: 'Untitled Course';
        $course = Course::create([
            'title' => $title,
            'short_description' => trim($data['short_description'] ?? ''),
            'heading' => null,
            'subheading' => null,
            'slug' => $this->uniqueSlug($title),
            'description' => RichTextSanitizer::sanitize($data['description'] ?? ''),
            'skills' => array_values($data['competencies'] ?? []),
            'objectives' => $this->linesToArray($data['learning_objectives'] ?? null),
            'category' => $data['category'] ?? null,
            'program' => $data['program'] ?? null,
            'level' => $data['level'] ?? 'Beginner',
            'pqf_level' => $data['pqf_level'] ?? null,
            'target_learners' => trim((string) ($data['target_learners'] ?? '')) ?: null,
            'delivery_mode' => $data['delivery_mode'] ?? null,
            'learning_hours' => filled($data['learning_hours'] ?? null) ? (int) $data['learning_hours'] : null,
            'assessment_strategy' => trim((string) ($data['assessment_strategy'] ?? '')) ?: null,
            'grading_rubric' => trim((string) ($data['grading_rubric'] ?? '')) ?: null,
            'credit_bearing' => $request->boolean('credit_bearing'),
            'credit_equivalency' => trim((string) ($data['credit_equivalency'] ?? '')) ?: null,
            'equivalent_course' => trim((string) ($data['equivalent_course'] ?? '')) ?: null,
            'duration' => ((int) ($data['duration'] ?? 0)) > 0 ? ((int) $data['duration']).'h' : null,
            'passing_score' => (int) ($data['passing_score'] ?? 75),
            'instructor' => $author->name,
            'created_by' => $author->id,
            'is_featured' => false,
            'thumbnail_url' => $this->storeThumbnail($request),
            'is_published' => false,
            'approval_status' => 'draft',
            'is_approved' => false,
        ]);

        $course->related_skills = $this->relatedSkillsFrom($request);
        $course->prerequisite_ids = $this->prerequisiteIdsFrom($request);
        $course->certificate_enabled = true;
        $course->certificate_title = 'Certificate of Completion';
        $course->certificate_mode = 'auto';
        $course->certificate_signature_name = $author->name;
        $course->badge_id = $this->saveInlineBadge($request, $course);

        // Completion Requirements — see MicrocredentialCompletionService.
        // A genuine, honored completion gate: when set, official completion
        // (and therefore badge/certificate issuance) additionally requires
        // the assigned faculty member to verify the student, on top of the
        // always-required academic-unit confirmation. Read with boolean()
        // for the same reason as certificate_enabled above — a checked
        // HTML checkbox posts "on"/"1", which the request's own array
        // never carries as a proper bool.
        $course->requires_faculty_verification = $request->boolean('requires_faculty_verification');
        $course->save();

        return $course;
    }

    private function saveInlineBadge(Request $request, Course $course): ?int
    {
        if (! $request->boolean('badge_enabled')) {
            return null;
        }
        $name = trim((string) $request->input('badge_name'));
        if ($name === '') {
            throw new \InvalidArgumentException('Badge name is required when badge issuance is enabled.');
        }
        $badge = $course->badge ?: new Badge;
        $badge->name = $name;
        $badge->description = trim((string) $request->input('badge_description')) ?: null;
        $badge->badge_level = match (strtolower(trim((string) $course->level))) {
            'beginner' => 'Beginner',
            'intermediate' => 'Intermediate',
            'advanced' => 'Advanced',
            default => 'Beginner',
        };
        $badge->is_active = true;
        $icon = (string) $request->input('badge_icon_base64');
        if ($icon !== '' && str_starts_with($icon, 'data:image/')) {
            $badge->icon_url = $icon;
        }
        $badge->save();

        return (int) $badge->id;
    }

    private function storeThumbnail(Request $request): ?string
    {
        if (! $request->hasFile('thumbnail') || ! $request->file('thumbnail')->isValid()) {
            return null;
        }

        $directory = public_path('uploads/thumbnails');
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $name = uniqid('thumb_').'.'.strtolower($request->file('thumbnail')->getClientOriginalExtension());
        $request->file('thumbnail')->move($directory, $name);

        return 'uploads/thumbnails/'.$name;
    }

    /** @return list<string>|null */
    private function linesToArray(?string $text): ?array
    {
        $items = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', (string) $text))));

        return $items === [] ? null : $items;
    }

    /** @return list<int> */
    private function prerequisiteIdsFrom(Request $request): array
    {
        return collect((array) $request->input('prerequisite_ids', []))
            ->map(fn ($value): int => (int) $value)
            ->filter(fn (int $value): bool => $value > 0)
            ->unique()
            ->values()
            ->all();
    }

    /** @return list<string> */
    private function relatedSkillsFrom(Request $request): array
    {
        $skills = collect((array) $request->input('related_skills', []))
            ->map(fn ($skill): string => trim((string) $skill))
            ->filter()
            ->values()
            ->all();
        $csv = preg_split('/[,\r\n]+/', (string) $request->input('related_skills_csv', ''));

        return array_values(array_unique(array_merge($skills, array_filter(array_map('trim', $csv)))));
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'course';
        $slug = $base;
        $suffix = 2;

        while (Course::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
