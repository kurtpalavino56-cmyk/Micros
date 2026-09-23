<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2 — revocation status + issuance-time snapshots for certificates.
 *
 * Existing columns (serial, file_path, title, issued_at, etc.) are
 * untouched. The snapshot columns preserve what was true about the
 * microcredential AT THE TIME the certificate was issued (title, learning
 * outcomes, competencies, PQF level, credit equivalency, learning hours) so
 * later edits/versioning of the course never alter an already-issued
 * certificate. Actual PDF generation/download routes are a Phase 5 concern;
 * this migration only adds the columns Phase 5 will read/write.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            if (! Schema::hasColumn('certificates', 'status')) {
                $table->string('status')->default('active')->after('issued_at'); // active | revoked
            }
            if (! Schema::hasColumn('certificates', 'revoked_at')) {
                $table->timestamp('revoked_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('certificates', 'revoked_by')) {
                $table->foreignId('revoked_by')->nullable()->after('revoked_at')
                    ->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('certificates', 'revocation_reason')) {
                $table->text('revocation_reason')->nullable()->after('revoked_by');
            }
            if (! Schema::hasColumn('certificates', 'microcredential_title_snapshot')) {
                $table->string('microcredential_title_snapshot')->nullable()->after('revocation_reason');
            }
            if (! Schema::hasColumn('certificates', 'learning_outcomes_snapshot')) {
                $table->json('learning_outcomes_snapshot')->nullable()->after('microcredential_title_snapshot');
            }
            if (! Schema::hasColumn('certificates', 'competencies_snapshot')) {
                $table->json('competencies_snapshot')->nullable()->after('learning_outcomes_snapshot');
            }
            if (! Schema::hasColumn('certificates', 'pqf_level_snapshot')) {
                $table->string('pqf_level_snapshot')->nullable()->after('competencies_snapshot');
            }
            if (! Schema::hasColumn('certificates', 'credit_equivalency_snapshot')) {
                $table->string('credit_equivalency_snapshot')->nullable()->after('pqf_level_snapshot');
            }
            if (! Schema::hasColumn('certificates', 'learning_hours_snapshot')) {
                $table->unsignedInteger('learning_hours_snapshot')->nullable()->after('credit_equivalency_snapshot');
            }
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            if (Schema::hasColumn('certificates', 'revoked_by')) {
                $table->dropConstrainedForeignId('revoked_by');
            }
            foreach ([
                'status', 'revoked_at', 'revocation_reason', 'microcredential_title_snapshot',
                'learning_outcomes_snapshot', 'competencies_snapshot', 'pqf_level_snapshot',
                'credit_equivalency_snapshot', 'learning_hours_snapshot',
            ] as $column) {
                if (Schema::hasColumn('certificates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
