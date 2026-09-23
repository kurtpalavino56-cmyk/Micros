<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseCreationService
{
    public function create(Request $request, array $data, User $author): Course
    {
        $title = trim($data['title'] ?? '') ?: 'Untitled Course';
        $course = Course::create([
            'title' => $title,
            'heading' => null,
            'subheading' => null,
            'slug' => $this->uniqueSlug($title),
            'description' => $this->sanitizeRichText($data['description'] ?? ''),
            'skills' => array_values($data['competencies'] ?? []),
            'objectives' => $this->linesToArray($data['learning_objectives'] ?? null),
            'category' => $data['category'] ?? null,
            'program' => $data['program'] ?? null,
            'level' => $data['level'] ?? 'Beginner',
            'pqf_level' => $data['pqf_level'] ?? null,
            'duration' => ((int) ($data['duration'] ?? 0)) > 0 ? ((int) $data['duration']).'h' : null,
            'passing_score' => (int) ($data['passing_score'] ?? 75),
            'instructor' => $author->name,
            'created_by' => $author->id,
            'is_featured' => false,
            'thumbnail_url' => $this->storeThumbnail($request),
            'is_published' => false,
            'approval_status' => ($data['status'] ?? 'submit') === 'draft' ? 'draft' : 'pending',
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

    private function sanitizeRichText(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') return '';
        $allowed = '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><h4><blockquote><a><img>';
        $html = strip_tags($html, $allowed);
        $html = preg_replace_callback('/<([a-z0-9]+)\b([^>]*)>/i', function ($match) {
            $tag = strtolower($match[1]);
            $attrs = $match[2] ?? '';
            $allowedAttrs = match ($tag) {
                'a' => ['href', 'target', 'rel', 'title'],
                'img' => ['src', 'alt', 'title', 'width', 'height'],
                default => [],
            };
            if ($allowedAttrs === []) return '<'.$tag.'>';
            preg_match_all('/([a-zA-Z_:][-a-zA-Z0-9_:.]*)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/u', $attrs, $parts, PREG_SET_ORDER);
            $safe=[];
            foreach ($parts as $part) {
                $name=strtolower($part[1]);
                if (!in_array($name,$allowedAttrs,true)) continue;
                $value=$part[2]!==''?$part[2]:($part[3]!==''?$part[3]:$part[4]);
                if (in_array($name,['href','src'],true)) {
                    $value=trim($value);
                    if (preg_match('/^(javascript|vbscript|data):/i',$value)) continue;
                    if ($name==='src' && !preg_match('/^(https?:\/\/|\/)/i',$value)) continue;
                }
                $safe[]=$name.'="'.e($value).'"';
            }
            return '<'.$tag.($safe?' '.implode(' ',$safe):'').'>';
        }, $html);
        return trim($html);
    }

    private function saveInlineBadge(Request $request, Course $course): ?int
    {
        if (! $request->boolean('badge_enabled')) return null;
        $name = trim((string) $request->input('badge_name'));
        if ($name === '') throw new \InvalidArgumentException('Badge name is required when badge issuance is enabled.');
        $badge = $course->badge ?: new \App\Models\Badge;
        $badge->name = $name;
        $badge->description = trim((string) $request->input('badge_description')) ?: null;
        $badge->badge_level = trim((string) $request->input('badge_level')) ?: null;
        $badge->is_active = true;
        $icon = (string) $request->input('badge_icon_base64');
        if ($icon !== '' && str_starts_with($icon, 'data:image/')) $badge->icon_url = $icon;
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
