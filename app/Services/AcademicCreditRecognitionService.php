<?php

namespace App\Services;

use App\Models\AcademicCreditRecognition;
use App\Models\StackingFramework;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AcademicCreditRecognitionService
{
    public function __construct(private StackingProgressService $stackingProgress) {}

    /**
     * A student is eligible only when the active approved framework is in a
     * genuine requirements_met state and the framework has some credit
     * recognition configuration. This does not create a record or award any
     * credit; it simply states the framework is eligible for the explicit
     * recognition request workflow.
     */
    public function canRequestRecognition(User $user, StackingFramework $framework): bool
    {
        if ($framework->status !== 'approved' || ! $framework->is_active) {
            return false;
        }

        $progress = $this->stackingProgress->syncFrameworkForUser($framework, (int) $user->id);

        if (! $progress || $progress->status !== 'requirements_met') {
            return false;
        }

        $hasRecognitionConfig = collect([
            $framework->equivalent_course,
            $framework->equivalent_units,
            $framework->credit_equivalency,
            $framework->credit_recognition_conditions,
            $framework->target_recognition,
        ])->some(fn ($value) => filled($value));

        return $hasRecognitionConfig;
    }

    public function requestRecognition(User $user, StackingFramework $framework, ?string $notes = null): AcademicCreditRecognition
    {
        if (! $this->canRequestRecognition($user, $framework)) {
            throw new InvalidArgumentException('Student is not eligible to request academic credit recognition for this framework.');
        }

        $record = AcademicCreditRecognition::query()
            ->where('user_id', $user->id)
            ->where('stacking_framework_id', $framework->id)
            ->first();

        if ($record && ! in_array($record->status, ['denied'], true)) {
            throw new InvalidArgumentException('This student already has an active academic credit recognition request for this framework.');
        }

        if (! $record) {
            return DB::transaction(function () use ($user, $framework, $notes) {
                return AcademicCreditRecognition::create([
                    'user_id' => $user->id,
                    'stacking_framework_id' => $framework->id,
                    'status' => 'pending',
                    'notes' => $notes,
                ]);
            });
        }

        $record->status = 'pending';
        $record->notes = $notes ?? $record->notes;
        $record->unit_reviewed_by = null;
        $record->unit_reviewed_at = null;
        $record->unit_recommendation = null;
        $record->dean_endorsed_by = null;
        $record->dean_endorsed_at = null;
        $record->registrar_recorded_by = null;
        $record->registrar_recorded_at = null;
        $record->save();

        return $record->fresh();
    }

    public function recommend(int $recognitionId, int $reviewerId, ?string $recommendation = null): AcademicCreditRecognition
    {
        $record = AcademicCreditRecognition::findOrFail($recognitionId);

        if ($record->status !== 'pending') {
            throw new InvalidArgumentException('Invalid transition: pending -> unit_recommended is required.');
        }

        $record->status = 'unit_recommended';
        $record->unit_reviewed_by = $reviewerId;
        $record->unit_reviewed_at = Carbon::now();
        $record->unit_recommendation = $recommendation ?? $record->unit_recommendation ?? 'Recommended for credit recognition.';
        $record->save();

        return $record->fresh();
    }

    public function endorse(int $recognitionId, int $reviewerId, ?string $notes = null): AcademicCreditRecognition
    {
        $record = AcademicCreditRecognition::findOrFail($recognitionId);

        if ($record->status !== 'unit_recommended') {
            throw new InvalidArgumentException('Invalid transition: unit_recommended -> dean_endorsed is required.');
        }

        $record->status = 'dean_endorsed';
        $record->dean_endorsed_by = $reviewerId;
        $record->dean_endorsed_at = Carbon::now();
        $record->notes = $notes ?? $record->notes;
        $record->save();

        return $record->fresh();
    }

    public function recordRegistrar(int $recognitionId, int $registrarId, ?string $notes = null): AcademicCreditRecognition
    {
        $record = AcademicCreditRecognition::findOrFail($recognitionId);

        if ($record->status !== 'dean_endorsed') {
            throw new InvalidArgumentException('Invalid transition: dean_endorsed -> registrar_recorded is required.');
        }

        $record->status = 'registrar_recorded';
        $record->registrar_recorded_by = $registrarId;
        $record->registrar_recorded_at = Carbon::now();
        $record->notes = $notes ?? $record->notes;
        $record->save();

        return $record->fresh();
    }

    public function deny(int $recognitionId, int $reviewerId, string $reason): AcademicCreditRecognition
    {
        $record = AcademicCreditRecognition::findOrFail($recognitionId);

        if (! in_array($record->status, ['pending', 'unit_recommended', 'dean_endorsed'], true)) {
            throw new InvalidArgumentException('Invalid transition: denial is only allowed while the record is pending or under review.');
        }

        $record->status = 'denied';
        $record->notes = $reason;
        $record->unit_reviewed_by ??= $reviewerId;
        $record->unit_reviewed_at ??= Carbon::now();
        $record->save();

        return $record->fresh();
    }
}
