<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('competency_categories')) {
            Schema::create('competency_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('color')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('competency_units')) {
            Schema::create('competency_units', function (Blueprint $table) {
                $table->id();
                $table->foreignId('competency_category_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->integer('order')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('competency_levels')) {
            Schema::create('competency_levels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('competency_unit_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->integer('level_number')->default(1);
                $table->integer('points')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('competency_outcomes')) {
            Schema::create('competency_outcomes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('competency_unit_id')->constrained()->cascadeOnDelete();
                $table->text('description');
                $table->integer('order')->default(1);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('assessments')) {
            Schema::create('assessments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('competency_unit_id')->constrained()->cascadeOnDelete();
                $table->foreignId('competency_level_id')->nullable()->constrained()->nullOnDelete();
                $table->string('type');
                $table->string('title');
                $table->text('description')->nullable();
                $table->integer('passing_score')->default(70);
                $table->string('status')->default('assigned');
                $table->integer('score')->nullable();
                $table->text('feedback')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('competency_progresses')) {
            Schema::create('competency_progresses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('competency_unit_id')->constrained()->cascadeOnDelete();
                $table->foreignId('assessment_id')->nullable()->constrained()->nullOnDelete();
                $table->string('status')->default('pending');
                $table->integer('mastery_score')->default(0);
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'competency_unit_id']);
            });
        }

        Schema::table('badges', function (Blueprint $table) {
            if (! Schema::hasColumn('badges', 'is_stackable')) {
                $table->boolean('is_stackable')->default(false)->after('icon_url');
            }
            if (! Schema::hasColumn('badges', 'badge_level')) {
                $table->string('badge_level')->nullable()->after('is_stackable');
            }
            if (! Schema::hasColumn('badges', 'prerequisite_badge_id')) {
                $table->foreignId('prerequisite_badge_id')->nullable()->after('badge_level')->constrained('badges')->nullOnDelete();
            }
            if (! Schema::hasColumn('badges', 'pathway_id')) {
                $table->foreignId('pathway_id')->nullable()->after('prerequisite_badge_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('badges', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('pathway_id');
            }
        });

        if (! Schema::hasTable('badge_rules')) {
            Schema::create('badge_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
                $table->string('rule_type');
                $table->string('rule_value')->nullable();
                $table->string('operator')->default('equals');
                $table->integer('threshold')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('badge_rules');
        Schema::dropIfExists('competency_progresses');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('competency_outcomes');
        Schema::dropIfExists('competency_levels');
        Schema::dropIfExists('competency_units');
        Schema::dropIfExists('competency_categories');

        Schema::table('badges', function (Blueprint $table) {
            if (Schema::hasColumn('badges', 'prerequisite_badge_id')) {
                $table->dropConstrainedForeignId('prerequisite_badge_id');
            }
            if (Schema::hasColumn('badges', 'pathway_id')) {
                $table->dropConstrainedForeignId('pathway_id');
            }
            if (Schema::hasColumn('badges', 'is_stackable')) {
                $table->dropColumn('is_stackable');
            }
            if (Schema::hasColumn('badges', 'badge_level')) {
                $table->dropColumn('badge_level');
            }
            if (Schema::hasColumn('badges', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
