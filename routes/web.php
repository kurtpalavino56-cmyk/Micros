<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\FacultyAnalyticsReportController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\StudentController;
use App\Http\Middleware\PreventBackHistory;
use App\Http\Middleware\RoleBasedAccess;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

// ══════════════════════════════════════════════════════════════════════════
// UPSKILL — route map
//
// Every URL and route NAME is identical to the original file, so all of
// the Blade views (which reference routes by name) work unchanged.
// The only difference: the dummy/session closures have been replaced by
// real controllers backed by the database.
// ══════════════════════════════════════════════════════════════════════════

// ── STUDENT VIEW COMPOSER ─────────────────────────────────────────────────
// Kept from the original routes file: merges any session-stored student
// profile edits into the $user object on Student_* pages. Profile updates
// are now persisted to the database (and the session key is cleared), so
// this simply has nothing to merge during normal operation — but it stays
// as a safety net so the topbar never breaks.
app('view')->composer('Student_*', function (View $view) {
    try {
        $saved = session('profile_data', []);
    } catch (Throwable $e) {
        return; // session not available yet (e.g. during boot)
    }

    if (empty($saved)) {
        return;
    }

    $data = $view->getData();
    if (! isset($data['user'])) {
        return;
    }

    $user = $data['user'];

    foreach (['name', 'role', 'avatar_url', 'email', 'phone', 'location'] as $field) {
        if (array_key_exists($field, $saved) && ! empty($saved[$field])) {
            $user->$field = $saved[$field];
        }
    }

    $view->with('user', $user);
});

// ── Public pages ──────────────────────────────────────────────────────────

Route::get('/', [PageController::class, 'homepage'])->name('Homepage');

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

// Faculty registration — requires a valid, unused Faculty Code from the Admin
Route::get('/faculty-register', [RegisterController::class, 'showFaculty'])->name('faculty.register');
Route::post('/faculty-register', [RegisterController::class, 'storeFaculty'])->name('faculty.register.store');

// Students directory — public, reached from the homepage nav. Lists only
// students who are actually enrolled in at least one course.
Route::get('/students', [PageController::class, 'students'])->name('students.index');

// Navbar section links → smooth-scroll targets on the homepage.
Route::get('/announcements', [PageController::class, 'announcementsRedirect']);
Route::get('/microcredentials', [PageController::class, 'microcredentialsRedirect']);

// Public course catalog — "View all Courses" on the homepage.
Route::get('/explore', [PageController::class, 'explore'])->name('explore');
Route::get('/explore/courses/{id}', [PageController::class, 'publicCourse'])
    ->whereNumber('id')->name('public.courses.show');

// PreventBackHistory: this page shows the signed-in user's activity, so it
// must not sit in the browser cache after logout.
Route::get('/notifications', [PageController::class, 'notifications'])
    ->middleware(PreventBackHistory::class)
    ->name('notifications.index');
Route::middleware(['auth', PreventBackHistory::class])
    ->post('/notifications/read-all', [PageController::class, 'markAllRead'])
    ->name('notifications.readAll');
Route::middleware(['auth', PreventBackHistory::class])
    ->get('/notifications/preview', [PageController::class, 'notificationPreview'])
    ->name('notifications.preview');
Route::get('/search', [PageController::class, 'search'])->name('search');

// Help Center — the footer has always linked to /help, but nothing was
// routed there (a 404). Students land in their inbox, where they can raise a
// complaint; anyone else is asked to sign in first.
Route::get('/help', [PageController::class, 'help'])->name('help');
Route::post('/help', [PageController::class, 'storeHelpMessage'])->name('help.store');

// Legal pages. The footer has linked to /privacy and /terms since it was
// written, but no routes existed, so both links returned 404. These are
// static views, so they are closures rather than controller actions.
// Certificate verification. The QR encodes /?certificate=SERIAL so a scan
// lands on the real homepage with the certificate floating over it, rather
// than on an isolated document page.
Route::get('/verify/{serial}', [PageController::class, 'verifyCertificate'])->name('certificate.verify');

