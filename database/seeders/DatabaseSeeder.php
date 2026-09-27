<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\Badge;
use App\Models\Certificate;
use App\Models\CompetencyCategory;
use App\Models\CompetencyProgress;
use App\Models\CompetencyUnit;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\FacultyCode;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * DatabaseSeeder — brings a fresh clone up to a usable state.
 *
 * database.sqlite is gitignored, so without this a new checkout has no
 * roles, no accounts and nothing to click on. Run with:
 *
 *     php artisan migrate:fresh --seed
 *
 * Login for every seeded account: password123
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRoles();

        $admin = $this->seedAdmin();
        $faculty = $this->seedFaculty();
        $students = $this->seedStudents();

        $this->seedFacultyCodes($admin, $faculty);
        $featuredCourse = $this->seedCourse($faculty);
        $courses = collect([$featuredCourse])->merge($this->seedAnalyticsCourses($faculty));
        $this->seedEnrolments($courses, $students);
        $this->seedAnalyticsCredentials($courses, $students);
        $this->seedAnalyticsCompetencies($students);
    }

    private function seedRoles(): void
    {
        foreach ([
            ['id' => User::ROLE_ADMIN,   'name' => 'admin',   'display_name' => 'Administrator'],
            ['id' => User::ROLE_FACULTY, 'name' => 'faculty', 'display_name' => 'Faculty'],
            ['id' => User::ROLE_STUDENT, 'name' => 'student', 'display_name' => 'Student'],
        ] as $role) {
            Role::updateOrCreate(['id' => $role['id']], $role);
        }
    }

    private function seedAdmin(): User
    {
        return User::updateOrCreate(
            ['email' => 'ramon.aquino@upskill.test'],
            [
                'first_name' => 'Ramon',
                'last_name' => 'Aquino',
                'username' => 'raquino',
                'password' => Hash::make('password123'),
                'role_id' => User::ROLE_ADMIN,
                'user_code' => '26-AD-0001',
                'student_id' => '26-AD-0001',
                'is_active' => true,
                'profile_completed' => true,
            ]
        );
    }

    private function seedFaculty(): User
    {
        return User::updateOrCreate(
            ['email' => 'liza.ferrer@upskill.test'],
            [
                'first_name' => 'Liza',
                'last_name' => 'Ferrer',
                'username' => 'lferrer',
                'password' => Hash::make('password123'),
                'role_id' => User::ROLE_FACULTY,
                'user_code' => '26-FC-0001',
                'student_id' => '26-FC-0001',
                'phone' => '09171234567',
                'location' => 'Pangasinan',
                'is_active' => true,
                'profile_completed' => true,
            ]
        );
    }

    /** @return Collection<int, User> */
    private function seedStudents()
    {
        // Explicit usernames rather than a generated "name+index" — the old
        // scheme produced logins like "juan0", which are awkward to read out
        // and to type on a phone during a demo.
        $students = [
            ['Miguel', 'Reyes',      'mreyes'],
            ['Carmen', 'Bautista',   'cbautista'],
            ['Noel',   'Villanueva', 'nvillanueva'],
            ['Angela', 'Santos',     'asantos'],
            ['Paolo',  'Garcia',     'pgarcia'],
            ['Jessa',  'Mendoza',    'jmendoza'],
            ['Rafael', 'Cruz',       'rcruz'],
            ['Sofia',  'Navarro',    'snavarro'],
            ['Diego',  'Ramos',      'dramos'],
            ['Leah',   'Flores',     'lflores'],
            ['Marco',  'Torres',     'mtorres'],
            ['Nina',   'Castillo',   'ncastillo'],
        ];

        return collect($students)->values()->map(function (array $s, int $i) {
            [$first, $last, $username] = $s;

            $code = $i < 3 ? sprintf('26-LN-%04d', $i + 1) : sprintf('26-DEMO-%04d', $i + 1);
            $email = strtolower($first).'.'.strtolower(str_replace(' ', '', $last)).'@upskill.test';

            return User::updateOrCreate(
                ['email' => $email],
                [
                    'first_name' => $first,
                    'last_name' => $last,
                    'username' => $username,
                    'password' => Hash::make('password123'),
                    'role_id' => User::ROLE_STUDENT,
                    'user_code' => $code,
                    'student_id' => $code,
                    'is_active' => true,
                    // Left false on purpose so the profile-completion form
                    // on the student dashboard can actually be exercised.
                    'profile_completed' => $i > 0,
                ]
            );
        });
    }

    private function seedFacultyCodes(User $admin, User $faculty): void
    {
        FacultyCode::updateOrCreate(
            ['code' => 'FAC-DEMO01'],
            ['created_by' => $admin->id, 'used_by' => $faculty->id, 'used_at' => now()]
        );

        // Two spare codes so faculty registration can be tested.
        foreach (['FAC-SPARE1', 'FAC-SPARE2'] as $code) {
            FacultyCode::updateOrCreate(
                ['code' => $code],
                ['created_by' => $admin->id, 'used_by' => null, 'used_at' => null]
            );
        }
    }

    /**
     * A fully populated course — modules, lessons and a quiz — so the
     * student learn / progress / quiz flow has something to run against.
     */
    private function seedCourse(User $faculty): Course
    {
        $course = Course::updateOrCreate(
            ['slug' => 'intro-to-web-development'],
            [
                'title' => 'Introduction to Web Development',
                'description' => 'HTML, CSS and the basics of how the web fits together.',
                'skills' => ['HTML & CSS', 'Web Development'],
                'objectives' => ['Build a static page', 'Understand the request lifecycle'],
                'category' => 'Information Technology',
                'level' => 'Beginner',
                'duration' => '4 weeks',
                'instructor' => $faculty->name,
                'created_by' => $faculty->id,
                'passing_score' => 75,
                'is_featured' => true,
                'is_published' => true,
                'approval_status' => 'approved',
                'is_approved' => true,
                'approved_at' => now(),
            ]
        );

        $modules = [
            ['title' => 'Getting Started', 'lessons' => ['What is the Web?', 'Setting Up Your Editor']],
            ['title' => 'HTML Basics',     'lessons' => ['Elements and Tags', 'Forms and Inputs']],
        ];

        foreach ($modules as $i => $definition) {
            $module = CourseModule::updateOrCreate(
                ['course_id' => $course->id, 'order' => $i + 1],
                ['title' => $definition['title'], 'description' => null]
            );

            foreach ($definition['lessons'] as $j => $title) {
                CourseLesson::updateOrCreate(
                    ['course_id' => $course->id, 'module_id' => $module->id, 'order' => $j + 1],
                    ['title' => $title, 'type' => 'Video', 'duration' => '10', 'content' => 'Placeholder lesson content.']
                );
            }

            if ($i === 0) {
                $quiz = Quiz::updateOrCreate(
                    ['course_id' => $course->id, 'module_id' => $module->id],
                    [
                        'title' => $definition['title'].' Quiz',
                        'passing_score' => 75,
                        'is_active' => true,
                    ]
                );

                QuizQuestion::updateOrCreate(
                    ['quiz_id' => $quiz->id, 'question' => 'What does HTML stand for?'],
                    [
                        'type' => 'Multiple Choice',
                        'options' => ['HyperText Markup Language', 'Hyperlink Text Mode', 'Home Tool Markup Language'],
                        'correct_answer' => 'HyperText Markup Language',
                        'points' => 1,
                    ]
                );
            }
        }

        $course->update(['lessons_count' => $course->lessons()->count()]);

        return $course;
    }

    /** @param Collection<int, Course> $courses */
    private function seedEnrolments($courses, $students): void
    {
        foreach ($courses as $courseIndex => $course) {
            foreach ($students as $studentIndex => $student) {
                $progress = (($studentIndex * 19) + ($courseIndex * 13)) % 101;
                Enrollment::updateOrCreate(
                    ['user_id' => $student->id, 'course_id' => $course->id],
                    [
                        'enrolled_at' => now()->subMonths(($studentIndex + $courseIndex) % 9)->subDays($studentIndex),
                        'is_completed' => $progress >= 80,
                        'progress_percent' => $progress,
                        'progress_state' => ['completed_lessons' => [], 'module_scores' => []],
                    ]
                );
            }

            $course->update(['enrolled_count' => $course->enrollments()->count()]);
        }
    }

    /** @return Collection<int, Course> */
    private function seedAnalyticsCourses(User $faculty)
    {
        $definitions = [
            ['title' => 'Data Structures & Algorithms', 'slug' => 'data-structures-algorithms', 'category' => 'Information Technology', 'program' => 'Computer Science'],
            ['title' => 'Mobile App Development', 'slug' => 'mobile-app-development', 'category' => 'Information Technology', 'program' => 'Computer Science'],
            ['title' => 'Digital Marketing', 'slug' => 'digital-marketing', 'category' => 'Business', 'program' => 'Business Administration'],
            ['title' => 'UI/UX Design', 'slug' => 'ui-ux-design', 'category' => 'Design', 'program' => 'Multimedia Arts'],
        ];

        return collect($definitions)->map(function (array $definition, int $index) use ($faculty) {
            return Course::updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'title' => $definition['title'],
                    'description' => 'Analytics demo course for the Upskill platform.',
                    'category' => $definition['category'],
                    'program' => $definition['program'],
                    'level' => $index % 2 === 0 ? 'Intermediate' : 'Beginner',
                    'duration' => '6 weeks',
                    'instructor' => $faculty->name,
                    'created_by' => $faculty->id,
                    'passing_score' => 70 + ($index * 2),
                    'is_published' => true,
                    'approval_status' => 'approved',
                    'is_approved' => true,
                    'approved_at' => now(),
                ]
            );
        });
    }

    /** @param Collection<int, Course> $courses */
    private function seedAnalyticsCredentials($courses, $students): void
    {
        $badges = collect(['Web Foundations', 'Algorithm Builder', 'Mobile Creator', 'Design Thinker'])->mapWithKeys(function (string $name) {
            $badge = Badge::updateOrCreate(
                ['name' => $name],
                ['description' => 'Analytics demo badge', 'is_active' => true, 'badge_level' => 'Foundational']
            );

            return [$name => $badge];
        });

        foreach ($students as $studentIndex => $student) {
            foreach ($badges->values()->take(($studentIndex % 4) + 1) as $badgeIndex => $badge) {
                DB::table('user_badges')->updateOrInsert(
                    ['user_id' => $student->id, 'badge_id' => $badge->id],
                    ['earned_at' => now()->subMonths(($studentIndex + $badgeIndex) % 9), 'updated_at' => now(), 'created_at' => now()]
                );
            }

            foreach ($courses->take(3) as $courseIndex => $course) {
                $enrollment = Enrollment::where('user_id', $student->id)->where('course_id', $course->id)->first();
                if ($enrollment?->is_completed) {
                    Certificate::updateOrCreate(
                        ['user_id' => $student->id, 'course_id' => $course->id],
                        ['serial' => sprintf('DEMO-%04d-%02d', $student->id, $courseIndex + 1), 'title' => 'Certificate of Completion', 'issued_at' => now()->subMonths(($studentIndex + $courseIndex) % 9)]
                    );
                }
            }
        }

        foreach ($courses as $courseIndex => $course) {
            $quiz = Quiz::updateOrCreate(
                ['course_id' => $course->id, 'title' => $course->title.' Assessment'],
                ['passing_score' => $course->passing_score, 'is_active' => true]
            );

            foreach ($students->take(8) as $studentIndex => $student) {
                DB::table('quiz_attempts')->updateOrInsert(
                    ['user_id' => $student->id, 'quiz_id' => $quiz->id],
                    ['score' => 62 + (($studentIndex * 7 + $courseIndex * 3) % 35), 'passed' => $studentIndex % 4 !== 0, 'submitted_at' => now()->subMonths(($studentIndex + $courseIndex) % 9), 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }
    }

    private function seedAnalyticsCompetencies($students): void
    {
        $definitions = [
            'Web Development' => ['HTML & CSS', 'Frontend Foundations'],
            'Programming' => ['Programming Logic', 'Algorithms'],
            'Database Management' => ['Relational Data', 'SQL Queries'],
            'Mobile Development' => ['Mobile UI', 'Mobile Services'],
        ];

        foreach ($definitions as $categoryName => $unitNames) {
            $category = CompetencyCategory::updateOrCreate(
                ['name' => $categoryName],
                ['description' => 'Analytics demo competency area', 'is_active' => true]
            );

            foreach ($unitNames as $unitIndex => $unitName) {
                $unit = CompetencyUnit::updateOrCreate(
                    ['competency_category_id' => $category->id, 'title' => $unitName],
                    ['description' => 'Analytics demo competency unit', 'order' => $unitIndex + 1, 'is_active' => true]
                );

                foreach ($students->take(6) as $studentIndex => $student) {
                    $score = max(35, 88 - ($unitIndex * 8) - (($studentIndex + $category->id) % 15));
                    $assessment = Assessment::updateOrCreate(
                        ['user_id' => $student->id, 'competency_unit_id' => $unit->id],
                        ['type' => 'Competency Review', 'title' => $unitName.' Review', 'passing_score' => 70, 'status' => $studentIndex === 5 ? 'pending' : 'completed', 'score' => $studentIndex === 5 ? null : $score]
                    );

                    CompetencyProgress::updateOrCreate(
                        ['user_id' => $student->id, 'competency_unit_id' => $unit->id],
                        ['assessment_id' => $assessment->id, 'status' => $studentIndex === 5 ? 'pending' : 'completed', 'mastery_score' => $score, 'completed_at' => $studentIndex === 5 ? null : now()->subMonths($studentIndex % 6)]
                    );
                }
            }
        }
    }
}
