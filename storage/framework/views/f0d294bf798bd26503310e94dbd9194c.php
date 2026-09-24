<?php $__env->startSection('title', 'Students | UpSkill – PSU Microcredentials'); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* ── Students page header ── */
.students-hero {
    background: linear-gradient(170deg, var(--navy) 0%, var(--navy-dark) 55%, #0e2b8e 100%);
    padding: 3.5rem 0 4.5rem;
    text-align: center;
}
.students-hero__title {
    font-family: var(--font-display);
    font-size: 2.4rem;
    font-weight: 800;
    color: var(--white);
    margin-bottom: 0.4rem;
}
.students-hero__sub {
    color: rgba(255,255,255,0.75);
    font-size: 0.98rem;
    margin-bottom: 2rem;
}

/* Search bar */
.student-search {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    background: var(--white);
    border-radius: 50px;
    padding: 0.45rem 0.45rem 0.45rem 1.4rem;
    max-width: 560px;
    margin: 0 auto;
    box-shadow: 0 10px 30px rgba(0,0,0,0.22);
}
.student-search svg {
    width: 20px; height: 20px;
    color: var(--text-muted);
    flex-shrink: 0;
}
.student-search__input {
    flex: 1;
    border: none;
    outline: none;
    background: none;
    font-family: var(--font-body);
    font-size: 0.95rem;
    color: var(--navy);
    min-width: 0;
}
.student-search__input::placeholder { color: var(--text-muted); opacity: 0.7; }
.student-search__btn {
    background: var(--gold);
    color: var(--navy-dark);
    border: none;
    border-radius: 50px;
    font-weight: 700;
    font-size: 0.88rem;
    padding: 0.6rem 1.6rem;
    cursor: pointer;
    transition: background var(--transition), box-shadow var(--transition);
    flex-shrink: 0;
}
.student-search__btn:hover { background: var(--gold-light); box-shadow: 0 6px 18px rgba(232,168,0,0.4); }
.students-hero__hint {
    margin-top: 0.85rem;
    font-size: 0.8rem;
    color: rgba(255,255,255,0.55);
}
.students-hero__hint code {
    background: rgba(255,255,255,0.12);
    border-radius: 6px;
    padding: 0.1rem 0.45rem;
    font-size: 0.78rem;
    color: var(--gold-light);
}

/* ── Directory section ── */
.students-section { padding: 3.5rem 0 5rem; }
.students-section__head {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.8rem;
    flex-wrap: wrap;
}
.students-count { font-size: 0.9rem; color: var(--text-muted); font-weight: 500; }

/* Grid of student cards */
.students-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 1.4rem;
}
.student-card {
    background: var(--card-bg);
    border: 1px solid rgba(10,31,110,0.08);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-card);
    padding: 1.6rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: transform var(--transition), box-shadow var(--transition);
}
.student-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 30px rgba(10,31,110,0.16);
}
.student-card__avatar {
    width: 52px; height: 52px;
    border-radius: 50%;
    background: var(--navy);
    color: var(--gold-light);
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 1.05rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    letter-spacing: 0.02em;
}
.student-card__name {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 1rem;
    color: var(--navy);
    margin-bottom: 0.35rem;
}
.student-card__id {
    display: inline-block;
    background: rgba(232,168,0,0.14);
    color: var(--navy);
    border: 1px solid rgba(232,168,0,0.45);
    border-radius: 50px;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    padding: 0.18rem 0.75rem;
    font-variant-numeric: tabular-nums;
}

/* Empty state */
.students-empty {
    display: none;
    text-align: center;
    padding: 4rem 1rem;
    color: var(--text-muted);
}
.students-empty__title {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 1.15rem;
    color: var(--navy);
    margin-bottom: 0.4rem;
}
.students-empty p { font-size: 0.9rem; }

/* ── Student profile modal ── */
.student-card { cursor: pointer; }
.student-card:focus-visible { outline: 3px solid var(--gold); outline-offset: 2px; }
.student-card__avatar-img {
    width: 52px; height: 52px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

.student-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(7,21,80,0.55);
    z-index: 2000;
    align-items: center;
    justify-content: center;
    padding: 1.2rem;
}
.student-modal-overlay.open { display: flex; }

