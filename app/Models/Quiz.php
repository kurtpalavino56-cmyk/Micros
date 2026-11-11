<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Quiz extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'course_id',
        'module_id',
        'lesson_id',
        'is_summative',
        'module_order',
        'title',
        'description',
        'passing_score',
        'attempts',
        'questions_per_attempt',
        'time_limit',
        'shuffle_questions',
        'shuffle_choices',
        'instructions',
        'is_active',
        'sort_order',
        'questions_changed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_summative' => 'boolean',
            'shuffle_questions' => 'boolean',
            'shuffle_choices' => 'boolean',
            'questions_per_attempt' => 'integer',
            'questions_changed_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'module_id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'lesson_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class);
    }

    public function sets(): HasMany
    {
        return $this->hasMany(QuizSet::class)->orderBy('position')->orderBy('id');
    }

    /**
     * Questions of one set, in authored order.
     *
     * With no set given this returns the default (first) set. Quizzes that
     * predate sets, or whose questions were created without one, are treated
     * as a single implicit set made of every question, so they behave as before.
     *
     * @return Collection<int, QuizQuestion>
     */
    public function questionsForSet(QuizSet|int|null $set = null): Collection
    {
        $questions = $this->relationLoaded('questions') ? $this->questions : $this->questions()->get();
        $ordered = $questions->sortBy([['position', 'asc'], ['id', 'asc']])->values();
        $setIds = $ordered->pluck('quiz_set_id')->filter()->unique()->values();

        if ($setIds->count() <= 1) {
            return $ordered;
        }

        $setId = $set instanceof QuizSet ? $set->id : $set;
        if ($setId === null) {
            $setId = $this->orderedSets()->first()?->id ?? $setIds->first();
        }

        return $ordered->where('quiz_set_id', (int) $setId)->values();
    }

    /**
     * Number of questions a student is served (the per-set count). Uses the
     * loaded questions relation when present, so list pages add no queries.
     */
    public function questionsPerSetCount(): int
    {
        $questions = $this->relationLoaded('questions') ? $this->questions : $this->questions()->get(['id', 'quiz_set_id']);
        if ($questions->isEmpty()) {
            return 0;
        }

        return (int) $questions->groupBy(fn (QuizQuestion $question) => (int) $question->quiz_set_id)->max(fn ($group) => $group->count());
    }

    /** Number of sets that actually hold questions (an empty quiz counts as one set). */
    public function setCount(): int
    {
        $questions = $this->relationLoaded('questions') ? $this->questions : $this->questions()->get(['id', 'quiz_set_id']);

        return max(1, $questions->pluck('quiz_set_id')->filter()->unique()->count());
    }

    /** @return Collection<int, QuizSet> */
    public function orderedSets(): Collection
    {
        return $this->relationLoaded('sets') ? $this->sets : $this->sets()->get();
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
