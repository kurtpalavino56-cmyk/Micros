<?php

namespace App\Services;

use App\Models\User;

class AdminPageAccessService
{
    /** @return array<string, string> */
    public function pages(): array
    {
        return array_merge(...array_values($this->sections()));
    }

    /** @return array<string, array<string, string>> */
    public function sections(): array
    {
        return [
            'Overview' => [
                'dashboard' => 'Dashboard',
                'profile' => 'Profile',
            ],
            'People & Access' => [
                'user-management' => 'User Management',
                'faculty-codes' => 'Faculty Codes',
            ],
            'Courses & Credentials' => [
                'courses' => 'Courses',
                'completion-verification' => 'Completion Verification',
                'credentials' => 'Credentials',
            ],
            'Academic Structure' => [
                'program-categories' => 'Program Categories',
                'learning-pathways' => 'Learning Pathways',
                'stacking-frameworks' => 'Stacking Frameworks',
                'academic-credit-recognition' => 'Academic Credit Recognition',
            ],
            'Reports & Oversight' => [
                'reports' => 'Reports',
                'audit-logs' => 'Audit Logs',
            ],
            'Communication' => [
                'announcements' => 'Announcements',
                'complaints' => 'Complaints',
            ],
        ];
    }

    /** @return array<string, string> */
    public function selectablePages(): array
    {
        return array_diff_key($this->pages(), array_flip(User::DEFAULT_SUBADMIN_ADMIN_PAGES));
    }

    /**
     * @param  list<string>  $pages
     * @return list<string>
     */
    public function subAdminPages(array $pages): array
    {
        return array_values(array_unique([
            ...User::DEFAULT_SUBADMIN_ADMIN_PAGES,
            ...$pages,
        ]));
    }

    public function pageForRoute(?string $routeName): ?string
    {
        if ($routeName === null) {
            return null;
        }

        if ($routeName === 'admin.dashboard') {
            return 'dashboard';
        }

        if ($routeName === 'monitoring.live') {
            return 'audit-logs';
        }

        if ($routeName === 'admin.usermanagement' || str_starts_with($routeName, 'admin.users.')) {
            return 'user-management';
        }

        if (str_starts_with($routeName, 'admin.facultycodes')) {
            return 'faculty-codes';
        }

        if (in_array($routeName, ['admin.enrollments', 'admin.courses.enrollments'], true)
            || str_starts_with($routeName, 'admin.enrollments.')
        ) {
            return 'completion-verification';
        }

        if ($routeName === 'admin.courses' || str_starts_with($routeName, 'admin.courses.')) {
            return 'courses';
        }

        if (str_starts_with($routeName, 'admin.certificates')
            || str_starts_with($routeName, 'admin.badges.')
        ) {
            return 'credentials';
        }

        if (str_starts_with($routeName, 'admin.categories')) {
            return 'program-categories';
        }

        if (str_starts_with($routeName, 'admin.pathways')) {
            return 'learning-pathways';
        }

        if (str_starts_with($routeName, 'admin.stacking-frameworks')) {
            return 'stacking-frameworks';
        }

        if (str_starts_with($routeName, 'admin.academic-credit-recognition')
            || $routeName === 'admin.credit-evidence'
        ) {
            return 'academic-credit-recognition';
        }

        if (str_starts_with($routeName, 'admin.report')) {
            return 'reports';
        }

        if (str_starts_with($routeName, 'admin.audit-logs')) {
            return 'audit-logs';
        }

        if (str_starts_with($routeName, 'admin.announcements')) {
            return 'announcements';
        }

        if (str_starts_with($routeName, 'admin.complaints')) {
            return 'complaints';
        }

        if (str_starts_with($routeName, 'admin.profile')) {
            return 'profile';
        }

        return null;
    }

    public function firstRouteFor(User $user): ?string
    {
        foreach (array_keys($this->pages()) as $page) {
            if ($user->canAccessAdminPage($page)) {
                $routeName = $this->routeForPage($page);
                if ($routeName !== null) {
                    return $routeName;
                }
            }
        }

        return null;
    }

    public function routeForPage(string $page): ?string
    {
        return [
            'dashboard' => 'admin.dashboard',
            'user-management' => 'admin.usermanagement',
            'faculty-codes' => 'admin.facultycodes',
            'courses' => 'admin.courses',
            'completion-verification' => 'admin.enrollments',
            'credentials' => 'admin.certificates',
            'program-categories' => 'admin.categories',
            'learning-pathways' => 'admin.pathways',
            'stacking-frameworks' => 'admin.stacking-frameworks',
            'academic-credit-recognition' => 'admin.academic-credit-recognition',
            'reports' => 'admin.report',
            'audit-logs' => 'admin.audit-logs',
            'announcements' => 'admin.announcements',
            'complaints' => 'admin.complaints',
            'profile' => 'admin.profile',
        ][$page] ?? null;
    }
}
