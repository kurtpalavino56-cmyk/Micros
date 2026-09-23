<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'skills_have')) {
                $table->json('skills_have')->nullable()->after('bio');
            }
            if (! Schema::hasColumn('users', 'skills_want')) {
                $table->json('skills_want')->nullable()->after('skills_have');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['skills_have', 'skills_want']);
        });
    }
};
