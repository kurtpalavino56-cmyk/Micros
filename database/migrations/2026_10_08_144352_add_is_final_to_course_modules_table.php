<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('course_modules') && ! Schema::hasColumn('course_modules', 'is_final')) {
            Schema::table('course_modules', function (Blueprint $table): void {
                $table->boolean('is_final')->default(false);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('course_modules') && Schema::hasColumn('course_modules', 'is_final')) {
            Schema::table('course_modules', function (Blueprint $table): void {
                $table->dropColumn('is_final');
            });
        }
    }
};
