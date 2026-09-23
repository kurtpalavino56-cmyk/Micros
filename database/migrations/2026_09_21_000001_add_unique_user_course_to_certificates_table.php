<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1 — Credential Lifecycle Integrity.
 *
 * One user, one course, one certificate. Until now only `serial` was
 * unique, so MicrocredentialCompletionService::issueCertificateIfEligible()
 * relied on a check-then-insert that two concurrent requests could both
 * pass. This adds the real backstop the service's QueryException handler
 * was written for.
 *
 * Existing duplicates are NOT deleted automatically: a certificate is an
 * issued credential, and choosing which duplicate to keep (and whether a
 * revoked or active one wins) is a decision for a person. If duplicates
 * exist, this migration stops with the offending pairs listed so they can
 * be resolved first; nothing is changed until then.
 */
return new class extends Migration
{
    private const INDEX = 'certificates_user_id_course_id_unique';

    public function up(): void
    {
        $duplicates = DB::table('certificates')
            ->select('user_id', 'course_id', DB::raw('COUNT(*) as total'))
            ->groupBy('user_id', 'course_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isNotEmpty()) {
            $sample = $duplicates->take(10)
                ->map(fn ($row) => "user_id={$row->user_id}, course_id={$row->course_id} ({$row->total} certificates)")
                ->implode('; ');

            throw new RuntimeException(
                'Cannot add a unique (user_id, course_id) index to certificates: '
                .$duplicates->count().' duplicate pair(s) already exist. '
                .'Resolve them manually first (nothing was changed). First offenders: '.$sample
            );
        }

        if (! Schema::hasIndex('certificates', self::INDEX)) {
            Schema::table('certificates', function (Blueprint $table) {
                $table->unique(['user_id', 'course_id'], self::INDEX);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('certificates', self::INDEX)) {
            Schema::table('certificates', function (Blueprint $table) {
                $table->dropUnique(self::INDEX);
            });
        }
    }
};
