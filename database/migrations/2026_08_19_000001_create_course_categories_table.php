<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Program Categories — the list a faculty member picks from when creating a
 * course. These were hardcoded in FacultyController; this makes them
 * admin-manageable from Admin › Management › Program Categories.
 *
 * courses.category stays a string: a course keeps the label it was created
 * with even if the category is later renamed or removed, so nothing is
 * orphaned.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('course_categories')) {
            Schema::create('course_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Seed the nine values that used to be hardcoded, so existing
        // courses keep matching a real category.
        $defaults = [
            'BS Information Technology',
            'BS Computer Science',
            'BS Information Systems',
            'Web Development',
            'Artificial Intelligence',
            'Databases',
            'Networking',
            'Computer Fundamentals',
            'Project Management',
        ];

        foreach ($defaults as $i => $name) {
            $exists = DB::table('course_categories')->where('name', $name)->exists();

            if (! $exists) {
                DB::table('course_categories')->insert([
                    'name' => $name,
                    'sort_order' => $i + 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Pick up any category already used by a course but missing above.
        $used = DB::table('courses')
            ->whereNotNull('category')
            ->where('category', '<>', '')
            ->distinct()
            ->pluck('category');

        foreach ($used as $name) {
            if (! DB::table('course_categories')->where('name', $name)->exists()) {
                DB::table('course_categories')->insert([
                    'name' => $name,
                    'sort_order' => 99,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('course_categories');
    }
};
