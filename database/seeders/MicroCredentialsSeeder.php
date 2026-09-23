<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * MicroCredentialsSeeder — seeds the full UPSKILL demo dataset:
 * roles, users (admin / faculty / students), courses with modules,
 * lessons and quizzes, enrollments, badges, certificates, pathways,
 * notifications and analytics events.
 *
 * Demo logins (password for all: "password"):
 *   admin@example.com · faculty@example.com · student@example.com
 */
class MicroCredentialsSeeder extends Seeder
{
    public function run(): void
    {
        // ── Roles ─────────────────────────────────────────────────────────
        DB::table('roles')->insertOrIgnore([
            ['id' => 1, 'name' => 'admin',   'display_name' => 'Administrator', 'description' => 'Platform administrator', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'faculty', 'display_name' => 'Faculty',       'description' => 'Course instructor',        'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'student', 'display_name' => 'Student',       'description' => 'Learner',                  'created_at' => now(), 'updated_at' => now()],
        ]);

        // ── Users ─────────────────────────────────────────────────────────
        $admin = $this->makeUser([
            'first_name' => 'System', 'last_name' => 'Administrator',
            'email' => 'admin@example.com', 'username' => 'admin',
            'role_id' => 1, 'code' => now()->format('y').'-AD-0001',
            'phone' => '09170000001', 'location' => 'Main Campus',
            'about' => 'Administrator of the UPSKILL platform.',
            'bio' => 'Oversees course management, student progress, analytics, and badge issuance.',
        ]);

        // Primary admin login — admin@controller.com / password
        $admin = $this->makeUser([
            'first_name' => 'Admin', 'last_name' => 'Controller',
            'email' => 'admin@controller.com', 'username' => 'admin.controller',
            'role_id' => 1, 'code' => now()->format('y').'-AD-0002',
            'phone' => '09170000004', 'location' => 'Main Campus',
            'about' => 'Administrator of the UPSKILL platform.',
            'bio' => 'Oversees course management, student progress, analytics, and badge issuance.',
        ]);

        $faculty = $this->makeUser([
            'first_name' => 'Juan', 'last_name' => 'Dela Cruz',
            'email' => 'faculty@example.com', 'username' => 'juan.delacruz',
            'role_id' => 2, 'code' => now()->format('y').'-FC-0001',
            'phone' => '09345678912', 'location' => 'San Juan, Pangasinan',
            'gender' => 'Male', 'education' => 'BS Information Technology',
            'date_of_birth' => '1985-10-12',
            'about' => 'Passionate about Web Developing and App Developing. Problem Solving. Love to Learn new Skills and More.',
            'bio' => 'Senior Faculty Member specializing in web development using Laravel.',
            'language' => 'English', 'timezone' => '(GMT + 8:00) Asia/Manila',
        ]);

        $ana = $this->makeUser([
            'first_name' => 'Ana', 'last_name' => 'Lopez',
            'email' => 'student@example.com', 'username' => 'ana.lopez',
            'role_id' => 3, 'code' => now()->format('y').'-LN-0001',
            'phone' => '09345678912', 'location' => 'San Juan, Pangasinan',
            'gender' => 'Female', 'education' => 'BS Information Technology',
            'date_of_birth' => '2003-10-12',
            'about' => 'Passionate about Web Development and App Development. Problem Solving. Love to Learn new Skills and More.',
            'bio' => 'Photo Editing Skilled. Frontend Web Designer for Wordpress. Can Edit Multiple Frames in Just 1 Hour.',
            'language' => 'English', 'timezone' => 'Asia/Manila',
        ]);

        // Extra students — the directory (Student_List) + faculty roster.
        $extraStudents = [];
        foreach ([
            ['Kurt',    'Palavino',          '22-LN-0712', 'kurt.palavino'],
            ['Anna',    'Reyes',             '21-LN-0789', 'anna.reyes'],
            ['Mike',    'Abdul',             '22-LN-9856', 'mike.abdul'],
            ['Juan',    'Dela Cruz',         '23-LN-0417', 'juan.student'],
            ['Maria',   'Clara Santos',      '23-LN-1189', 'maria.santos'],
            ['Jose',    'Rizal Mercado',     '24-LN-0562', 'jose.mercado'],
            ['Andres',  'Bonifacio',         '24-LN-2031', 'andres.bonifacio'],
            ['Gabriela', 'Silang',            '25-LN-0748', 'gabriela.silang'],
            ['Emilio',  'Aguinaldo',         '25-LN-1394', 'emilio.aguinaldo'],
            ['Melchora', 'Aquino',            '25-LN-2216', 'melchora.aquino'],
            ['Antonio', 'Luna',              '26-LN-0083', 'antonio.luna'],
            ['Gregoria', 'de Jesus',          '26-LN-0925', 'gregoria.dejesus'],
        ] as [$first, $last, $code, $username]) {
            $extraStudents[$username] = $this->makeUser([
                'first_name' => $first, 'last_name' => $last,
                'email' => $username.'@example.com', 'username' => $username,
                'role_id' => 3, 'code' => $code,
            ]);
        }

        // ── Badges ────────────────────────────────────────────────────────
        $badgeIds = [];
        foreach ([
            ['Database Master', 'Completed Database and Eloquent Model'],
            ['AI Pioneer',      'Completed an Artificial Intelligence course'],
            ['Web Wizard',      'Completed a Web Development course'],
            ['Certified Pro',   'Completed a professional certification track'],
        ] as [$name, $desc]) {
            $badgeIds[$name] = DB::table('badges')->insertGetId([
                'name' => $name, 'description' => $desc, 'icon_url' => null,
                'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // ── Courses ───────────────────────────────────────────────────────
        $courseIds = [];
        $courses = [
            [
                'title' => 'Full-Stack Web Development with Laravel',
                'description' => 'Master modern web development using the Laravel framework, MySQL and Blade templating. Build real-world applications from scratch.',
                'category' => 'Web Development', 'level' => 'Intermediate', 'duration' => '40h',
                'skills' => ['Laravel', 'PHP', 'MySQL', 'MVC'],
                'objectives' => ['Build Full-web Applications', 'Understand MVC Architecture', 'Master Laravel Eloquent ORM', 'Deploy applications in production'],
                'is_featured' => true, 'badge' => 'Web Wizard',
            ],
            [
                'title' => 'Computer Networking Fundamentals',
                'description' => 'Learn the core concepts of computer networking, protocols, and network design.',
                'category' => 'Networking', 'level' => 'Beginner', 'duration' => '30h',
                'skills' => ['TCP/IP', 'DNS', 'Routing', 'Switching'],
                'objectives' => ['Understand OSI and TCP/IP models', 'Configure basic network devices', 'Troubleshoot common network issues'],
                'is_featured' => true, 'badge' => null,
            ],
            [
                'title' => 'Introduction to Artificial Intelligence',
                'description' => 'Explore the fundamentals of Artificial Intelligence, machine learning algorithms and their real-world applications.',
                'category' => 'Artificial Intelligence', 'level' => 'Beginner', 'duration' => '35h',
                'skills' => ['AI Basics', 'Machine Learning', 'Neural Networks'],
                'objectives' => ['Understand core AI concepts', 'Train simple ML models', 'Apply AI to real-world problems'],
                'is_featured' => true, 'badge' => 'AI Pioneer',
            ],
            [
                'title' => 'Database Fundamentals',
                'description' => 'Design, query and manage relational databases with confidence.',
                'category' => 'Databases', 'level' => 'Beginner', 'duration' => '30h',
                'skills' => ['SQL', 'MySQL', 'Normalization'],
                'objectives' => ['Design relational schemas', 'Write efficient SQL queries', 'Normalize database tables'],
                'is_featured' => false, 'badge' => 'Database Master',
            ],
            [
                'title' => 'Web Development Bootcamp',
                'description' => 'An intensive bootcamp covering HTML, CSS, JavaScript and modern frameworks.',
                'category' => 'Web Development', 'level' => 'Intermediate', 'duration' => '60h',
                'skills' => ['HTML', 'CSS', 'JavaScript'],
                'objectives' => ['Build responsive layouts', 'Master DOM manipulation', 'Deploy a portfolio site'],
                'is_featured' => false, 'badge' => 'Certified Pro',
            ],
            [
                'title' => 'Machine Learning Essentials',
                'description' => 'The essential machine learning techniques every developer should know.',
                'category' => 'Artificial Intelligence', 'level' => 'Advanced', 'duration' => '45h',
                'skills' => ['Python', 'Regression', 'Classification'],
                'objectives' => ['Understand supervised learning', 'Evaluate model performance', 'Build an end-to-end ML pipeline'],
                'is_featured' => false, 'badge' => 'AI Pioneer',
            ],
            [
                'title' => 'Introduction to Computing',
                'description' => 'A friendly first step into the world of computing and information technology.',
                'category' => 'Computer Fundamentals', 'level' => 'Beginner', 'duration' => '25h',
                'skills' => ['Computing Basics', 'Hardware', 'Software'],
                'objectives' => ['Identify computer components', 'Understand operating systems', 'Use productivity software'],
                'is_featured' => false, 'badge' => null,
            ],
            [
                'title' => 'IT Projects Management',
                'description' => 'Plan, execute and deliver IT projects on time and on budget.',
                'category' => 'Project Management', 'level' => 'Intermediate', 'duration' => '30h',
                'skills' => ['Agile', 'Scrum', 'Planning'],
                'objectives' => ['Draft a project charter', 'Run sprint ceremonies', 'Manage stakeholders'],
                'is_featured' => false, 'badge' => null,
            ],
            [
                'title' => 'Web Development Fundamentals',
                'description' => 'Your first website: structure with HTML, style with CSS.',
                'category' => 'Web Development', 'level' => 'Beginner', 'duration' => '30h',
                'skills' => ['HTML', 'CSS'],
                'objectives' => ['Write semantic HTML', 'Style pages with CSS', 'Publish a static site'],
                'is_featured' => false, 'badge' => 'Web Wizard',
            ],
            [
                'title' => 'Data Management Essentials',
                'description' => 'Organise, clean and prepare data for analysis.',
                'category' => 'Databases', 'level' => 'Beginner', 'duration' => '28h',
                'skills' => ['Data Cleaning', 'Spreadsheets', 'SQL'],
                'objectives' => ['Clean messy datasets', 'Model simple databases', 'Export data for reporting'],
                'is_featured' => false, 'badge' => null,
            ],
            [
                'title' => 'Computer Organization Basics',
                'description' => 'How computers work under the hood: CPUs, memory and storage.',
                'category' => 'Computer Fundamentals', 'level' => 'Beginner', 'duration' => '26h',
                'skills' => ['CPU', 'Memory', 'Storage'],
                'objectives' => ['Explain the fetch-decode-execute cycle', 'Compare memory hierarchies', 'Understand binary data'],
                'is_featured' => false, 'badge' => null,
            ],
        ];

        foreach ($courses as $c) {
            $courseIds[$c['title']] = DB::table('courses')->insertGetId([
                'title' => $c['title'],
                'slug' => Str::slug($c['title']),
                'description' => $c['description'],
                'skills' => json_encode($c['skills']),
                'objectives' => json_encode($c['objectives']),
                'category' => $c['category'],
                'program' => 'BS Information Technology',
                'term' => '1st Semester 2026 - 2027',
                'level' => $c['level'],
                'duration' => $c['duration'],
                'instructor' => 'Prof. Juan Dela Cruz',
                'created_by' => $faculty->id,
                'badge_id' => $c['badge'] ? $badgeIds[$c['badge']] : null,
                'lessons_count' => 0,
                'enrolled_count' => 0,
                'passing_score' => 75,
                'is_featured' => $c['is_featured'],
                'is_published' => true,
                'thumbnail_url' => null,
                'approval_status' => 'approved',
                'is_approved' => true,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // A pending submission from faculty — demonstrates the Approve /
        // Denied buttons on the Admin Courses & Badges page.
        $pendingCourseId = DB::table('courses')->insertGetId([
            'title' => 'Introduction to Cloud Computing',
            'slug' => 'introduction-to-cloud-computing',
            'description' => 'Understand cloud service models, deployment strategies, and how to deploy applications to the cloud.',
            'skills' => json_encode(['Cloud Basics', 'AWS', 'Deployment']),
            'objectives' => json_encode(['Explain IaaS, PaaS and SaaS', 'Deploy a web app to the cloud', 'Compare cloud providers']),
            'category' => 'Computer Fundamentals', 'program' => 'BS Information Technology',
            'term' => '1st Semester 2026 - 2027', 'level' => 'Beginner', 'duration' => '20h',
            'instructor' => 'Prof. Juan Dela Cruz', 'created_by' => $faculty->id,
            'passing_score' => 75, 'is_featured' => false, 'is_published' => false,
            'approval_status' => 'pending', 'is_approved' => false,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('course_modules')->insert([
            'course_id' => $pendingCourseId, 'title' => 'Getting Started with the Cloud',
            'description' => 'Foundational cloud concepts', 'order' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // Hidden container course for external certifications.
        $externalCourseId = DB::table('courses')->insertGetId([
            'title' => 'External Certifications', 'slug' => 'external-certifications',
            'description' => 'Container for externally-issued certifications.',
            'category' => 'Certifications', 'level' => 'Beginner',
            'instructor' => null, 'created_by' => $faculty->id,
            'passing_score' => 75, 'is_featured' => false, 'is_published' => false,
            'approval_status' => 'approved', 'is_approved' => true,
            'approved_by' => $admin->id, 'approved_at' => now(),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // ── Modules / Lessons / Quizzes for the two flagship courses ──────
        $laravelId = $courseIds['Full-Stack Web Development with Laravel'];
        $networkId = $courseIds['Computer Networking Fundamentals'];

        $this->seedModule($laravelId, 1, 'Laravel Fundamentals', 'Core concepts of Laravel Framework', [
            ['Introduction to Laravel', 'Video', '15m', 'Get to know the Laravel framework and its ecosystem.'],
            ['Routing and Controllers', 'Text', '15m', 'Handle HTTP requests with routes and controllers.'],
            ['Blade Templates',         'Text', '15m', 'Build dynamic views with the Blade templating engine.'],
        ], ['Laravel Fundamentals Quiz', 75, [
            ['Which artisan command creates a new controller?', ['php artisan make:controller', 'php artisan new:controller', 'php artisan create:controller'], 'php artisan make:controller'],
            ['Blade template files use which extension?', ['.blade.php', '.tpl.php', '.view.php'], '.blade.php'],
            ['Which directory holds route definitions?', ['routes', 'app/Http', 'resources'], 'routes'],
        ]]);

        $this->seedModule($laravelId, 2, 'Database & Eloquent ORM', 'Master database interactions with Eloquent', [
            ['Database Migrations',    'Video', '20m', 'Version-control your database schema with migrations.'],
            ['Eloquent Relationships', 'Text',  '25m', 'Model one-to-many and many-to-many relationships.'],
        ], ['Database & Eloquent ORM', 75, [
            ['What does ORM stand for?', ['Object-Relational Mapping', 'Object-Request Model', 'Ordered Row Manager'], 'Object-Relational Mapping'],
            ['Which method runs all pending migrations?', ['migrate', 'migrate:fresh', 'db:seed'], 'migrate'],
            ['A one-to-many relation is defined with…', ['hasMany', 'belongsToMany', 'morphTo'], 'hasMany'],
        ]]);

        $this->seedModule($networkId, 1, 'Networking Basics', 'Core networking concepts', [
            ['Introduction to Networking', 'Video', '15m', 'What a network is and why it matters.'],
            ['OSI & TCP/IP Models',        'Text',  '20m', 'The layered models that describe network communication.'],
            ['Routing and Switching',      'Text',  '20m', 'How packets find their way across networks.'],
        ], ['Networking Basics Quiz', 75, [
            ['How many layers does the OSI model have?', ['7', '4', '5'], '7'],
            ['Which device forwards packets between networks?', ['Router', 'Switch', 'Hub'], 'Router'],
            ['DNS resolves…', ['Domain names to IP addresses', 'IP addresses to MAC addresses', 'URLs to ports'], 'Domain names to IP addresses'],
        ]]);

        // ── Enrollments ───────────────────────────────────────────────────
        $enroll = function (User $user, string $courseTitle, int $progress, bool $completed, ?string $when = null) use ($courseIds) {
            DB::table('enrollments')->insertOrIgnore([
                'user_id' => $user->id,
                'course_id' => $courseIds[$courseTitle],
                'enrolled_at' => $when ? Carbon::parse($when) : now()->subDays(random_int(3, 40)),
                'is_completed' => $completed,
                'progress_percent' => $progress,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        };

        // Ana (the demo student) — active in two courses, completed one.
        $enroll($ana, 'Full-Stack Web Development with Laravel', 45, false);
        $enroll($ana, 'Computer Networking Fundamentals', 100, true);
        $enroll($ana, 'Introduction to Artificial Intelligence', 62, false);
        $enroll($ana, 'Database Fundamentals', 100, true);

        // Faculty manage-screen roster for the Laravel course.
        $enroll($extraStudents['kurt.palavino'], 'Full-Stack Web Development with Laravel', 30, false);
        $enroll($extraStudents['anna.reyes'], 'Full-Stack Web Development with Laravel', 100, true);
        $enroll($extraStudents['mike.abdul'], 'Full-Stack Web Development with Laravel', 100, true);

        // Directory students and their courses.
        $enroll($extraStudents['juan.student'], 'Full-Stack Web Development with Laravel', 55, false);
        $enroll($extraStudents['juan.student'], 'Database Fundamentals', 60, false);
        $enroll($extraStudents['maria.santos'], 'Introduction to Artificial Intelligence', 100, true);
        $enroll($extraStudents['maria.santos'], 'Machine Learning Essentials', 100, true);
        $enroll($extraStudents['maria.santos'], 'Computer Networking Fundamentals', 40, false);
        $enroll($extraStudents['jose.mercado'], 'Computer Networking Fundamentals', 20, false);
        $enroll($extraStudents['andres.bonifacio'], 'Database Fundamentals', 100, true);
        $enroll($extraStudents['andres.bonifacio'], 'Full-Stack Web Development with Laravel', 55, false);
        $enroll($extraStudents['gabriela.silang'], 'Introduction to Computing', 10, false);
        $enroll($extraStudents['emilio.aguinaldo'], 'IT Projects Management', 100, true);
        $enroll($extraStudents['melchora.aquino'], 'Web Development Fundamentals', 30, false);
        $enroll($extraStudents['melchora.aquino'], 'Data Management Essentials', 15, false);
        $enroll($extraStudents['antonio.luna'], 'Computer Organization Basics', 100, true);
        $enroll($extraStudents['antonio.luna'], 'Introduction to Computing', 50, false);

        // Keep the denormalised counters truthful.
        foreach ($courseIds as $cid) {
            DB::table('courses')->where('id', $cid)->update([
                'enrolled_count' => DB::table('enrollments')->where('course_id', $cid)->count(),
            ]);
        }

        // ── User badges ───────────────────────────────────────────────────
        // NOTE: no badges are pre-awarded. Every "Earned" counter starts at
        // 0 and climbs in real time as students actually earn badges.

        // ── Certificates (Ana) ────────────────────────────────────────────
        foreach ([
            'AWS Certified Solutions Architect',
            'CompTIA A+',
            'CompTIA Network+',
            'CompTIA Security+',
            'Cisco CCNA',
            'Microsoft Certified: Azure Fundamentals',
            'Oracle Certified Professional: Java SE',
            'Google IT Support Professional',
            'Certified Kubernetes Administrator',
            'PMP - Project Management Professional',
            'Certified Ethical Hacker (CEH)',
            'Microsoft Certified: Azure Developer Associate',
        ] as $i => $title) {
            DB::table('certificates')->insert([
                'user_id' => $ana->id, 'course_id' => $externalCourseId,
                'title' => $title, 'file_path' => null,
                'issued_at' => now()->subDays(30 + $i * 7),
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // ── Quiz attempts (feeds the faculty quiz-average column) ─────────
        $quizIds = DB::table('quizzes')->pluck('id', 'title');
        DB::table('quiz_attempts')->insert([
            ['user_id' => $ana->id,                        'quiz_id' => $quizIds['Laravel Fundamentals Quiz'], 'score' => 100, 'passed' => true,  'started_at' => now()->subDays(2), 'submitted_at' => now()->subDays(2), 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $extraStudents['kurt.palavino']->id, 'quiz_id' => $quizIds['Laravel Fundamentals Quiz'], 'score' => 66,  'passed' => false, 'started_at' => now()->subDay(),   'submitted_at' => now()->subDay(),   'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $extraStudents['anna.reyes']->id,    'quiz_id' => $quizIds['Database & Eloquent ORM'],   'score' => 100, 'passed' => true,  'started_at' => now()->subDays(3), 'submitted_at' => now()->subDays(3), 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ── Lesson completions (drives progress / analytics) ──────────────
        $laravelLessonIds = DB::table('course_lessons')->where('course_id', $laravelId)->orderBy('order')->pluck('id');
        foreach ($laravelLessonIds->take(2) as $lessonId) {
            DB::table('lesson_completions')->insertOrIgnore([
                'user_id' => $ana->id, 'lesson_id' => $lessonId,
                'completed_at' => now()->subDays(2), 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // ── Pathway ───────────────────────────────────────────────────────
        $pathwayId = DB::table('pathways')->insertGetId([
            'name' => 'Full Stack Web Developer',
            'description' => 'A guided pathway from web basics to full-stack mastery.',
            'is_active' => true,
            'steps' => json_encode([
                ['label' => 'Goal 1', 'title' => 'Web Development', 'color' => '#2DD4CF', 'status' => 'completed'],
                ['label' => 'Goal 2', 'title' => 'Laravel',         'color' => '#D8C84A', 'status' => 'completed'],
                ['label' => 'Goal 3', 'title' => 'SQL',             'color' => '#E5483D', 'status' => 'current'],
                ['label' => 'Goal 4', 'title' => 'Locked',          'color' => '#9CA3AF', 'status' => 'locked'],
            ]),
            'destination' => 'Full Stack Web Developer',
            'destination_color' => '#5FD93D',
            'connector_color' => '#2563EB',
            'recommendations' => json_encode([
                ['title' => 'Take Blade Courses', 'completion' => 0],
                ['title' => 'Take SQL Courses',   'completion' => 0],
                ['title' => 'Networking Course',  'completion' => 0],
                ['title' => 'HTML & CSS',         'completion' => 0],
            ]),
            'desired_title' => 'Data Analyst',
            // Full skill list the pathway requires — the page computes
            // current/missing competencies and readiness per student.
            'desired_competencies' => json_encode(['Networking', 'HTML & CSS', 'Python', 'Statistics', 'SQL']),
            'current_competencies' => json_encode([]),
            'missing_competencies' => json_encode([]),
            'readiness_percent' => 0,
            'readiness_label' => 'Data Analytics',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        DB::table('pathway_courses')->insertOrIgnore([
            ['pathway_id' => $pathwayId, 'course_id' => $laravelId, 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['pathway_id' => $pathwayId, 'course_id' => $courseIds['Database Fundamentals'], 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ── Faculty registration codes (admin-sharable) ──────────────────
        // One already consumed by the seeded faculty account (shows RED),
        // the rest available to share (show GREEN).
        DB::table('faculty_codes')->insertOrIgnore([
            ['code' => 'FAC-DEMO01', 'created_by' => $admin->id, 'used_by' => $faculty->id, 'used_at' => now()->subDays(10), 'created_at' => now()->subDays(10), 'updated_at' => now()->subDays(10)],
            ['code' => 'FAC-7K2M9P', 'created_by' => $admin->id, 'used_by' => null, 'used_at' => null, 'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)],
            ['code' => 'FAC-4X8N2Q', 'created_by' => $admin->id, 'used_by' => null, 'used_at' => null, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],
            ['code' => 'FAC-9T3W6R', 'created_by' => $admin->id, 'used_by' => null, 'used_at' => null, 'created_at' => now()->subDay(), 'updated_at' => now()->subDay()],
        ]);

        // ── Notifications ─────────────────────────────────────────────────
        $notify = function (User $user, string $title, string $message, string $type, bool $unread, Carbon $when) {
            DB::table('notifications')->insert([
                'user_id' => $user->id, 'title' => $title, 'message' => $message,
                'type' => $type, 'is_read' => ! $unread,
                'created_at' => $when, 'updated_at' => $when,
            ]);
        };

        foreach ([$ana, $faculty, $admin] as $u) {
            $notify($u, 'Course update available', 'A new lesson was added to your enrolled course.', 'course', true, now()->subMinutes(10));
            $notify($u, 'New announcement', 'The faculty team posted a new milestone update.', 'announcement', true, now()->subHour());
            $notify($u, 'System reminder', 'Your badge portfolio was refreshed.', 'system', false, now()->subDay());
        }

        // ── Announcements ─────────────────────────────────────────────────
        DB::table('announcements')->insert([
            ['title' => 'Enrollment is now open', 'body' => 'Secure your slot for the upcoming semester.', 'created_by' => $admin->id, 'is_published' => true, 'published_at' => now()->subDays(2), 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'New microcredential tracks', 'body' => 'Stackable badges are live for Web Development and AI.', 'created_by' => $admin->id, 'is_published' => true, 'published_at' => now()->subDays(5), 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'Platform maintenance', 'body' => 'Scheduled maintenance this weekend, 10 PM - 12 AM.', 'created_by' => $admin->id, 'is_published' => true, 'published_at' => now()->subDays(8), 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ── Analytics events (feeds /monitoring/live) ─────────────────────
        DB::table('analytics_events')->insert([
            ['user_id' => $ana->id, 'event_type' => 'enrollment', 'entity_type' => 'course', 'entity_id' => $laravelId, 'metadata' => json_encode(['detail' => 'Ana Lopez joined the Full-Stack course']), 'occurred_at' => now()->subMinutes(2), 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $extraStudents['andres.bonifacio']->id, 'event_type' => 'badge_issued', 'entity_type' => 'badge', 'entity_id' => $badgeIds['Database Master'], 'metadata' => json_encode(['detail' => 'Database Master badge awarded']), 'occurred_at' => now()->subMinutes(12), 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $extraStudents['maria.santos']->id, 'event_type' => 'course_completed', 'entity_type' => 'course', 'entity_id' => $courseIds['Machine Learning Essentials'], 'metadata' => json_encode(['detail' => 'Maria Clara Santos marked a milestone']), 'occurred_at' => now()->subMinutes(24), 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ── Competency framework (minimal, functional) ────────────────────
        $catId = DB::table('competency_categories')->insertGetId([
            'name' => 'Web Development', 'description' => 'Core web development competencies.',
            'color' => '#2DD4CF', 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach ([['Laravel', 1], ['SQL', 2], ['HTML & CSS', 3]] as [$unit, $order]) {
            $unitId = DB::table('competency_units')->insertGetId([
                'competency_category_id' => $catId, 'title' => $unit,
                'description' => $unit.' competency unit', 'order' => $order,
                'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('competency_levels')->insert([
                ['competency_unit_id' => $unitId, 'title' => 'Basic', 'level_number' => 1, 'points' => 100, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['competency_unit_id' => $unitId, 'title' => 'Intermediate', 'level_number' => 2, 'points' => 200, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['competency_unit_id' => $unitId, 'title' => 'Advanced', 'level_number' => 3, 'points' => 300, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    /**
     * Create (or fetch) a user with a fixed user_code and hashed password.
     */
    private function makeUser(array $data): User
    {
        return User::updateOrCreate(
            ['email' => $data['email']],
            [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'username' => $data['username'],
                'password' => bcrypt('password'),
                'role_id' => $data['role_id'],
                'student_id' => $data['code'],
                'user_code' => $data['code'],
                'phone' => $data['phone'] ?? null,
                'location' => $data['location'] ?? null,
                'avatar_url' => null,
                'is_active' => true,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'education' => $data['education'] ?? null,
                'about' => $data['about'] ?? null,
                'bio' => $data['bio'] ?? null,
                'language' => $data['language'] ?? 'English',
                'timezone' => $data['timezone'] ?? null,
                'profile_completed' => true,
            ]
        );
    }

    /**
     * Seed one module with its lessons and one quiz (with questions).
     */
    private function seedModule(int $courseId, int $order, string $title, string $subtitle, array $lessons, array $quizSpec): void
    {
        $moduleId = DB::table('course_modules')->insertGetId([
            'course_id' => $courseId, 'title' => $title, 'description' => $subtitle,
            'order' => $order, 'created_at' => now(), 'updated_at' => now(),
        ]);

        foreach ($lessons as $i => [$lessonTitle, $type, $duration, $content]) {
            DB::table('course_lessons')->insert([
                'course_id' => $courseId, 'module_id' => $moduleId,
                'title' => $lessonTitle, 'type' => $type, 'duration' => $duration,
                'content' => $content, 'order' => $i + 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        [$quizTitle, $passing, $questions] = $quizSpec;
        $quizId = DB::table('quizzes')->insertGetId([
            'course_id' => $courseId, 'module_id' => $moduleId,
            'title' => $quizTitle, 'description' => $subtitle,
            'passing_score' => $passing, 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        foreach ($questions as [$question, $options, $correct]) {
            DB::table('quiz_questions')->insert([
                'quiz_id' => $quizId, 'question' => $question, 'type' => 'Multiple Choice',
                'options' => json_encode($options), 'correct_answer' => $correct, 'points' => 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // Keep courses.lessons_count truthful.
        DB::table('courses')->where('id', $courseId)->update([
            'lessons_count' => DB::table('course_lessons')->where('course_id', $courseId)->count(),
        ]);
    }
}
