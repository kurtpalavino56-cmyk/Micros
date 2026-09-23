<?php
    $completionMode = old('completion_mode', $framework?->completion_mode ?? 'all_required');
    $outcomes = old(
        'cumulative_outcomes',
        is_array($framework?->cumulative_outcomes ?? null)
            ? implode("\n", $framework->cumulative_outcomes)
            : ($framework?->cumulative_outcomes ?? '')
    );
    $requiredCount = old('required_count', $framework?->required_count ?? 1);
?>

<div class="form-grid">
    <div class="field">
        <label for="name-<?php echo e($formId); ?>">Framework name</label>
        <input id="name-<?php echo e($formId); ?>" name="name" value="<?php echo e(old('name', $framework?->name)); ?>" required maxlength="150">
        <small>Official name of the stack, not the name of an individual microcredential.</small>
    </div>

    <div class="field">
        <label for="pqf-<?php echo e($formId); ?>">PQF level</label>
        <select id="pqf-<?php echo e($formId); ?>" name="pqf_level">
            <option value="">Select level</option>
            <?php for($level = 5; $level <= 8; $level++): ?>
                <option value="<?php echo e($level); ?>" <?php if((string) old('pqf_level', $framework?->pqf_level) === (string) $level): echo 'selected'; endif; ?>>Level <?php echo e($level); ?></option>
            <?php endfor; ?>
        </select>
        <small>Use the PQF level applicable to the combined recognition target.</small>
    </div>

    <div class="field field-wide">
        <label for="description-<?php echo e($formId); ?>">Framework description / rationale</label>
        <textarea id="description-<?php echo e($formId); ?>" name="description" maxlength="2000"><?php echo e(old('description', $framework?->description)); ?></textarea>
        <small>Explain why these microcredentials are grouped together and what the framework is intended to recognize.</small>
    </div>

    <div class="field field-wide">
        <label for="outcomes-<?php echo e($formId); ?>">Cumulative learning outcomes</label>
        <textarea id="outcomes-<?php echo e($formId); ?>" name="cumulative_outcomes" maxlength="4000" placeholder="One outcome per line"><?php echo e($outcomes); ?></textarea>
        <small>Enter the combined competencies demonstrated when the stack is completed. One outcome per line.</small>
    </div>

    <div class="field field-wide form-section-label">
        <strong>Academic recognition target</strong>
        <span>These fields describe the possible academic recognition; completion does not automatically create academic credit.</span>
    </div>

    <div class="field">
        <label for="target-<?php echo e($formId); ?>">Intended academic recognition</label>
        <input id="target-<?php echo e($formId); ?>" name="target_recognition" value="<?php echo e(old('target_recognition', $framework?->target_recognition)); ?>" maxlength="255" placeholder="e.g. Web Development elective recognition">
    </div>

    <div class="field">
        <label for="equivalent-course-<?php echo e($formId); ?>">Equivalent course</label>
        <input id="equivalent-course-<?php echo e($formId); ?>" name="equivalent_course" value="<?php echo e(old('equivalent_course', $framework?->equivalent_course)); ?>" maxlength="255" placeholder="e.g. IT 4xx – Web Development">
    </div>

    <div class="field">
        <label for="equivalent-units-<?php echo e($formId); ?>">Equivalent academic units</label>
        <input id="equivalent-units-<?php echo e($formId); ?>" name="equivalent_units" type="number" min="0" step="0.01" value="<?php echo e(old('equivalent_units', $framework?->equivalent_units)); ?>" placeholder="e.g. 3">
    </div>

    <div class="field">
        <label for="credit-equivalency-<?php echo e($formId); ?>">Credit equivalency statement</label>
        <input id="credit-equivalency-<?php echo e($formId); ?>" name="credit_equivalency" value="<?php echo e(old('credit_equivalency', $framework?->credit_equivalency)); ?>" maxlength="1000" placeholder="e.g. May be recognized as 3 academic units">
    </div>

    <div class="field">
        <label for="approving-unit-<?php echo e($formId); ?>">Approving academic unit</label>
        <input id="approving-unit-<?php echo e($formId); ?>" name="approving_academic_unit" value="<?php echo e(old('approving_academic_unit', $framework?->approving_academic_unit)); ?>" maxlength="255" placeholder="e.g. College of Computing Sciences">
        <small>Enter the academic unit, not an individual role such as “Dean”.</small>
    </div>

    <div class="field">
        <label for="conditions-<?php echo e($formId); ?>">Academic recognition conditions</label>
        <textarea id="conditions-<?php echo e($formId); ?>" name="credit_recognition_conditions" maxlength="2000" placeholder="Additional institutional conditions for recognition"><?php echo e(old('credit_recognition_conditions', $framework?->credit_recognition_conditions)); ?></textarea>
    </div>

    <div class="field field-wide form-section-label">
        <strong>Completion rule</strong>
        <span>Choose whether every required microcredential must be completed or only a minimum number of them.</span>
    </div>

    <div class="field">
        <label for="completion-mode-<?php echo e($formId); ?>">Completion model</label>
        <select id="completion-mode-<?php echo e($formId); ?>" name="completion_mode" data-completion-mode>
            <option value="all_required" <?php if($completionMode === 'all_required'): echo 'selected'; endif; ?>>Complete all required microcredentials</option>
            <option value="minimum_required" <?php if($completionMode === 'minimum_required'): echo 'selected'; endif; ?>>Complete a minimum number of required microcredentials</option>
        </select>
        <small>For example, “2 of 3” requires all three to be marked Required and sets the minimum to 2.</small>
    </div>

    <div class="field" data-minimum-count-field>
        <label for="required-count-<?php echo e($formId); ?>">Minimum required microcredentials</label>
        <input id="required-count-<?php echo e($formId); ?>" name="required_count" type="number" min="1" step="1" value="<?php echo e($requiredCount); ?>">
        <small data-required-count-help>The system will save this automatically when “Complete all” is selected.</small>
    </div>

    <div class="field">
        <label for="sequence-<?php echo e($formId); ?>">Completion order</label>
        <input type="hidden" name="sequence_required" value="0">
        <select id="sequence-<?php echo e($formId); ?>" name="sequence_required">
            <option value="0" <?php if(! old('sequence_required', $framework?->sequence_required)): echo 'selected'; endif; ?>>No fixed order</option>
            <option value="1" <?php if((bool) old('sequence_required', $framework?->sequence_required)): echo 'selected'; endif; ?>>Complete in listed order</option>
        </select>
        <small>If enabled, the required microcredentials must be completed in the configured order.</small>
    </div>
