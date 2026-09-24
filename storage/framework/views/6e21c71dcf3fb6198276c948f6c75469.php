

<?php
    $title       = $course['title']       ?? 'Course Title';
    $description = $course['description'] ?? '';
    $professor   = $course['professor']   ?? '';
    $hours       = $course['hours']       ?? 0;
    $rating      = $course['rating']      ?? 0;
    $category    = $course['category']    ?? 'Web Development';
    $level       = $course['level']       ?? 'Intermediate';
    $image       = $course['image']       ?? null;
    $slug        = $course['slug']        ?? '#';
?>

<article class="course-card">
    <a href="<?php echo e($slug); ?>" class="course-card__thumb">
        <?php if($image): ?>
            <img src="<?php echo e($image); ?>" alt="<?php echo e($title); ?>" loading="lazy">
        <?php else: ?>
            <div class="course-card__thumb-placeholder"></div>
        <?php endif; ?>
    </a>

    <div class="course-card__body">
        <div class="course-card__tags">
            <span class="tag tag-teal"><?php echo e($category); ?></span>
            <span class="tag tag-gold"><?php echo e($level); ?></span>
        </div>

        <h3 class="course-card__title">
            <a href="<?php echo e($slug); ?>"><?php echo e($title); ?></a>
        </h3>

        <p class="course-card__desc"><?php echo e($description); ?></p>

        <div class="course-card__meta">
            <span class="course-card__prof"><?php echo e($professor); ?></span>
            <span class="course-card__hours"><?php echo e($hours); ?>h</span>
            <span class="course-card__rating">
                <?php for($i = 1; $i <= 5; $i++): ?>
                    <?php echo e($i <= $rating ? '★' : '☆'); ?>

                <?php endfor; ?>
            </span>
        </div>
    </div>
</article><?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/components/course-card.blade.php ENDPATH**/ ?>