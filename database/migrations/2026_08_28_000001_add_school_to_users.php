<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * users.school — the last school, college or university the learner
 * attended, collected on the About Me profile-completion step.
 *
 * Stored as a plain string rather than a foreign key to a schools table:
 * the picker offers a fixed list plus an "Other" option with a free-text
 * box, so an institution that is not on the list still records cleanly and
 * editing the list later never orphans a user row.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'school')) {
                $table->string('school')->nullable()->after('education');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'school')) {
                $table->dropColumn('school');
            }
        });
    }
};
