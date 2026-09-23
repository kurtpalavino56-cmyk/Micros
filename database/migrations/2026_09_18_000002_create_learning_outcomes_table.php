<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2 — explicit, measurable learning outcomes per microcredential.
 *
 * `competency_unit_id` is nullable and reuses the existing competency
 * framework (`competency_units`) rather than creating a parallel one. Not
 * every outcome has to map to an existing competency unit right away.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('learning_outcomes')) {
            Schema::create('learning_outcomes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained()->cascadeOnDelete();
                $table->foreignId('competency_unit_id')->nullable()->constrained('competency_units')->nullOnDelete();
                $table->string('code')->nullable(); // e.g. "LO1"
                $table->text('description');
                $table->unsignedInteger('order')->default(1);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_outcomes');
    }
};
