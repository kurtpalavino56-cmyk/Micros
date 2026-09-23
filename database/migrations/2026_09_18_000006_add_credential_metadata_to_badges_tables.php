<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2 — verifiable credential metadata for badges.
 *
 * `badges` keeps every existing column untouched (is_stackable, badge_level,
 * prerequisite_badge_id, pathway_id, is_active) — none of it is repurposed
 * or removed here.
 *
 * `user_badges` (the per-student issuance record) gets a public
 * `credential_uid` distinct from the row id, revocation tracking, and
 * snapshot columns so a badge's displayed content doesn't silently drift if
 * the underlying course/competencies change later.
 *
 * Deliberately NO `stacking_framework_id` on either table: a completed
 * microcredential/badge may contribute to more than one approved framework,
 * so that relationship is computed from `stacking_framework_requirements` +
 * completed enrollments, never stored on the badge itself.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            if (! Schema::hasColumn('badges', 'pqf_level')) {
                $table->string('pqf_level')->nullable()->after('badge_level');
            }
            if (! Schema::hasColumn('badges', 'issuing_institution')) {
                $table->string('issuing_institution')
                    ->default('Pangasinan State University - Lingayen Campus')
                    ->after('pqf_level');
            }
        });

        Schema::table('user_badges', function (Blueprint $table) {
            if (! Schema::hasColumn('user_badges', 'credential_uid')) {
                $table->string('credential_uid', 64)->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('user_badges', 'status')) {
                $table->string('status')->default('active')->after('earned_at'); // active | revoked
            }
            if (! Schema::hasColumn('user_badges', 'revoked_at')) {
                $table->timestamp('revoked_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('user_badges', 'revoked_by')) {
                $table->foreignId('revoked_by')->nullable()->after('revoked_at')
                    ->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('user_badges', 'revocation_reason')) {
                $table->text('revocation_reason')->nullable()->after('revoked_by');
            }
            if (! Schema::hasColumn('user_badges', 'competencies_snapshot')) {
                $table->json('competencies_snapshot')->nullable()->after('revocation_reason');
            }
            if (! Schema::hasColumn('user_badges', 'learning_outcomes_snapshot')) {
                $table->json('learning_outcomes_snapshot')->nullable()->after('competencies_snapshot');
            }
            if (! Schema::hasColumn('user_badges', 'pqf_level_snapshot')) {
                $table->string('pqf_level_snapshot')->nullable()->after('learning_outcomes_snapshot');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_badges', function (Blueprint $table) {
            if (Schema::hasColumn('user_badges', 'revoked_by')) {
                $table->dropConstrainedForeignId('revoked_by');
            }
            foreach ([
                'credential_uid', 'status', 'revoked_at', 'revocation_reason',
                'competencies_snapshot', 'learning_outcomes_snapshot', 'pqf_level_snapshot',
            ] as $column) {
                if (Schema::hasColumn('user_badges', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('badges', function (Blueprint $table) {
            foreach (['pqf_level', 'issuing_institution'] as $column) {
                if (Schema::hasColumn('badges', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
