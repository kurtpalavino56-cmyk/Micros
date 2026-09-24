
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Certificates | Upskill</title>
<link rel="icon" type="image/png" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
<link rel="apple-touch-icon" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
<style>
    :root {
        --navy: #13176b;
        --navy-deep: #0c0f4d;
        --gold: #dba617;
        --cyan: #7fe9e3;
        --ink: #17205f;
        --muted: #69728a;
        --line: #e2e6ef;
        --surface: #ffffff;
        --page: #f5f7fb;
        --shadow: 0 8px 24px rgba(19, 23, 107, 0.07);
    }

    * { box-sizing: border-box; }
    body {
        margin: 0;
        font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        color: var(--ink);
        background: var(--page);
    }
    a { text-decoration: none; color: inherit; }
    button { font-family: inherit; }

    /* Student shell: keep the sidebar and page content in two columns.
       This page previously omitted the display:grid declaration and therefore
       the sidebar expanded across the full viewport. */
    .layout.student-sidebar-layout {
        display: grid;
        grid-template-columns: 244px minmax(0, 1fr);
        min-height: calc(100vh - 70px);
        align-items: stretch;
    }

    .layout.student-sidebar-layout.sidebar-collapsed {
        grid-template-columns: 78px minmax(0, 1fr);
    }

    /* Main content */
    .main {
        padding: 34px 38px 64px;
        max-width: 1180px;
    }

    .page-head {
        margin-bottom: 22px;
    }
    .page-head h2 {
        margin: 0;
        color: var(--navy);
        font-size: 30px;
        line-height: 1.2;
        font-weight: 800;
        letter-spacing: -.3px;
    }
    .page-head p {
        margin: 7px 0 0;
        color: var(--muted);
        font-size: 14px;
    }

    /* Certificate list */
    .certificate-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .certificate-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        min-height: 88px;
        padding: 18px 20px;
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: 8px;
        box-shadow: var(--shadow);
    }

    .certificate-info {
        min-width: 0;
        flex: 1;
    }
    .certificate-course {
        margin: 0;
        color: var(--navy);
        font-size: 17px;
        line-height: 1.35;
        font-weight: 750;
        overflow-wrap: anywhere;
    }
    .certificate-date {
        margin: 5px 0 0;
        color: var(--muted);
        font-size: 13px;
    }
    .certificate-date strong {
        color: #4d566d;
        font-weight: 700;
    }

    .certificate-actions {
        display: flex;
        align-items: center;
        gap: 9px;
        flex-shrink: 0;
    }
    .certificate-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 92px;
        height: 38px;
        padding: 0 17px;
        border: 1px solid var(--navy);
        border-radius: 6px;
        font-size: 13px;
        font-weight: 750;
        line-height: 1;
        transition: background-color .15s ease, color .15s ease, border-color .15s ease, transform .15s ease;
    }
    .certificate-btn:hover {
        transform: translateY(-1px);
    }
    .certificate-btn.view {
        color: #fff;
        background: var(--navy);
    }
    .certificate-btn.view:hover {
        background: var(--navy-deep);
        border-color: var(--navy-deep);
    }
    .certificate-btn.download {
        color: var(--navy);
        background: #fff;
    }
    .certificate-btn.download:hover {
        background: #f3f5fa;
    }

    .empty-state {
        background: var(--surface);
        border: 1px dashed #cfd5e2;
        border-radius: 8px;
        padding: 46px 28px;
        text-align: center;
        color: var(--muted);
    }
    .empty-state strong {
        display: block;
        color: var(--navy);
        font-size: 16px;
        margin-bottom: 5px;
    }
    .empty-state p {
        margin: 0;
        font-size: 14px;
    }

    @media (max-width: 760px) {
        .main { padding: 26px 20px 50px; }
        .page-head h2 { font-size: 26px; }
        .certificate-row {
            align-items: flex-start;
            flex-direction: column;
            gap: 16px;
        }
        .certificate-actions {
            width: 100%;
        }
        .certificate-btn {
            flex: 1;
        }
    }
</style>
</head>
<body>

<?php echo $__env->make('components.student-navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="layout student-sidebar-layout">
    <?php echo $__env->make('components.student-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="main">
        <header class="page-head">
            <h2>My Certificates</h2>
            <p>Your earned microcredential certificates.</p>
        </header>

        <?php if($certificates->count()): ?>
            <section class="certificate-list" aria-label="Earned certificates">
                <?php $__currentLoopData = $certificates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $certificate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="certificate-row">
                        <div class="certificate-info">
                            <h3 class="certificate-course"><?php echo e($certificate->course_name); ?></h3>
                            <p class="certificate-date">
                                <strong>Date received:</strong> <?php echo e($certificate->issued_date); ?>

                            </p>
                        </div>

                        <div class="certificate-actions">
                            <a href="<?php echo e($certificate->view_url); ?>" class="certificate-btn view">View</a>
                            <a href="<?php echo e($certificate->download_url); ?>" class="certificate-btn download">Download</a>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </section>
        <?php else: ?>
            <div class="empty-state">
                <strong>No certificates yet</strong>
                <p>Complete a microcredential course to receive your first certificate.</p>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php echo $__env->make('components.responsive', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/student/certificates.blade.php ENDPATH**/ ?>