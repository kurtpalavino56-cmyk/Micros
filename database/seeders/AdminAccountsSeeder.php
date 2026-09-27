<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * AdminAccountsSeeder — creates the two administrator accounts on ANY
 * database: a fresh install, a teammate's laptop, or a deployed server.
 *
 * Why this exists: accounts created by hand in tinker live only in that
 * one machine's database file. A seeder is repeatable — run it on every
 * copy of the project and the same admins exist everywhere.
 *
 * Safe to run repeatedly: updateOrCreate() keys on the username, so
 * re-running updates the same rows instead of failing on duplicates.
 *
 * Passwords default to the project values but can be overridden per
 * environment via ADMIN1_PASSWORD / ADMIN2_PASSWORD in .env — use that
 * on any real deployment instead of committing credentials in code.
 */
class AdminAccountsSeeder extends Seeder
{
    public function run(): void
    {
        // Resolve the admin role by NAME (not a hard-coded id) so this
        // works even on a database where the roles table ids differ.
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator']
        );

        $admins = [
            [
                'first_name' => 'Admin',
                'last_name' => 'One',
                'username' => 'admin1',
                'email' => 'admin1@gmail.com',
                'password' => env('ADMIN1_PASSWORD', 'Admin45#1256$2026PSU'),
                'user_code' => '26-AD-0002',
            ],
            [
                'first_name' => 'Admin',
                'last_name' => 'Two',
                'username' => 'admin2',
                'email' => 'admin2@gmail.com',
                'password' => env('ADMIN2_PASSWORD', 'Admin80!22052Z$202PSU'),
                'user_code' => '26-AD-0003',
            ],
        ];

        foreach ($admins as $data) {
            User::updateOrCreate(
                ['username' => $data['username']],
                [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'role_id' => $adminRole->id,
                    'user_code' => $data['user_code'],
                    'student_id' => $data['user_code'],
                    'is_active' => true,
                ]
            );
        }

        $this->command?->info('Admin accounts seeded: admin1, admin2');
    }
}