.student-modal {
    background: var(--white);
    border-radius: var(--radius-lg);
    width: 100%;
    max-width: 520px;
    max-height: 88vh;
    overflow-y: auto;
    box-shadow: 0 24px 60px rgba(0,0,0,0.35);
    position: relative;
}
.student-modal__close {
    position: absolute;
    top: 0.8rem; right: 1rem;
    background: none;
    border: none;
    color: rgba(255,255,255,0.85);
    font-size: 1.7rem;
    line-height: 1;
    cursor: pointer;
    z-index: 1;
}
.student-modal__close:hover { color: var(--gold-light); }

.student-modal__header {
    background: linear-gradient(170deg, var(--navy) 0%, var(--navy-dark) 100%);
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    padding: 1.8rem 1.8rem 1.6rem;
    display: flex;
    align-items: center;
    gap: 1.1rem;
}
.student-modal__avatar {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: var(--gold);
    color: var(--navy-dark);
    font-family: var(--font-display);
    font-weight: 800;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 3px solid rgba(255,255,255,0.35);
    overflow: hidden;
}
.student-modal__avatar img { width: 100%; height: 100%; object-fit: cover; }
.student-modal__name {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 1.25rem;
    color: var(--white);
    margin-bottom: 0.4rem;
}
.student-modal__header .student-card__id {
    color: var(--white);
    background: rgba(255,255,255,0.12);
    border-color: rgba(232,168,0,0.7);
}

.student-modal__body { padding: 1.5rem 1.8rem 1.8rem; }
.student-modal__section-title {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--navy);
    margin-bottom: 0.9rem;
}

/* Course rows */
.course-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.9rem;
    border: 1px solid rgba(10,31,110,0.12);
    border-radius: var(--radius-md);
    padding: 0.85rem 1rem;
    margin-bottom: 0.7rem;
    background: var(--white);
}
.course-row__title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--navy);
}

/* Completed course → glowing green + Completed badge + QR code */
.course-row.completed {
    border-color: #22c55e;
    background: #f0fdf4;
    box-shadow: 0 0 12px rgba(34,197,94,0.55);
    animation: courseGlow 2s ease-in-out infinite;
}
@keyframes courseGlow {
    0%, 100% { box-shadow: 0 0 8px  rgba(34,197,94,0.40); }
    50%      { box-shadow: 0 0 18px rgba(34,197,94,0.75); }
}
@media (prefers-reduced-motion: reduce) {
    .course-row.completed { animation: none; }
}
.course-row__badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    background: #22c55e;
    color: var(--white);
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    padding: 0.22rem 0.7rem;
    margin-top: 0.35rem;
    text-transform: uppercase;
}
.course-row__qr {
    width: 58px; height: 58px;
    border-radius: 8px;
    border: 1px solid #bbf7d0;
    background: var(--white);
    padding: 3px;
    flex-shrink: 0;
}
.course-row--none {
    font-size: 0.88rem;
    color: var(--text-muted);
    padding: 0.5rem 0;
}

