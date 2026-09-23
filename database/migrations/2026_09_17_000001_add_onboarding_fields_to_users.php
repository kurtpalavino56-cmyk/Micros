<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('location');
            }
            if (! Schema::hasColumn('users', 'career_goal')) {
                $table->string('career_goal')->nullable()->after('skills_want');
            }
            if (! Schema::hasColumn('users', 'pathway_id')) {
                $table->foreignId('pathway_id')->nullable()->after('career_goal')->constrained('pathways')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'pathway_id')) {
                $table->dropConstrainedForeignId('pathway_id');
            }
            foreach (['career_goal', 'address'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
