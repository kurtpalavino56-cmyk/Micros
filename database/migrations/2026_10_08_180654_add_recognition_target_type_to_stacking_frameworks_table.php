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
        Schema::table('stacking_frameworks', function (Blueprint $table) {
            $table->string('recognition_target_type')->nullable()->after('target_recognition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stacking_frameworks', function (Blueprint $table) {
            $table->dropColumn('recognition_target_type');
        });
    }
};
