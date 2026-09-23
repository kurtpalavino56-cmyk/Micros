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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'user_code')) {
                $table->string('user_code')->nullable()->unique()->after('student_id');
            }
        });

        DB::transaction(function () {
            $users = DB::table('users')->whereNull('user_code')->orWhere('user_code', '')->get();

            foreach ($users as $user) {
                $roleId = (int) ($user->role_id ?? 3);
                $prefix = match ($roleId) {
                    1 => 'AD',
                    2 => 'FC',
                    3 => 'LN',
                    default => 'LN',
                };

                $year = now()->format('y');
                $latest = DB::table('users')
                    ->whereNotNull('user_code')
                    ->where('user_code', 'like', $year.'-'.$prefix.'-%')
                    ->get()
                    ->map(function ($record) {
                        preg_match('/^(\d{2})-([A-Z]{2})-(\d{4})$/', (string) $record->user_code, $matches);

                        return $matches ? (int) $matches[3] : 0;
                    })
                    ->max() ?? 0;

                $nextNumber = $latest + 1;
                $candidate = sprintf('%s-%s-%04d', $year, $prefix, $nextNumber);

                $suffix = 1;
                while (DB::table('users')->where('user_code', $candidate)->exists()) {
                    $candidate = sprintf('%s-%s-%04d', $year, $prefix, $nextNumber + $suffix);
                    $suffix++;
                }

                DB::table('users')->where('id', $user->id)->update(['user_code' => $candidate]);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('user_code')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['user_code']);
            $table->dropColumn('user_code');
        });
    }
};
