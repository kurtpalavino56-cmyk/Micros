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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'date_of_birth')) {
                $table->string('date_of_birth')->nullable()->after('avatar_url');
            }
            if (! Schema::hasColumn('users', 'gender')) {
                $table->string('gender')->nullable()->after('date_of_birth');
            }
            if (! Schema::hasColumn('users', 'education')) {
                $table->string('education')->nullable()->after('gender');
            }
            if (! Schema::hasColumn('users', 'about')) {
                $table->text('about')->nullable()->after('education');
            }
            if (! Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable()->after('about');
            }
            if (! Schema::hasColumn('users', 'language')) {
                $table->string('language')->nullable()->after('bio');
            }
            if (! Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone')->nullable()->after('language');
            }
            if (! Schema::hasColumn('users', 'profile_completed')) {
                $table->boolean('profile_completed')->default(false)->after('timezone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['date_of_birth', 'gender', 'education', 'about', 'bio', 'language', 'timezone', 'profile_completed'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