@media (max-width: 600px) {
    .students-hero__title { font-size: 1.8rem; }
    .student-search { flex-wrap: nowrap; }
    .student-search__btn { padding: 0.55rem 1.1rem; }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<?php
    $students = $students ?? collect([
        (object)['name' => 'Juan Dela Cruz',      'student_id' => '23-LN-0417', 'avatar_url' => null, 'courses' => [
            ['title' => 'Full-Stack Web Development with Laravel', 'completed' => true],
            ['title' => 'Database Fundamentals',                   'completed' => false],
        ]],
        (object)['name' => 'Maria Clara Santos',  'student_id' => '23-LN-1189', 'avatar_url' => null, 'courses' => [
            ['title' => 'Introduction to Artificial Intelligence', 'completed' => true],
            ['title' => 'Machine Learning Essentials',             'completed' => true],
            ['title' => 'Computer Networking Fundamentals',        'completed' => false],
        ]],
        (object)['name' => 'Jose Rizal Mercado',  'student_id' => '24-LN-0562', 'avatar_url' => null, 'courses' => [
            ['title' => 'Computer Networking Fundamentals',        'completed' => false],
        ]],
        (object)['name' => 'Andres Bonifacio',    'student_id' => '24-LN-2031', 'avatar_url' => null, 'courses' => [
            ['title' => 'Database Fundamentals',                   'completed' => true],
            ['title' => 'Full-Stack Web Development with Laravel', 'completed' => false],
        ]],
        (object)['name' => 'Gabriela Silang',     'student_id' => '25-LN-0748', 'avatar_url' => null, 'courses' => [
            ['title' => 'Introduction to Computing',               'completed' => false],
        ]],
        (object)['name' => 'Emilio Aguinaldo',    'student_id' => '25-LN-1394', 'avatar_url' => null, 'courses' => [
            ['title' => 'IT Projects Management',                  'completed' => true],
        ]],
        (object)['name' => 'Melchora Aquino',     'student_id' => '25-LN-2216', 'avatar_url' => null, 'courses' => [
            ['title' => 'Web Development Fundamentals',            'completed' => false],
            ['title' => 'Data Management Essentials',              'completed' => false],
        ]],
        (object)['name' => 'Antonio Luna',        'student_id' => '26-LN-0083', 'avatar_url' => null, 'courses' => [
            ['title' => 'Computer Organization Basics',            'completed' => true],
            ['title' => 'Introduction to Computing',               'completed' => false],
        ]],
        (object)['name' => 'Gregoria de Jesus',   'student_id' => '26-LN-0925', 'avatar_url' => null, 'courses' => []],
    ]);
?>


<section class="students-hero">
    <div class="container">
        <h1 class="students-hero__title">Students</h1>
        <p class="students-hero__sub">Look up an enrolled student by their Student ID.</p>

        <div class="student-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/>
            </svg>
            <input type="text"
                   id="studentSearchInput"
                   class="student-search__input"
                   placeholder="Search by Student ID…"
                   autocomplete="off">
            <button type="button" class="student-search__btn" onclick="filterStudents()">Search</button>
        </div>
        <p class="students-hero__hint">Student ID format: <code>23-LN-0417</code></p>
    </div>
</section>


<section class="students-section">
    <div class="container">

        <div class="students-section__head">
            <h2 class="section-title" style="margin-bottom:0;">Student Directory</h2>
            <span class="students-count" id="studentsCount"><?php echo e(count($students)); ?> students</span>
        </div>

        <div class="students-grid" id="studentsGrid">
            <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $parts = preg_split('/\s+/', trim($student->name));
                    $initials = strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
                ?>
                <div class="student-card"
                     role="button"
                     tabindex="0"
                     onclick="openStudentModal(<?php echo e($i); ?>)"
                     onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openStudentModal(<?php echo e($i); ?>);}"
                     data-student-id="<?php echo e(strtolower($student->student_id)); ?>"
                     data-student-name="<?php echo e(strtolower($student->name)); ?>">
                    <?php if($student->avatar_url ?? null): ?>
                        <img class="student-card__avatar-img" src="<?php echo e($student->avatar_url); ?>" alt="<?php echo e($student->name); ?>">
                    <?php else: ?>
                        <div class="student-card__avatar"><?php echo e($initials); ?></div>
                    <?php endif; ?>
                    <div>
                        <div class="student-card__name"><?php echo e($student->name); ?></div>
                        <span class="student-card__id"><?php echo e($student->student_id); ?></span>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="students-empty" id="studentsEmpty">
            <div class="students-empty__title">No student found</div>
            <p>No Student ID matches your search. Check the format (e.g. 23-LN-0417) and try again.</p>
        </div>

    </div>
</section>


