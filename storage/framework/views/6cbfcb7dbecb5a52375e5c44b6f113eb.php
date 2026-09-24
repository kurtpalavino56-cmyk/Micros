
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 0; }
    body {
        margin: 0;
        font-family: 'Helvetica', 'DejaVu Sans', Arial, sans-serif;
        color: #12225c;
    }
    .sheet {
        width: 100%;
        height: 100%;
        box-sizing: border-box;
        border: 10px solid #16357a;
        padding: 26px 40px;
    }
    .inner-rule {
        border: 2px solid #d4a017;
        padding: 24px 36px;
    }
    .institution {
        text-align: center;
        font-size: 16px;
        letter-spacing: 1px;
        color: #0f1f4d;
        font-weight: bold;
    }
    .word {
        text-align: center;
        font-size: 34px;
        font-weight: bold;
        color: #0f2461;
        letter-spacing: 2px;
        margin-top: 4px;
    }
    .of {
        text-align: center;
        font-size: 18px;
        color: #d4a017;
        font-weight: bold;
        margin-bottom: 14px;
    }
    .awarded { text-align: center; font-size: 13px; color: #22304f; }
    .name {
        text-align: center;
        font-size: 30px;
        font-weight: bold;
        color: #12225c;
        margin: 4px 0;
    }
    .for { text-align: center; font-size: 13px; font-style: italic; color: #22304f; }
    .course {
        text-align: center;
        font-size: 18px;
        font-weight: bold;
        color: #1a4fd6;
        margin: 6px 0 14px;
    }
    table.meta { width: 100%; margin-top: 10px; font-size: 11px; }
    table.meta td { padding: 2px 6px; vertical-align: top; }
    table.meta td.k { font-weight: bold; width: 160px; color: #12225c; }
    .section-title { font-size: 12px; font-weight: bold; color: #12225c; margin-top: 12px; }
    ul.plain { margin: 4px 0 0 16px; padding: 0; font-size: 11px; }
    ul.plain li { margin-bottom: 2px; }
    table.footer { width: 100%; margin-top: 22px; }
    table.footer td { vertical-align: bottom; }
    .qr-cell { width: 110px; text-align: center; font-size: 10px; }
    .qr-cell img { width: 90px; height: 90px; }
    .sign-cell { text-align: center; font-size: 11px; }
    .sign-rule { border-top: 1px solid #12225c; width: 220px; margin: 30px auto 4px; }
    .sign-name { font-weight: bold; }
    .status-revoked {
        text-align: center;
        color: #a11d1d;
        font-weight: bold;
        font-size: 12px;
        margin-top: 8px;
    }
</style>
</head>
<body>
<div class="sheet">
    <div class="inner-rule">
        <div class="institution"><?php echo e($cert['institution']); ?></div>
        <div class="word">CERTIFICATE</div>
        <div class="of">of Completion</div>

        <div class="awarded">This certificate is awarded to</div>
        <div class="name"><?php echo e($cert['student_name']); ?></div>
        <div class="for">for successfully completing the Microcredential</div>
        <div class="course"><?php echo e($cert['course_title']); ?></div>

        <table class="meta">
            <tr>
                <td class="k">Date Completed</td>
                <td><?php echo e($cert['date_completed'] ?? '—'); ?></td>
                <td class="k">Credential ID</td>
                <td><?php echo e($cert['serial']); ?></td>
            </tr>
            <tr>
                <td class="k">Learning Hours</td>
                <td><?php echo e($cert['learning_hours'] ?? '—'); ?></td>
                <td class="k">PQF Level</td>
                <td><?php echo e($cert['pqf_level'] ?? '—'); ?></td>
            </tr>
            <?php if(!empty($cert['credit_equivalency'])): ?>
            <tr>
                <td class="k">Credit Equivalency</td>
                <td colspan="3"><?php echo e($cert['credit_equivalency']); ?></td>
            </tr>
            <?php endif; ?>
        </table>

        <?php if(!empty($cert['learning_outcomes'])): ?>
        <div class="section-title">Learning Outcomes Achieved</div>
        <ul class="plain">
            <?php $__currentLoopData = $cert['learning_outcomes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php if(!empty($lo['code'])): ?><?php echo e($lo['code']); ?>: <?php endif; ?><?php echo e($lo['description'] ?? ''); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
        <?php endif; ?>

        <?php if(!empty($cert['competencies'])): ?>
        <div class="section-title">Competencies Achieved</div>
        <ul class="plain">
            <?php $__currentLoopData = $cert['competencies']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($unit['title'] ?? ''); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
        <?php endif; ?>

        <table class="footer">
            <tr>
                <td class="qr-cell">
                    <?php if(!empty($cert['qr_url'])): ?>
                        <img src="<?php echo e($cert['qr_url']); ?>" alt="Verify">
                    <?php endif; ?>
                    <div>Scan to verify</div>
                </td>
                <td></td>
                <td class="sign-cell">
                    <div class="sign-rule"></div>
                    <div class="sign-name"><?php echo e($cert['issuer_name']); ?></div>
                    <div><?php echo e($cert['issuer_role']); ?></div>
                </td>
            </tr>
        </table>

        <?php if(($cert['status'] ?? 'active') === 'revoked'): ?>
            <div class="status-revoked">THIS CERTIFICATE HAS BEEN REVOKED</div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
<?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/certificates/pdf.blade.php ENDPATH**/ ?>