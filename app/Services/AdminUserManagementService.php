<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserManagementService
{
    public function create(array $data, UserCodeService $userCodes): User
    {
        $code = $userCodes->generateForRole((int) $data['role_id']);

        return User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'role_id' => (int) $data['role_id'],
            'student_id' => $code,
            'user_code' => $code,
            'is_active' => true,
        ]);
    }

    public function detail(User $user): object
    {
        return (object) [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'role' => match ((int) $user->role_id) {
                User::ROLE_ADMIN => 'Administrator',
                User::ROLE_FACULTY => 'Faculty',
                default => 'Learner',
            },
            'student_id' => $user->student_id ?? $user->user_code,
            'phone' => $user->phone,
            'location' => $user->location,
            'gender' => $user->gender,
            'education' => $user->education,
            'school' => $user->school,
            'date_of_birth' => $user->date_of_birth,
            'bio' => $user->bio,
            'avatar_url' => $user->avatar_url,
            'is_active' => (bool) $user->is_active,
            'joined' => $user->created_at?->format('M d, Y'),
            'enrollments' => (int) ($user->enrollments_count ?? $user->enrollments()->count()),
            'courses_created' => (int) $user->role_id === User::ROLE_FACULTY
                ? Course::where('created_by', $user->id)->count()
                : 0,
            'skills_have' => $user->skills_have ?? [],
            'skills_want' => $user->skills_want ?? [],
        ];
    }

    /** @return array{deleted: bool, message: string} */
    public function delete(User $user, int $currentUserId): array
    {
        if ($user->isAdmin()) {
            return ['deleted' => false, 'message' => 'Administrator accounts cannot be deleted from this screen.'];
        }

        if ($user->id === $currentUserId) {
            return ['deleted' => false, 'message' => 'You cannot delete your own account.'];
        }

        $name = $user->name;
        $user->delete();

        return ['deleted' => true, 'message' => $name];
    }
}
