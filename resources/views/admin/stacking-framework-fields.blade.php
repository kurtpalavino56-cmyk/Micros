@php
    $completionMode = old('completion_mode', $framework?->completion_mode ?? 'all_required');
    $outcomes = old(
        'cumulative_outcomes',
        is_array($framework?->cumulative_outcomes ?? null)
            ? implode("\n", $framework->cumulative_outcomes)
            : ($framework?->cumulative_outcomes ?? '')
    );
    $requiredCount = old('required_count', $framework?->required_count ?? 1);
@endphp

<div class="form-grid">
    <div class="field">
        <label for="name-{{ $formId }}">Framework name</label>
        <input id="name-{{ $formId }}" name="name" value="{{ old('name', $framework?->name) }}" required maxlength="150">
        <small>Official name of the stack, not the name of an individual microcredential.</small>
    </div>

    <div class="field">
        <label for="pqf-{{ $formId }}">PQF level</label>
        <select id="pqf-{{ $formId }}" name="pqf_level">
            <option value="">Select level</option>
            @for($level = 5; $level <= 8; $level++)
                <option value="{{ $level }}" @selected((string) old('pqf_level', $framework?->pqf_level) === (string) $level)>Level {{ $level }}</option>
            @endfor
        </select>
        <small>Use the PQF level applicable to the combined recognition target.</small>
    </div>

    <div class="field field-wide">
        <label for="description-{{ $formId }}">Framework description / rationale</label>
        <textarea id="description-{{ $formId }}" name="description" maxlength="2000">{{ old('description', $framework?->description) }}</textarea>
        <small>Explain why these microcredentials are grouped together and what the framework is intended to recognize.</small>
    </div>

    <div class="field field-wide">
        <label for="outcomes-{{ $formId }}">Cumulative learning outcomes</label>
        <textarea id="outcomes-{{ $formId }}" name="cumulative_outcomes" maxlength="4000" placeholder="One outcome per line">{{ $outcomes }}</textarea>
        <small>Enter the combined competencies demonstrated when the stack is completed. One outcome per line.</small>
    </div>

    <div class="field field-wide form-section-label">
        <strong>Academic recognition target</strong>
        <span>These fields describe the possible academic recognition; completion does not automatically create academic credit.</span>
    </div>

    <div class="field">
        <label for="target-{{ $formId }}">Intended academic recognition</label>
        <input id="target-{{ $formId }}" name="target_recognition" value="{{ old('target_recognition', $framework?->target_recognition) }}" maxlength="255" placeholder="e.g. Web Development elective recognition">
    </div>

    <div class="field">
        <label for="equivalent-course-{{ $formId }}">Equivalent course</label>
        <input id="equivalent-course-{{ $formId }}" name="equivalent_course" value="{{ old('equivalent_course', $framework?->equivalent_course) }}" maxlength="255" placeholder="e.g. IT 4xx – Web Development">
    </div>

    <div class="field">
        <label for="equivalent-units-{{ $formId }}">Equivalent academic units</label>
        <input id="equivalent-units-{{ $formId }}" name="equivalent_units" type="number" min="0" step="0.01" value="{{ old('equivalent_units', $framework?->equivalent_units) }}" placeholder="e.g. 3">
    </div>

    <div class="field">
        <label for="credit-equivalency-{{ $formId }}">Credit equivalency statement</label>
        <input id="credit-equivalency-{{ $formId }}" name="credit_equivalency" value="{{ old('credit_equivalency', $framework?->credit_equivalency) }}" maxlength="1000" placeholder="e.g. May be recognized as 3 academic units">
    </div>

    <div class="field">
        <label for="approving-unit-{{ $formId }}">Approving academic unit</label>
        <input id="approving-unit-{{ $formId }}" name="approving_academic_unit" value="{{ old('approving_academic_unit', $framework?->approving_academic_unit) }}" maxlength="255" placeholder="e.g. College of Computing Sciences">
        <small>Enter the academic unit, not an individual role such as “Dean”.</small>
    </div>

    <div class="field">
        <label for="conditions-{{ $formId }}">Academic recognition conditions</label>
        <textarea id="conditions-{{ $formId }}" name="credit_recognition_conditions" maxlength="2000" placeholder="Additional institutional conditions for recognition">{{ old('credit_recognition_conditions', $framework?->credit_recognition_conditions) }}</textarea>
    </div>

    <div class="field field-wide form-section-label">
        <strong>Completion rule</strong>
        <span>Choose whether every required microcredential must be completed or only a minimum number of them.</span>
    </div>

    <div class="field">
        <label for="completion-mode-{{ $formId }}">Completion model</label>
        <select id="completion-mode-{{ $formId }}" name="completion_mode" data-completion-mode>
            <option value="all_required" @selected($completionMode === 'all_required')>Complete all required microcredentials</option>
            <option value="minimum_required" @selected($completionMode === 'minimum_required')>Complete a minimum number of required microcredentials</option>
        </select>
        <small>For example, “2 of 3” requires all three to be marked Required and sets the minimum to 2.</small>
    </div>

    <div class="field" data-minimum-count-field>
        <label for="required-count-{{ $formId }}">Minimum required microcredentials</label>
        <input id="required-count-{{ $formId }}" name="required_count" type="number" min="1" step="1" value="{{ $requiredCount }}">
        <small data-required-count-help>The system will save this automatically when “Complete all” is selected.</small>
    </div>

    <div class="field">
        <label for="sequence-{{ $formId }}">Completion order</label>
        <input type="hidden" name="sequence_required" value="0">
        <select id="sequence-{{ $formId }}" name="sequence_required">
            <option value="0" @selected(! old('sequence_required', $framework?->sequence_required))>No fixed order</option>
            <option value="1" @selected((bool) old('sequence_required', $framework?->sequence_required))>Complete in listed order</option>
        </select>
        <small>If enabled, the required microcredentials must be completed in the configured order.</small>
    </div>