Route::view('/privacy', 'public.privacy')->name('privacy');
Route::view('/terms', 'public.terms')->name('terms');
// ── Password reset ────────────────────────────────────────────────────
// "Forgot Password?" on the login screen. Three steps: email -> 6-digit
// code -> new password. The code is emailed through EmailJS from the
// SERVER (see App\Services\EmailJsMailer) and stored hashed in the
// password_reset_tokens table.
//
// Throttled because these endpoints email real people and check a 6-digit
// secret: without a limit, the code could simply be guessed by brute force.
Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendCode'])
    ->middleware('throttle:6,1')->name('password.email');

Route::get('/forgot-password/verify', [PasswordResetController::class, 'showVerify'])->name('password.verify');
Route::post('/forgot-password/verify', [PasswordResetController::class, 'verify'])
    ->middleware('throttle:10,1')->name('password.verify.submit');
Route::post('/forgot-password/resend', [PasswordResetController::class, 'resend'])
    ->middleware('throttle:3,1')->name('password.resend');

Route::get('/reset-password', [PasswordResetController::class, 'showReset'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');
Route::get('/monitoring/live', [PageController::class, 'monitoringLive'])->name('monitoring.live');

// ══════════════════════════════════════════════════════════════════════════
// ADMIN ROUTES
// ══════════════════════════════════════════════════════════════════════════

Route::middleware([PreventBackHistory::class, RoleBasedAccess::class.':admin'])->group(function () {
    Route::get('/Admin-dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    Route::get('/Admin-profile', [AdminController::class, 'profile'])->name('admin.profile');
    Route::patch('/Admin-profile', [AdminController::class, 'updateProfile'])->name('admin.profile.update');

    Route::get('/Admin-usermanagement', [AdminController::class, 'userManagement'])->name('admin.usermanagement');
    Route::post('/Admin-usermanagement/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::get('/Admin-usermanagement/users/{id}', [AdminController::class, 'showUser'])->whereNumber('id')->name('admin.users.show');
    // #2 — delete a student / faculty account
    Route::post('/Admin-usermanagement/users/{id}/delete', [AdminController::class, 'destroyUser'])
        ->whereNumber('id')->name('admin.users.destroy');

    Route::get('/Admin-facultycodes', [AdminController::class, 'facultyCodes'])->name('admin.facultycodes');
    Route::post('/Admin-facultycodes', [AdminController::class, 'generateFacultyCode'])->name('admin.facultycodes.generate');
    Route::post('/Admin-facultycodes/{id}/delete', [AdminController::class, 'deleteFacultyCode'])
        ->whereNumber('id')->name('admin.facultycodes.delete');

    Route::get('/Admin-courses', [AdminController::class, 'courses'])->name('admin.courses');
    Route::get('/Admin-courses/{id}', [AdminController::class, 'showCourse'])
        ->whereNumber('id')->name('admin.courses.show');
    Route::post('/Admin-courses/{id}/enrollments/{enrollment}/academic-confirm', [AdminController::class, 'academicConfirmEnrollment'])
        ->whereNumber(['id', 'enrollment'])->name('admin.enrollments.academic-confirm');
    Route::post('/Admin-courses/{id}/approve', [AdminController::class, 'approveCourse'])
        ->whereNumber('id')->name('admin.courses.approve');
    Route::post('/Admin-courses/{id}/deny', [AdminController::class, 'denyCourse'])
        ->whereNumber('id')->name('admin.courses.deny');
    Route::post('/Admin-courses/{id}/delete', [AdminController::class, 'destroyCourse'])
        ->whereNumber('id')->name('admin.courses.destroy');
    Route::post('/Admin-courses/{id}/publish', [AdminController::class, 'togglePublishCourse'])
        ->whereNumber('id')->name('admin.courses.publish');
    Route::post('/Admin-courses/{id}/feature', [AdminController::class, 'toggleFeatureCourse'])
        ->whereNumber('id')->name('admin.courses.feature');
    // Program Categories — the list faculty pick from on the course form
    Route::get('/Admin-programcategories', [AdminController::class, 'programCategories'])
        ->name('admin.categories');
    Route::post('/Admin-programcategories', [AdminController::class, 'storeProgramCategory'])
        ->name('admin.categories.store');
    Route::patch('/Admin-programcategories/{id}', [AdminController::class, 'updateProgramCategory'])
        ->whereNumber('id')->name('admin.categories.update');
    Route::post('/Admin-programcategories/{id}/toggle', [AdminController::class, 'toggleProgramCategory'])
        ->whereNumber('id')->name('admin.categories.toggle');
    Route::post('/Admin-programcategories/{id}/delete', [AdminController::class, 'destroyProgramCategory'])
        ->whereNumber('id')->name('admin.categories.destroy');

    Route::get('/Admin-pathways', [AdminController::class, 'pathways'])->name('admin.pathways');
    Route::post('/Admin-pathways', [AdminController::class, 'storePathway'])->name('admin.pathways.store');
    Route::patch('/Admin-pathways/{id}', [AdminController::class, 'updatePathway'])
        ->whereNumber('id')->name('admin.pathways.update');
    Route::post('/Admin-pathways/{id}/delete', [AdminController::class, 'destroyPathway'])
        ->whereNumber('id')->name('admin.pathways.destroy');

    Route::get('/Admin-stacking-frameworks', [AdminController::class, 'stackingFrameworks'])
        ->name('admin.stacking-frameworks');
    Route::post('/Admin-stacking-frameworks', [AdminController::class, 'storeStackingFramework'])
        ->name('admin.stacking-frameworks.store');
    Route::patch('/Admin-stacking-frameworks/{id}', [AdminController::class, 'updateStackingFramework'])
        ->whereNumber('id')->name('admin.stacking-frameworks.update');
    Route::post('/Admin-stacking-frameworks/{id}/submit', [AdminController::class, 'submitStackingFramework'])
        ->whereNumber('id')->name('admin.stacking-frameworks.submit');
    Route::post('/Admin-stacking-frameworks/{id}/approve', [AdminController::class, 'approveStackingFramework'])
        ->whereNumber('id')->name('admin.stacking-frameworks.approve');
    Route::post('/Admin-stacking-frameworks/{id}/deactivate', [AdminController::class, 'deactivateStackingFramework'])
        ->whereNumber('id')->name('admin.stacking-frameworks.deactivate');

    Route::get('/Admin-academic-credit-recognition', [AdminController::class, 'academicCreditRecognitionRequests'])
        ->name('admin.academic-credit-recognition');
    Route::post('/Admin-academic-credit-recognition/{id}/recommend', [AdminController::class, 'recommendAcademicCreditRecognition'])
        ->whereNumber('id')->name('admin.academic-credit-recognition.recommend');
    Route::post('/Admin-academic-credit-recognition/{id}/endorse', [AdminController::class, 'endorseAcademicCreditRecognition'])
        ->whereNumber('id')->name('admin.academic-credit-recognition.endorse');
    Route::post('/Admin-academic-credit-recognition/{id}/record', [AdminController::class, 'recordAcademicCreditRecognition'])
        ->whereNumber('id')->name('admin.academic-credit-recognition.record');
    Route::post('/Admin-academic-credit-recognition/{id}/deny', [AdminController::class, 'denyAcademicCreditRecognition'])
        ->whereNumber('id')->name('admin.academic-credit-recognition.deny');

    Route::get('/Admin-report', [AdminController::class, 'report'])->name('admin.report');

    // #3 — Announcements (sidebar item under Analytics)
    Route::get('/Admin-announcements', [AdminController::class, 'announcements'])->name('admin.announcements');
    Route::post('/Admin-announcements', [AdminController::class, 'storeAnnouncement'])->name('admin.announcements.store');
    Route::patch('/Admin-announcements/{id}', [AdminController::class, 'updateAnnouncement'])
        ->whereNumber('id')->name('admin.announcements.update');
    Route::post('/Admin-announcements/{id}/delete', [AdminController::class, 'destroyAnnouncement'])
        ->whereNumber('id')->name('admin.announcements.destroy');

    // #4 — Complaint inbox from the Help Center
    Route::get('/Admin-complaints', [AdminController::class, 'complaints'])->name('admin.complaints');
    Route::post('/Admin-complaints/{id}/reply', [AdminController::class, 'replyComplaint'])
        ->whereNumber('id')->name('admin.complaints.reply');
    Route::post('/Admin-complaints/{id}/resolve', [AdminController::class, 'resolveComplaint'])
        ->whereNumber('id')->name('admin.complaints.resolve');
});

// ══════════════════════════════════════════════════════════════════════════
// STUDENT ROUTES
// ══════════════════════════════════════════════════════════════════════════

Route::middleware([PreventBackHistory::class, RoleBasedAccess::class.':student'])->group(function () {
    Route::get('/courses', [StudentController::class, 'coursesIndexRedirect'])->name('courses.index');

    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
    Route::get('/onboarding', [StudentController::class, 'onboarding'])->name('onboarding.show');

    Route::get('/courses/browse', [StudentController::class, 'browse'])->name('courses.browse');
    Route::get('/courses/{id}', [StudentController::class, 'show'])->whereNumber('id')->name('courses.show');
    Route::post('/courses/{id}/enroll', [StudentController::class, 'enroll'])->whereNumber('id')->name('courses.enroll');
    Route::get('/courses/{id}/learn', [StudentController::class, 'learn'])->whereNumber('id')->name('courses.learn');
    Route::post('/courses/{id}/progress', [StudentController::class, 'saveProgress'])
        ->whereNumber('id')->name('courses.progress');
    Route::post('/courses/{id}/complete', [StudentController::class, 'completeCourse'])
        ->whereNumber('id')->name('courses.complete');
    Route::get('/courses/{courseId}/lesson/{lessonId}', [StudentController::class, 'lesson'])
        ->whereNumber('courseId')->whereNumber('lessonId')->name('courses.lesson');
    Route::get('/quiz/{id}', [StudentController::class, 'quiz'])->whereNumber('id')->name('quiz.show');

    Route::get('/courses/enrolled', [StudentController::class, 'enrolledCourses'])->name('courses.enrolled');
    Route::get('/badges', [StudentController::class, 'badges'])->name('badges.index');
    Route::get('/certificates', [StudentController::class, 'certificates'])->name('certificates.index');
    Route::get('/certificates/{serial}', [StudentController::class, 'viewCertificate'])->name('certificates.view');
    Route::get('/certificates/{serial}/download', [StudentController::class, 'downloadCertificate'])->name('certificates.download');

    Route::get('/profile', [StudentController::class, 'profile'])->name('profile.show');
    Route::patch('/profile', [StudentController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/complete', [StudentController::class, 'completeProfile'])->name('profile.complete');

    Route::get('/pathways', [StudentController::class, 'pathways'])->name('pathways.index');
    Route::get('/stacking-progress', [StudentController::class, 'stackingProgress'])->name('stacking.progress');
    Route::post('/stacking-progress/{frameworkId}/recognition', [StudentController::class, 'requestAcademicCreditRecognition'])
        ->whereNumber('frameworkId')->name('stacking.recognition.request');
    Route::get('/analytics', [StudentController::class, 'analytics'])->name('analytics.index');

    // #4 — Help Centre inbox (envelope beside the notification bell)
    Route::get('/inbox', [StudentController::class, 'inbox'])->name('inbox.index');
    Route::post('/inbox', [StudentController::class, 'storeComplaint'])->name('inbox.store');
    Route::post('/inbox/{id}/reply', [StudentController::class, 'replyComplaint'])
        ->whereNumber('id')->name('inbox.reply');
});

// ══════════════════════════════════════════════════════════════════════════
// FACULTY ROUTES
// ══════════════════════════════════════════════════════════════════════════

Route::middleware([PreventBackHistory::class, RoleBasedAccess::class.':faculty'])->group(function () {
    Route::get('/Faculty-dashboard', [FacultyController::class, 'dashboard'])->name('faculty.dashboard');

    Route::get('/Faculty-profile', [FacultyController::class, 'profile'])->name('faculty.profile');
    Route::patch('/Faculty-profile', [FacultyController::class, 'updateProfile'])->name('faculty.profile.update');

    Route::get('/Faculty-analytics', [FacultyController::class, 'analytics'])->name('faculty.analytics');
    // Download the whole Faculty Analytics page as a PDF report
    Route::get('/Faculty-analytics/report', [FacultyAnalyticsReportController::class, 'download'])
        ->name('faculty.analytics.report');

    // Faculty inbox — announcements addressed to faculty by the admin
    Route::get('/Faculty-inbox', [FacultyController::class, 'inbox'])->name('faculty.inbox');
    Route::post('/Faculty-inbox/read-all', [FacultyController::class, 'markInboxRead'])->name('faculty.inbox.readAll');

    Route::get('/Faculty-students', [FacultyController::class, 'students'])->name('faculty.students');

    Route::get('/Faculty-mycourses', [FacultyController::class, 'courses'])->name('faculty.courses');
    Route::get('/Faculty-mycourses/manage/{id?}', [FacultyController::class, 'manage'])
        ->whereNumber('id')->name('faculty.courses.manage');
    Route::post('/Faculty-enrollments/{enrollment}/verify', [FacultyController::class, 'verifyEnrollment'])
        ->whereNumber('enrollment')->name('faculty.enrollments.verify');

    Route::get('/Faculty-createcourse', [FacultyController::class, 'createForm'])->name('faculty.create');
    Route::post('/Faculty-createcourse', [FacultyController::class, 'createStore'])->name('faculty.create.store');
    Route::post('/Faculty-editor/upload', [FacultyController::class, 'uploadEditorAsset'])->name('faculty.editor.upload');

    // Edit an existing course — same form as Create, pre-filled.
    Route::get('/Faculty-mycourses/{id}/edit', [FacultyController::class, 'editForm'])
        ->whereNumber('id')->name('faculty.courses.edit');
    Route::patch('/Faculty-mycourses/{id}', [FacultyController::class, 'updateCourse'])
        ->whereNumber('id')->name('faculty.courses.update');

    Route::post('/Faculty-mycourses/manage/{id}/modules', [FacultyController::class, 'storeModule'])
        ->whereNumber('id')->name('faculty.module.store');
    Route::post('/Faculty-mycourses/manage/{id}/modules/{key}/delete', [FacultyController::class, 'deleteModule'])
        ->whereNumber('id')->name('faculty.module.delete');

    Route::post('/Faculty-mycourses/manage/{id}/modules/{moduleIndex}/lessons', [FacultyController::class, 'storeLesson'])
        ->whereNumber('id')->whereNumber('moduleIndex')->name('faculty.lesson.store');
    // Edit an existing lesson (title / description / duration / file)
    Route::post('/Faculty-mycourses/manage/{id}/modules/{moduleIndex}/lessons/{lessonId}/update', [FacultyController::class, 'updateLesson'])
        ->whereNumber('id')->whereNumber('moduleIndex')->whereNumber('lessonId')->name('faculty.lesson.update');
    Route::post('/Faculty-mycourses/manage/{id}/modules/{moduleIndex}/lessons/{key}/delete', [FacultyController::class, 'deleteLesson'])
        ->whereNumber('id')->whereNumber('moduleIndex')->name('faculty.lesson.delete');

    Route::get('/Faculty-mycourses/manage/{id}/modules/{moduleIndex}/quiz/create', [FacultyController::class, 'quizCreate'])
        ->whereNumber('id')->whereNumber('moduleIndex')->name('faculty.quiz.create');
    Route::post('/Faculty-mycourses/manage/{id}/modules/{moduleIndex}/quiz', [FacultyController::class, 'storeQuiz'])
        ->whereNumber('id')->whereNumber('moduleIndex')->name('faculty.quiz.store');
    // #8 — delete a module's quiz
    Route::post('/Faculty-mycourses/manage/{id}/modules/{moduleIndex}/quiz/delete', [FacultyController::class, 'destroyQuiz'])
        ->whereNumber('id')->whereNumber('moduleIndex')->name('faculty.quiz.destroy');

});
