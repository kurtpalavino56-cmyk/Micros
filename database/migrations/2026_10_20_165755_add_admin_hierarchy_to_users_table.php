<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'admin_page_access')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->json('admin_page_access')->nullable();
            });
        }

        DB::table('roles')->updateOrInsert(
            ['id' => 4],
            ['name' => 'superadmin', 'display_name' => 'Superadmin', 'description' => 'Full access to all administrator pages and account roles.', 'updated_at' => now(), 'created_at' => now()]
        );
        DB::table('roles')->updateOrInsert(
            ['id' => 5],
            ['name' => 'subadmin', 'display_name' => 'Sub-admin', 'description' => 'Administrator access limited to assigned pages.', 'updated_at' => now(), 'created_at' => now()]
        );

        DB::table('users')
            ->where('email', 'ramon.aquino@upskill.test')
            ->update(['role_id' => 4]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')
            ->whereIn('role_id', [4, 5])
            ->update(['role_id' => 1, 'admin_page_access' => null]);
        DB::table('roles')->whereIn('id', [4, 5])->delete();

        if (Schema::hasColumn('users', 'admin_page_access')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('admin_page_access');
            });
        }
    }
};