<div class="student-modal-overlay" id="studentModalOverlay" onclick="if(event.target===this)closeStudentModal()">
    <div class="student-modal" role="dialog" aria-modal="true" aria-label="Student profile">
        <button type="button" class="student-modal__close" onclick="closeStudentModal()" aria-label="Close">&times;</button>

        <div class="student-modal__header">
            <div id="modalAvatar" class="student-modal__avatar"></div>
            <div>
                <div class="student-modal__name" id="modalName"></div>
                <span class="student-card__id" id="modalStudentId"></span>
            </div>
        </div>

        <div class="student-modal__body">
            <div class="student-modal__section-title">Taken Courses</div>
            <div id="modalCourses"></div>
        </div>
    </div>
</div>

<script>
    window.STUDENTS_DATA = <?php echo json_encode($students->values(), 15, 512) ?>;
</script>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    const searchInput   = document.getElementById('studentSearchInput');
    const studentCards  = document.querySelectorAll('#studentsGrid .student-card');
    const emptyState    = document.getElementById('studentsEmpty');
    const countEl       = document.getElementById('studentsCount');
    const totalStudents = studentCards.length;

    function filterStudents() {
        const q = searchInput.value.trim().toLowerCase();
        let visible = 0;

        studentCards.forEach(function (card) {
            const matches = q === ''
                || card.dataset.studentId.includes(q)
                || card.dataset.studentName.includes(q);
            card.style.display = matches ? 'flex' : 'none';
            if (matches) visible++;
        });

        emptyState.style.display = visible === 0 ? 'block' : 'none';
        countEl.textContent = q === ''
            ? totalStudents + ' students'
            : visible + ' of ' + totalStudents + ' students';
    }

    // Filter live as the user types, and on Enter
    searchInput.addEventListener('input', filterStudents);
    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') filterStudents();
    });

    /* ── Student profile modal ─────────────────────── */
    const modalOverlay = document.getElementById('studentModalOverlay');

    function initialsOf(name) {
        const parts = name.trim().split(/\s+/);
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function openStudentModal(index) {
        const s = window.STUDENTS_DATA[index];
        if (!s) return;

        // Header: photo (or initials), name, Student ID
        const avatarEl = document.getElementById('modalAvatar');
        avatarEl.innerHTML = s.avatar_url
            ? '<img src="' + escapeHtml(s.avatar_url) + '" alt="' + escapeHtml(s.name) + '">'
            : initialsOf(s.name);
        document.getElementById('modalName').textContent      = s.name;
        document.getElementById('modalStudentId').textContent = s.student_id;

        // Taken courses list
        const coursesEl = document.getElementById('modalCourses');
        const courses   = s.courses || [];
        coursesEl.innerHTML = '';

        if (courses.length === 0) {
            coursesEl.innerHTML = '<div class="course-row--none">No courses taken yet.</div>';
        } else {
            courses.forEach(function (c) {
                const row = document.createElement('div');
                if (c.completed) {
                    // Completed → green glow + Completed badge + QR code beside it.
                    // The QR encodes a verification string for the certificate.
                    // Encode the certificate's verification URL, so a scan
                    // opens UPSKILL with the certificate floating over the
                    // homepage. Falls back to the site root for a completed
                    // course that has no certificate row yet.
                    const qrData = encodeURIComponent(c.verify_url || <?php echo json_encode(url('/'), 15, 512) ?>);
                    row.className = 'course-row completed';
                    row.innerHTML =
                        '<div>' +
                            '<div class="course-row__title">' + escapeHtml(c.title) + '</div>' +
                            '<span class="course-row__badge">&#10003; Completed</span>' +
                        '</div>' +
                        '<img class="course-row__qr" alt="Completion QR code" ' +
                             'src="https://api.qrserver.com/v1/create-qr-code/?size=116x116&data=' + qrData + '">';
                } else {
                    // Not completed → plain row
                    row.className = 'course-row';
                    row.innerHTML = '<div class="course-row__title">' + escapeHtml(c.title) + '</div>';
                }
                coursesEl.appendChild(row);
            });
        }

        modalOverlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeStudentModal() {
        modalOverlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modalOverlay.classList.contains('open')) closeStudentModal();
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/public/students.blade.php ENDPATH**/ ?>