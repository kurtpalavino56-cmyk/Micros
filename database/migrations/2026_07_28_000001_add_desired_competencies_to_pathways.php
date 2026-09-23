<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * pathways.desired_competencies (JSON): the full list of skills a pathway
 * requires. The My Pathways page compares it against what each student has
 * actually completed to compute current/missing competencies, readiness %,
 * and personalised course recommendations.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pathways', function (Blueprint $table) {
            if (! Schema::hasColumn('pathways', 'desired_competencies')) {
                $table->json('desired_competencies')->nullable()->after('desired_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pathways', function (Blueprint $table) {
            if (Schema::hasColumn('pathways', 'desired_competencies')) {
                $table->dropColumn('desired_competencies');
            }
        });
    }
};
