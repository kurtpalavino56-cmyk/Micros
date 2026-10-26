<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempt_grants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('extra_attempts');
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason');
            $table->timestamps();
            $table->index(['student_id', 'quiz_id', 'created_at'], 'quiz_attempt_grants_student_quiz_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempt_grants');
    }
};