</div>

<div class="completion-rule-summary" data-completion-summary data-form-id="<?php echo e($formId); ?>"></div>

<div class="requirement-header">
    <div>
        <h3>Microcredential requirements</h3>
        <p>Mark a microcredential as <strong>Required</strong> if it counts toward the framework's completion target. Unmarked items are optional.</p>
    </div>
    <button class="btn-action btn-secondary" type="button" onclick="addRequirementRow('<?php echo e($formId); ?>')">Add requirement</button>
</div>

<div class="requirement-table-head" aria-hidden="true">
    <span>Microcredential</span>
    <span>Order</span>
    <span>Completion</span>
    <span></span>
</div>

<div id="requirements-<?php echo e($formId); ?>" data-next-index="<?php echo e($framework?->requirements?->count() ?? 0); ?>">
    <?php $__currentLoopData = ($framework?->requirements ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $requirement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="requirement-row">
            <input type="hidden" name="requirements[<?php echo e($index); ?>][id]" value="<?php echo e($requirement->id); ?>">
            <select name="requirements[<?php echo e($index); ?>][course_id]" required>
                <option value="">Select microcredential</option>
                <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($course->id); ?>" <?php if($requirement->course_id === $course->id): echo 'selected'; endif; ?>><?php echo e($course->title); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <input name="requirements[<?php echo e($index); ?>][order]" type="number" min="1" step="1" value="<?php echo e($requirement->order); ?>" required>
            <label class="requirement-required"><input name="requirements[<?php echo e($index); ?>][is_required]" type="checkbox" value="1" <?php if($requirement->is_required): echo 'checked'; endif; ?>> Required</label>
            <button class="remove-row" type="button" aria-label="Remove requirement" onclick="this.closest('.requirement-row').remove(); updateCompletionRule('<?php echo e($formId); ?>')">×</button>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<template id="requirement-template-<?php echo e($formId); ?>">
    <div class="requirement-row">
        <select data-field="course_id" required>
            <option value="">Select microcredential</option>
            <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($course->id); ?>"><?php echo e($course->title); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <input data-field="order" type="number" min="1" step="1" value="1" required>
        <label class="requirement-required"><input data-field="is_required" type="checkbox" value="1"> Required</label>
        <button class="remove-row" type="button" aria-label="Remove requirement" onclick="this.closest('.requirement-row').remove(); updateCompletionRule('<?php echo e($formId); ?>')">×</button>
    </div>
</template>

<script>
    function updateCompletionRule(formId) {
        const root = document.getElementById('requirements-' + formId);
        const mode = document.getElementById('completion-mode-' + formId);
        const countField = document.querySelector('#required-count-' + formId)?.closest('.field');
        const countInput = document.getElementById('required-count-' + formId);
        const summary = document.querySelector('[data-completion-summary][data-form-id="' + formId + '"]');
        if (!root || !mode || !countInput) return;

        const requiredCount = root.querySelectorAll('input[data-field="is_required"]:checked, .requirement-required input[type="checkbox"]:checked').length;
        const minimumMode = mode.value === 'minimum_required';

        if (countField) countField.style.display = minimumMode ? '' : '';
        countInput.disabled = !minimumMode;
        countInput.max = Math.max(requiredCount, 1);
        if (!minimumMode) countInput.value = Math.max(requiredCount, 1);
        if (minimumMode && Number(countInput.value || 0) > requiredCount) countInput.value = Math.max(requiredCount, 1);

        if (summary) {
            summary.textContent = minimumMode
                ? `Completion target: ${countInput.value || 0} of ${requiredCount} required microcredential${requiredCount === 1 ? '' : 's'}.`
                : `Completion target: all ${requiredCount} required microcredential${requiredCount === 1 ? '' : 's'}.`;
        }
    }

    function addRequirementRow(formId) {
        const container = document.getElementById('requirements-' + formId);
        const template = document.getElementById('requirement-template-' + formId);
        if (!container || !template) return;
        const index = Number(container.dataset.nextIndex || 0);
        const row = template.content.cloneNode(true);
        row.querySelector('[data-field="course_id"]').name = 'requirements[' + index + '][course_id]';
        row.querySelector('[data-field="order"]').name = 'requirements[' + index + '][order]';
        row.querySelector('[data-field="is_required"]').name = 'requirements[' + index + '][is_required]';
        container.appendChild(row);
        container.dataset.nextIndex = String(index + 1);
        updateCompletionRule(formId);
    }

    document.addEventListener('change', function (event) {
        const mode = event.target.closest('[data-completion-mode]');
        if (mode) updateCompletionRule(mode.id.replace('completion-mode-', ''));
        if (event.target.matches('.requirement-required input[type="checkbox"]')) {
            const root = event.target.closest('[id^="requirements-"]');
            if (root) updateCompletionRule(root.id.replace('requirements-', ''));
        }
    });

    document.addEventListener('input', function (event) {
        if (!event.target.matches('[id^="required-count-"]')) return;
        const formId = event.target.id.replace('required-count-', '');
        updateCompletionRule(formId);
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[id^="completion-mode-"]').forEach(function (mode) {
            updateCompletionRule(mode.id.replace('completion-mode-', ''));
        });
    });
</script>
<?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/admin/stacking-framework-fields.blade.php ENDPATH**/ ?>