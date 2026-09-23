<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('stacking_frameworks', 'completion_mode')) {
            Schema::table('stacking_frameworks', function (Blueprint $table): void {
                $table->string('completion_mode')->default('all_required')->after('target_recognition');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('stacking_frameworks', 'completion_mode')) {
            Schema::table('stacking_frameworks', function (Blueprint $table): void {
                $table->dropColumn('completion_mode');
            });
        }
    }
};
