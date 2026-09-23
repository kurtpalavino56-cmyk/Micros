<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * UserCodeService — generates the platform's public ID codes in the
 * YY-PREFIX-NNNN format shown across the UI (e.g. 26-LN-0001).
 *
 *   AD → Administrator · FC → Faculty · LN → Learner (student)
 */
class UserCodeService
{
    /**
     * Generate the next available code for the given role id
     * (1 = admin, 2 = faculty, 3 = student).
     */
    public function generateForRole(int $roleId): string
    {
        $prefix = match ($roleId) {
            User::ROLE_ADMIN => 'AD',
            User::ROLE_FACULTY => 'FC',
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

        return $candidate;
    }
}