</div>

<div class="completion-rule-summary" data-completion-summary data-form-id="{{ $formId }}"></div>

<div class="requirement-header">
    <div>
        <h3>Microcredential requirements</h3>
        <p>Mark a microcredential as <strong>Required</strong> if it counts toward the framework's completion target. Unmarked items are optional.</p>
    </div>
    <button class="btn-action btn-secondary" type="button" onclick="addRequirementRow('{{ $formId }}')">Add requirement</button>
</div>

<div class="requirement-table-head" aria-hidden="true">
    <span>Microcredential</span>
    <span>Order</span>
    <span>Completion</span>
    <span></span>
</div>

<div id="requirements-{{ $formId }}" data-next-index="{{ $framework?->requirements?->count() ?? 0 }}">
    @foreach(($framework?->requirements ?? []) as $index => $requirement)
        <div class="requirement-row">
            <input type="hidden" name="requirements[{{ $index }}][id]" value="{{ $requirement->id }}">
            <select name="requirements[{{ $index }}][course_id]" required>
                <option value="">Select microcredential</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}" @selected($requirement->course_id === $course->id)>{{ $course->title }}</option>
                @endforeach
            </select>
            <input name="requirements[{{ $index }}][order]" type="number" min="1" step="1" value="{{ $requirement->order }}" required>
            <label class="requirement-required"><input name="requirements[{{ $index }}][is_required]" type="checkbox" value="1" @checked($requirement->is_required)> Required</label>
            <button class="remove-row" type="button" aria-label="Remove requirement" onclick="this.closest('.requirement-row').remove(); updateCompletionRule('{{ $formId }}')">×</button>
        </div>
    @endforeach
</div>

<template id="requirement-template-{{ $formId }}">
    <div class="requirement-row">
        <select data-field="course_id" required>
            <option value="">Select microcredential</option>
            @foreach($courses as $course)<option value="{{ $course->id }}">{{ $course->title }}</option>@endforeach
        </select>
        <input data-field="order" type="number" min="1" step="1" value="1" required>
        <label class="requirement-required"><input data-field="is_required" type="checkbox" value="1"> Required</label>
        <button class="remove-row" type="button" aria-label="Remove requirement" onclick="this.closest('.requirement-row').remove(); updateCompletionRule('{{ $formId }}')">×</button>
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
