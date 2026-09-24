
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($course->title); ?> – Courses &amp; Badges</title>
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --navy: #071550;
            --blue: #0b4ea2;
            --soft: #eaf2ff;
            --yellow: #f4c430;
            --yellow-dark: #e8b91f;
            --yellow-soft: #fff8dc;
            --yellow-text: #7a5b00;
            --surface: #f5f7fa;
            --white: #ffffff;
            --line: #e2e7ef;
            --text: #17212b;
            --muted: #687385;
            --danger: #b3352d;
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background: var(--surface);
            color: var(--text);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }
        a { text-decoration: none; color: inherit; }

        /* Layout (topbar + shared sidebar offset) */
        .layout { display: block; min-height: calc(100vh - 66px); }
        .main {
            margin-left: 238px !important;
            width: calc(100% - 238px) !important;
            min-width: 0;
            padding: 28px 34px 56px;
        }
        .page-wrap { max-width: 1080px; margin: 0 auto; }

        .back-link { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 14px; font-size: .83rem; font-weight: 600; color: var(--muted); }
        .back-link:hover { color: var(--navy); }

        /* Cards */
        .card { background: var(--white); border: 1px solid var(--line); border-radius: 12px; padding: 20px 22px; }
        .card h3 { font-size: .92rem; font-weight: 700; color: var(--blue); margin-bottom: 12px; }
        .card h3 small { display: block; margin-top: 2px; font-size: .74rem; font-weight: 400; color: var(--muted); }
        .card p { font-size: .88rem; line-height: 1.65; color: #374151; }
        .stack { display: flex; flex-direction: column; gap: 16px; min-width: 0; }

        /* Banners */
        .banner { border-radius: 10px; padding: 13px 16px; margin-bottom: 16px; font-size: .86rem; line-height: 1.55; white-space: pre-wrap; }
        .banner strong { display: block; margin-bottom: 3px; font-size: .78rem; }
        .banner-note   { background: var(--yellow-soft); border: 1px solid var(--yellow); color: var(--yellow-text); }
        .banner-denied { background: #fff1f0; border: 1px solid #f3c8c4; color: var(--danger); }
        .alert-success { background: #eaf8ef; border: 1px solid #ccebd6; color: #166534; border-radius: 10px; padding: 11px 16px; font-size: .86rem; font-weight: 600; margin-bottom: 16px; }

        /* Header */
        .head-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap; }
        .detail-title { font-size: 1.4rem; font-weight: 700; color: var(--blue); line-height: 1.25; }
        .detail-sub { margin-top: 4px; font-size: .84rem; color: var(--muted); }

        .status-pill { display: inline-flex; align-items: center; gap: 7px; padding: 4px 12px; border-radius: 999px; font-size: .76rem; font-weight: 600; }
        .status-pill .dot { width: 8px; height: 8px; border-radius: 50%; }
        .status-pending  { background: var(--yellow-soft); color: var(--yellow-text); }
        .status-pending .dot { background: var(--yellow); }
        .status-approved { background: var(--soft); color: var(--blue); }
        .status-approved .dot { background: var(--blue); }
        .status-denied   { background: #fff1f0; color: var(--danger); }
        .status-denied .dot { background: #d64545; }
        .status-draft    { background: #f1f3f7; color: #5b6678; }
        .status-draft .dot { background: #a3adbd; }

        .detail-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-top: 18px; }
        .detail-actions form { display: inline-flex; }
        .btn {
            padding: 9px 18px; border-radius: 8px; border: 1px solid var(--line);
            background: var(--white); color: var(--text); font: inherit; font-size: .84rem; font-weight: 600; cursor: pointer;
            transition: background .15s ease;
        }
        .btn:hover { background: #f5f7fa; }
        .btn-approve { background: var(--yellow); border-color: var(--yellow); color: var(--navy); }
        .btn-approve:hover { background: var(--yellow-dark); }
        .btn-ghost { background: transparent; }
        .btn-delete { margin-left: auto; color: var(--danger); border-color: transparent; background: transparent; }
        .btn-delete:hover { background: #fff1f0; }

        #deny-panel { display: none; width: 100%; margin-top: 4px; }
        #deny-panel.open { display: block; }
        #deny-panel label { display: block; margin-bottom: 6px; font-size: .78rem; font-weight: 600; color: var(--muted); }
        #deny-panel textarea {
            width: 100%; min-height: 110px; padding: 12px 14px; resize: vertical;
            border: 1px solid var(--line); border-radius: 10px; font: inherit; font-size: .88rem; color: var(--text); outline: none;
        }
        #deny-panel textarea:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(11,78,162,.1); }
        .field-error { margin-top: 6px; font-size: .8rem; color: var(--danger); }
        .deny-actions { display: flex; gap: 8px; margin-top: 10px; }

        /* Quick facts strip */
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); padding: 0; margin: 16px 0; }
        .stats > div { padding: 14px 18px; border-right: 1px solid var(--line); }
        .stats > div:last-child { border-right: 0; }
        .stats small { display: block; font-size: .74rem; color: var(--muted); }
        .stats b { font-size: .95rem; font-weight: 600; color: var(--blue); }

        /* Two-column body */
        .cols { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 16px; align-items: start; }

        .chip-row { display: flex; flex-wrap: wrap; gap: 6px; }
        .chip { padding: 4px 11px; border-radius: 999px; background: var(--soft); color: var(--blue); font-size: .77rem; font-weight: 500; }
        .subhead { margin-top: 18px; }

        .obj-list { list-style: none; }
        .obj-list li { position: relative; padding: 8px 0 8px 20px; border-bottom: 1px solid var(--line); font-size: .88rem; color: #374151; }
        .obj-list li:last-child { border-bottom: 0; }
        .obj-list li::before { content: ''; position: absolute; left: 3px; top: 15px; width: 7px; height: 7px; border-radius: 50%; background: var(--yellow); }

        /* Modules */
        .module-block { border: 1px solid var(--line); border-radius: 10px; margin-bottom: 10px; overflow: hidden; }
        .module-block:last-child { margin-bottom: 0; }
        .module-title { padding: 12px 14px 4px; font-size: .92rem; font-weight: 600; }
        .module-sub { padding: 0 14px 10px; font-size: .8rem; color: var(--muted); }
        .lesson-row, .quiz-row {
            display: flex; justify-content: space-between; align-items: center; gap: 10px;
            padding: 9px 14px; border-top: 1px solid var(--line); font-size: .84rem; cursor: pointer;
        }
        .lesson-row:hover { background: #f8fafd; }
        .lesson-row::before { content: '▸'; margin-right: 8px; font-size: .75rem; color: var(--muted); }
        .lesson-row span:first-of-type { flex: 1; }
        .lesson-meta { font-size: .76rem; color: var(--muted); }
        .quiz-row { background: var(--yellow-soft); color: var(--yellow-text); font-weight: 600; }
        .quiz-row span:last-child { font-size: .76rem; }

        .lesson-detail, .quiz-detail { display: none; padding: 10px 16px 12px 30px; border-top: 1px dashed var(--line); font-size: .82rem; color: var(--muted); background: #fbfcfe; }
        .lesson-detail.open, .quiz-detail.open { display: block; }
        .lesson-desc { margin: 0 0 6px; line-height: 1.5; }
        .lesson-empty { font-style: italic; }
        .lesson-file { color: var(--blue); font-weight: 600; text-decoration: underline; }
        .quiz-instructions { margin-bottom: 10px; font-style: italic; }
        .quiz-question { padding: 8px 0; border-top: 1px solid var(--line); }
        .quiz-question:first-of-type { border-top: 0; }
        .quiz-question-head { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 4px; }
        .quiz-question-text { font-weight: 600; color: var(--text); }
        .quiz-question-meta { white-space: nowrap; font-size: .74rem; }
        .quiz-options { margin: 4px 0 0; padding-left: 4px; list-style: none; }
        .quiz-options li { padding: 2px 0; }
        .quiz-options li.is-correct { color: #166534; font-weight: 600; }
        .quiz-answer { margin-top: 4px; color: #166534; }
        .modules-empty { font-size: .86rem; color: var(--muted); }

        /* Award */
        .award-badge { display: block; width: 110px; max-width: 100%; height: auto; margin: 2px auto 8px; }
        .award-name { text-align: center; font-size: .9rem; font-weight: 600; color: var(--text); word-break: break-word; }
        .award-sub { text-align: center; margin-top: 2px; font-size: .76rem; color: var(--muted); }
        .award-label { display: flex; align-items: center; gap: 8px; margin: 16px 0 8px; font-size: .78rem; font-weight: 600; color: var(--muted); }
        .award-tag { padding: 2px 9px; border-radius: 999px; background: var(--soft); color: var(--blue); font-size: .7rem; font-weight: 600; }
        .award-none { padding: 18px 12px; border: 1px dashed #d3dae6; border-radius: 10px; text-align: center; font-size: .8rem; color: #98a2b3; }
        .award-upload { border: 1px solid var(--line); border-radius: 10px; overflow: hidden; background: var(--white); }
        .award-upload img { display: block; width: 100%; height: auto; }
        .award-upload embed { display: block; width: 100%; height: 260px; }
        .award-link { display: inline-block; margin-top: 8px; font-size: .8rem; font-weight: 600; color: var(--blue); text-decoration: underline; }
        .cert-wrap { overflow-x: auto; }

        /* Progress */
        .progress-track { height: 5px; border-radius: 3px; background: #eef1f6; overflow: hidden; margin-bottom: 8px; }
        .progress-fill { height: 100%; background: var(--blue); border-radius: 3px; }
        .pct { font-size: .82rem; color: var(--muted); }

        /* Enrollment review */
        .enroll-row { display: grid; grid-template-columns: minmax(150px, 1.2fr) repeat(3, minmax(110px, 1fr)) auto; gap: 10px 16px; align-items: center; padding: 12px 0; border-top: 1px solid var(--line); font-size: .84rem; }
        .enroll-row:first-of-type { border-top: 0; }
        .enroll-name { font-weight: 600; }
        .enroll-name small, .enroll-cell small { display: block; font-size: .72rem; font-weight: 400; color: var(--muted); }
        .enroll-actions { display: flex; gap: 6px; }
        .enroll-actions .btn { padding: 6px 12px; font-size: .78rem; }

        @media (max-width: 1024px) {
            .main { margin-left: 0 !important; width: 100% !important; padding: 24px 20px 48px; }
            .cols { grid-template-columns: 1fr; }
        }
        @media (max-width: 760px) {
            .enroll-row { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 560px) {
            .main { padding: 18px 12px 40px; }
            .card { padding: 18px 16px; }
            .btn-delete { margin-left: 0; }
        }
    </style>
</head>
<body>
<?php echo $__env->make('components.authenticated-topbar', ['user' => auth()->user()], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="layout admin-layout">

    
    <?php echo $__env->make('components.admin-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <main class="main">
    <div class="page-wrap">

        <a href="<?php echo e(route('admin.courses')); ?>" class="back-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
            Back to courses and badges
        </a>

        <?php if(session('success')): ?>
            <div class="alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <?php if(! empty($course->change_note)): ?>
            <div class="banner banner-note"><strong>What the author changed</strong><?php echo e($course->change_note); ?></div>
        <?php endif; ?>

        <?php if(! empty($course->denial_feedback)): ?>
            <div class="banner banner-denied"><strong>Previous denial feedback</strong><?php echo e($course->denial_feedback); ?></div>
        <?php endif; ?>

        
        <div class="card">
            <div class="head-top">
                <div>
                    <div class="detail-title"><?php echo e($course->title); ?></div>
                    <div class="detail-sub">
                        By <?php echo e($course->instructor ?? 'Faculty'); ?>

                        <?php if($course->category): ?> · <?php echo e($course->category); ?> <?php endif; ?>
                        <?php if($course->created_at): ?> · Created <?php echo e($course->created_at->format('M j, Y')); ?> <?php endif; ?>
                    </div>
                </div>
                <span class="status-pill status-<?php echo e($course->status_key); ?>">
                    <span class="dot"></span><?php echo e($course->status); ?>

                </span>
            </div>

            <div class="detail-actions">
                <?php if($course->status_key === 'pending'): ?>
                    <form method="POST" action="<?php echo e(route('admin.courses.approve', $course->id)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-approve">Approve</button>
                    </form>
                    <button type="button" class="btn" id="deny-open">Deny with feedback</button>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('admin.courses.destroy', $course->id)); ?>" style="margin-left:auto;">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-delete"
                            onclick="return confirm('Permanently delete \'<?php echo e(addslashes($course->title)); ?>\'? Its modules, lessons, quizzes and enrollments will also be removed. This cannot be undone.');">
                        Delete course
                    </button>
                </form>
            </div>

            <?php if($course->status_key === 'pending'): ?>
                <div id="deny-panel" class="<?php echo e($errors->has('denial_feedback') ? 'open' : ''); ?>" style="margin-top:14px;">
                    <form method="POST" action="<?php echo e(route('admin.courses.deny', $course->id)); ?>">
                        <?php echo csrf_field(); ?>
                        <label for="denial_feedback">Feedback for the author (required)</label>
                        <textarea id="denial_feedback" name="denial_feedback" required minlength="5"
                                  placeholder="Explain what needs to change before this course can be approved."><?php echo e(old('denial_feedback')); ?></textarea>
                        <?php $__errorArgs = ['denial_feedback'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="field-error"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <div class="deny-actions">
                            <button type="submit" class="btn btn-approve">Send denial and feedback</button>
                            <button type="button" class="btn btn-ghost" id="deny-cancel">Cancel</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        
        <div class="card stats">
            <div><small>Level</small><b><?php echo e($course->level ?? '—'); ?></b></div>
            <div><small>Duration</small><b><?php echo e($course->duration ?? '—'); ?></b></div>
            <div><small>Program</small><b><?php echo e($course->program ?? '—'); ?></b></div>
            <div><small>Term</small><b><?php echo e($course->term ?? '—'); ?></b></div>
            <div><small>Students</small><b><?php echo e($course->students); ?></b></div>
            <div><small>Modules</small><b><?php echo e($course->modules_count); ?></b></div>
            <div><small>Lessons</small><b><?php echo e($course->lessons_count); ?></b></div>
        </div>

        <div class="cols">

            
            <div class="stack">

                <div class="card">
                    <h3>Description</h3>
                    <p><?php echo $course->description ?: 'No description provided.'; ?></p>
                </div>

                <?php if(! empty($course->skills) || ! empty($course->related_skills)): ?>
                <div class="card">
                    <?php if(! empty($course->skills)): ?>
                        <h3>Skills covered</h3>
                        <div class="chip-row">
                            <?php $__currentLoopData = $course->skills; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $skill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="chip"><?php echo e($skill); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>

                    
                    <?php if(! empty($course->related_skills)): ?>
                        <h3 class="<?php echo e(! empty($course->skills) ? 'subhead' : ''); ?>">
                            Related on
                            <small>Set by faculty and used to recommend this course. Not shown on any student page.</small>
                        </h3>
                        <div class="chip-row">
                            <?php $__currentLoopData = $course->related_skills; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $skill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="chip"><?php echo e($skill); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if(! empty($course->objectives)): ?>
                <div class="card">
                    <h3>Learning objectives</h3>
                    <ul class="obj-list">
                        <?php $__currentLoopData = $course->objectives; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $objective): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($objective); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="card">
                    <h3>Course content</h3>
                    <?php $__empty_1 = true; $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="module-block">
                            <div class="module-title"><?php echo e($module->title); ?></div>
                            <?php if($module->description): ?>
                                <div class="module-sub"><?php echo $module->description; ?></div>
                            <?php endif; ?>

                            <?php $__currentLoopData = $module->lessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="lesson-row" onclick="this.nextElementSibling.classList.toggle('open')">
                                    <span><?php echo e($lesson->title); ?></span>
                                    <span class="lesson-meta"><?php echo e($lesson->type); ?><?php echo e($lesson->duration ? ' · ' . $lesson->duration : ''); ?></span>
                                </div>
                                <div class="lesson-detail">
                                    <?php if($lesson->description): ?>
                                        <p class="lesson-desc"><?php echo e($lesson->description); ?></p>
                                    <?php endif; ?>
                                    <?php if($lesson->file_url): ?>
                                        <a class="lesson-file" href="<?php echo e($lesson->file_url); ?>" target="_blank" rel="noopener">Open attached file (<?php echo e($lesson->file_name); ?>)</a>
                                    <?php elseif(! $lesson->description): ?>
                                        <p class="lesson-desc lesson-empty">No description or file attached.</p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            <?php if($module->quiz): ?>
                                <div class="quiz-row" onclick="this.nextElementSibling.classList.toggle('open')">
                                    <span>Quiz: <?php echo e($module->quiz->title); ?></span>
                                    <span><?php echo e($module->quiz->questions_count); ?> questions · Pass <?php echo e($module->quiz->passing_score); ?>%<?php echo e($module->quiz->time_limit ? ' · ' . $module->quiz->time_limit . ' min' : ''); ?></span>
                                </div>
                                <div class="quiz-detail">
                                    <?php if($module->quiz->instructions): ?>
                                        <p class="quiz-instructions"><?php echo e($module->quiz->instructions); ?></p>
                                    <?php endif; ?>
                                    <?php $__currentLoopData = $module->quiz->questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qi => $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="quiz-question">
                                            <div class="quiz-question-head">
                                                <span class="quiz-question-text">Q<?php echo e($qi + 1); ?>. <?php echo e($q->question); ?></span>
                                                <span class="quiz-question-meta"><?php echo e($q->type); ?><?php echo e($q->points ? ' · ' . $q->points . ' pts' : ''); ?></span>
                                            </div>
                                            <?php if(! empty($q->options)): ?>
                                                <ul class="quiz-options">
                                                    <?php $__currentLoopData = $q->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $oi => $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <li class="<?php echo e($opt === $q->correct_answer ? 'is-correct' : ''); ?>">
                                                            <?php echo e(chr(65 + $oi)); ?>. <?php echo e($opt); ?><?php if($opt === $q->correct_answer): ?> (correct) <?php endif; ?>
                                                        </li>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </ul>
                                            <?php elseif($q->correct_answer): ?>
                                                <div class="quiz-answer">Answer: <strong><?php echo e($q->correct_answer); ?></strong></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="modules-empty">No modules have been added to this course yet.</div>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="stack">

                <div class="card">
                    <h3>Completion award</h3>

                    <?php if($course->badge_icon): ?>
                        <img class="award-badge" src="<?php echo e($course->badge_icon); ?>" alt="<?php echo e($course->badge); ?>">
                    <?php else: ?>
                        <div class="award-none">No badge designed yet</div>
                    <?php endif; ?>
                    <div class="award-name"><?php echo e($course->badge); ?></div>
                    <?php if($course->badge_level): ?>
                        <div class="award-sub"><?php echo e($course->badge_level); ?></div>
                    <?php endif; ?>

                    <div class="award-label">
                        Certificate
                        <?php if($course->certificate_enabled): ?>
                            <span class="award-tag"><?php echo e($course->certificate_mode === 'upload' ? 'Uploaded' : 'Auto-generated'); ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if(! $course->certificate_enabled): ?>
                        <div class="award-none">No certificate configured yet</div>
                    <?php elseif($course->certificate_mode === 'upload' && $course->certificate_file): ?>
                        <?php $ext = strtolower(pathinfo($course->certificate_file, PATHINFO_EXTENSION)); ?>
                        <div class="award-upload">
                            <?php if($ext === 'pdf'): ?>
                                <embed src="<?php echo e(asset($course->certificate_file)); ?>" type="application/pdf">
                            <?php else: ?>
                                <img src="<?php echo e(asset($course->certificate_file)); ?>" alt="Uploaded certificate">
                            <?php endif; ?>
                        </div>
                        <a class="award-link" href="<?php echo e(asset($course->certificate_file)); ?>" target="_blank">Open the uploaded file</a>
                    <?php else: ?>
                        <div class="cert-wrap">
                            <?php echo $__env->make('components.certificate', ['cert' => $course->certificate], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h3>Completion</h3>
                    <div class="progress-track"><div class="progress-fill" style="width: <?php echo e($course->percent); ?>%"></div></div>
                    <div class="pct"><?php echo e($course->percent); ?>% average completion · <?php echo e($course->students); ?> enrolled</div>
                </div>
            </div>
        </div>

        
        <div class="card" style="margin-top:16px;">
            <h3>Enrollment review</h3>
            <?php $__empty_1 = true; $__currentLoopData = $enrollments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enrollment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="enroll-row">
                    <div class="enroll-name">
                        <?php echo e($enrollment->student_name); ?>

                        <small><?php echo e($enrollment->institutional_label); ?></small>
                    </div>
                    <div class="enroll-cell"><small>Completion</small><?php echo e(ucfirst(str_replace('_', ' ', $enrollment->completion_status))); ?></div>
                    <div class="enroll-cell"><small>Faculty verification</small><?php echo e(ucfirst(str_replace('_', ' ', $enrollment->faculty_verification_status))); ?></div>
                    <div class="enroll-cell"><small>Academic unit</small><?php echo e(ucfirst(str_replace('_', ' ', $enrollment->academic_unit_confirmation_status))); ?></div>
                    <div class="enroll-cell"><small>Completed</small><?php echo e($enrollment->completed_at?->format('M j, Y') ?? '—'); ?></div>
                    <?php if($enrollment->academic_confirmation_actionable): ?>
                        <form method="POST" action="<?php echo e(route('admin.enrollments.academic-confirm', ['id' => $course->id, 'enrollment' => $enrollment->id])); ?>" class="enroll-actions" style="grid-column:1/-1;">
                            <?php echo csrf_field(); ?>
                            <button type="submit" name="decision" value="confirmed" class="btn btn-approve">Confirm</button>
                            <button type="submit" name="decision" value="rejected" class="btn">Reject</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="modules-empty">No enrollments for this course yet.</div>
            <?php endif; ?>
        </div>

    </div>
    </main>
</div>

<script>
    (function () {
        var open = document.getElementById('deny-open');
        var panel = document.getElementById('deny-panel');
        var cancel = document.getElementById('deny-cancel');
        if (!panel) return;

        function show() { panel.classList.add('open'); if (open) open.style.display = 'none'; }
        function hide() { panel.classList.remove('open'); if (open) open.style.display = ''; }

        if (open) open.addEventListener('click', show);
        if (cancel) cancel.addEventListener('click', hide);
        if (location.hash === '#deny-panel' || panel.classList.contains('open')) show();
    })();
</script>


<button id="back-to-top-btn" type="button" title="Back to top" aria-label="Back to top"
        onclick="window.scrollTo({top:0,behavior:'smooth'});">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>
</button>
<style>
    #back-to-top-btn{position:fixed;right:26px;bottom:26px;z-index:2000;width:46px;height:46px;border-radius:50%;border:none;background:#071550;color:#fff;display:none;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 8px 20px rgba(7,21,80,.28);transition:transform .15s ease,background .2s ease,color .2s ease;}
    #back-to-top-btn:hover{background:#f4c430;color:#071550;transform:translateY(-2px);}
    #back-to-top-btn svg{width:21px;height:21px;}
</style>
<script>
    (function () {
        var btn = document.getElementById('back-to-top-btn');
        if (!btn) return;
        function t() { btn.style.display = (window.scrollY || document.documentElement.scrollTop) > 400 ? 'flex' : 'none'; }
        window.addEventListener('scroll', t, { passive: true });
        t();
    })();
</script>

<?php echo $__env->make('components.responsive', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html><?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/admin/courses/show.blade.php ENDPATH**/ ?>