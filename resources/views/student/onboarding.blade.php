<x-layout title="Student Onboarding | UpSkill PSU" shell="student" page="onboarding">



    <div class="onboard-brand" aria-label="UpSkill PSU onboarding">
        <span>UP<span class="brand-gold">SKILL</span></span>
        <span class="brand-psu">PSU</span>
        <span class="brand-label">ONBOARDING</span>
    </div>

    <div class="wizard-wrap">
        <div class="wizard-progress" aria-live="polite">
            <div class="progress-copy"><span id="step-count">Step 1 of 9</span><span id="progress-percent">11% complete</span></div>
            <div class="progress-track ui-progress-track" role="progressbar" aria-label="Onboarding progress" aria-valuemin="1" aria-valuemax="9" aria-valuenow="1">
                <div class="progress-fill ui-progress-fill" id="progress-fill"></div>
            </div>
        </div>

        @if ($errors->any())
            <div class="validation-message" id="server-errors">
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @else
            <div class="validation-message" id="server-errors" aria-live="assertive"></div>
        @endif

        <form method="POST" action="{{ route('profile.complete') }}" id="student-onboarding-form" novalidate>
            @csrf

            <section class="step-panel" data-step="0">
                <h1 class="step-title ui-page-title">When were you born?</h1>
                <p class="step-subtitle ui-page-subtitle">This helps us personalize your learning experience.</p>
                <div class="illustration"><img src="{{ asset('Images/Date of birth.png') }}" alt="Date of birth"></div>
                <div>
                    <label class="field-label ui-label" for="date_of_birth">Date of birth</label>
                    <input class="field-control ui-input" id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" max="{{ now()->toDateString() }}" data-required>
                </div>
            </section>

            <section class="step-panel" data-step="1" hidden>
                <h1 class="step-title ui-page-title">How do you describe your gender?</h1>
                <p class="step-subtitle ui-page-subtitle">Choose the option you’re most comfortable sharing.</p>
                <div class="illustration"><img src="{{ asset('Images/Gender.png') }}" alt="Gender"></div>
                <div>
                    <label class="field-label ui-label" for="gender">Gender</label>
                    <select class="field-control ui-select" id="gender" name="gender" data-required>
                        <option value="">Select your answer</option>
                        @foreach (['Male', 'Female', 'Prefer not to say'] as $value)
                            <option value="{{ $value }}" @selected(old('gender') === $value)>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </section>

            <section class="step-panel" data-step="2" hidden>
                <h1 class="step-title ui-page-title">Where did you study?</h1>
                <p class="step-subtitle ui-page-subtitle">Choose your school or university.</p>
                <div class="illustration"><img src="{{ asset('Images/School or university.png') }}" alt="School or university"></div>
                <div>
                    <label class="field-label ui-label" for="school">School or university</label>
                    <select class="field-control ui-select" id="school" name="school" data-required>
                        <option value="">Select your school</option>
                        @foreach (($schools ?? []) as $school)
                            <option value="{{ $school }}" @selected(old('school') === $school)>{{ $school }}</option>
                        @endforeach
                        <option value="Other" @selected(old('school') === 'Other')>Other</option>
                    </select>
                    <div class="other-school" id="other-school-wrap" @if(old('school') !== 'Other') hidden @endif>
                        <label class="field-label ui-label" for="school_other">Enter your school name</label>
                        <input class="field-control ui-input" id="school_other" name="school_other" type="text" maxlength="255" value="{{ old('school_other') }}" data-required-when="other-school">
                    </div>
                </div>
            </section>

            <section class="step-panel" data-step="3" hidden>
                <h1 class="step-title ui-page-title">Your education</h1>
                <p class="step-subtitle ui-page-subtitle">This helps us tailor the right starting point.</p>
                <div class="illustration"><img src="{{ asset('Images/Highest education level.png') }}" alt="Highest education level"></div>
                <div>
                    <label class="field-label ui-label" for="education">Highest level of education</label>
                    <select class="field-control ui-select" id="education" name="education" data-required>
                        <option value="">Select education level</option>
                        @foreach (['Senior High School','Vocational / Technical','Undergraduate (College)',"Bachelor's Degree","Master's Degree",'Doctorate / PhD'] as $value)
                            <option value="{{ $value }}" @selected(old('education') === $value)>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </section>

            <section class="step-panel" data-step="4" hidden>
                <h1 class="step-title ui-page-title">Where are you based?</h1>
                <p class="step-subtitle ui-page-subtitle">Share the address you want associated with your profile.</p>
                <div class="illustration"><img src="{{ asset('Images/Address.png') }}" alt="Address"></div>
                <div>
                    <label class="field-label ui-label" for="address">Address</label>
                    <textarea class="field-control ui-textarea" id="address" name="address" rows="3" maxlength="500" data-required>{{ old('address') }}</textarea>
                </div>
            </section>

            <section class="step-panel" data-step="5" hidden>
                <h1 class="step-title ui-page-title">What do you want to learn?</h1>
                <p class="step-subtitle ui-page-subtitle">Choose every skill area that sparks your curiosity.</p>
                <div class="illustration"><img src="{{ asset('Images/Skills you want to learn.png') }}" alt="Skills you want to learn"></div>
                <div class="skills-grid" data-skill-pager>
                    @foreach (($skill_options ?? []) as $skill)
                        <label class="skill-option" data-skill-option>
                            <input type="checkbox" name="skills_want[]" value="{{ $skill }}" @checked(in_array($skill, (array) old('skills_want', []), true))>
                            <span class="skill-bubble">{{ $skill }}</span>
                        </label>
                    @endforeach
                </div>
                <button class="more-skills ui-button ui-button--secondary ui-button--sm" type="button" data-more-skills>More +</button>
                <p class="skills-count" data-skill-count aria-live="polite"></p>
            </section>

            <section class="step-panel" data-step="6" hidden>
                <h1 class="step-title ui-page-title">What skills do you already have?</h1>
                <p class="step-subtitle ui-page-subtitle">Choose every skill you have some experience with.</p>
                <div class="illustration"><img src="{{ asset('Images/Skills you already have.png') }}" alt="Skills you already have"></div>
                <div class="skills-grid" data-skill-pager>
                    @foreach (($skill_options ?? []) as $skill)
                        <label class="skill-option" data-skill-option>
                            <input type="checkbox" name="skills_have[]" value="{{ $skill }}" @checked(in_array($skill, (array) old('skills_have', []), true))>
                            <span class="skill-bubble">{{ $skill }}</span>
                        </label>
                    @endforeach
                </div>
                <button class="more-skills ui-button ui-button--secondary ui-button--sm" type="button" data-more-skills>More +</button>
                <p class="skills-count" data-skill-count aria-live="polite"></p>
            </section>

            <section class="step-panel" data-step="7" hidden>
                <h1 class="step-title ui-page-title">What do you want to become?</h1>
                <p class="step-subtitle ui-page-subtitle">Tell us about the career goal you’re working toward.</p>
                <div class="illustration"><img src="{{ asset('Images/Career goal.png') }}" alt="Career goal"></div>
                <div>
                    <label class="field-label ui-label" for="career_goal">Career goal</label>
                    <input class="field-control ui-input" id="career_goal" name="career_goal" type="text" maxlength="255" placeholder="e.g. Software Engineer" value="{{ old('career_goal') }}" data-required>
                </div>
            </section>

            <section class="step-panel" data-step="8" hidden>
                <h1 class="step-title ui-page-title">Anything else you’d like us to know?</h1>
                <p class="step-subtitle ui-page-subtitle">This is optional. You can update it later in your profile.</p>
                <div class="illustration"><img src="{{ asset('Images/About you.png') }}" alt="About you"></div>
                <div>
                    <label class="field-label ui-label" for="bio">About you <span style="font-weight:400;color:var(--ui-text-muted)">(optional)</span></label>
                    <textarea class="field-control ui-textarea" id="bio" name="bio" rows="4" maxlength="2000" placeholder="Share what success looks like for you.">{{ old('bio') }}</textarea>
                </div>
            </section>

            <div class="wizard-actions">
                <button class="wizard-button secondary ui-button ui-button--secondary" type="button" id="previous-step" hidden>Back</button>
                <div class="right-actions">
                    <button class="wizard-button ui-button ui-button--primary" type="button" id="next-step">Continue <span aria-hidden="true">→</span></button>
                    <button class="wizard-button ui-button ui-button--primary" type="submit" id="finish-step" hidden>Complete profile <span aria-hidden="true">→</span></button>
                </div>
            </div>
            <p class="finish-note" id="finish-note" hidden>Your pathway is optional and can be selected later.</p>
        </form>
    </div>

    <script>
        (function () {
            const form = document.getElementById('student-onboarding-form');
            const steps = Array.from(form.querySelectorAll('[data-step]'));
            const backButton = document.getElementById('previous-step');
            const nextButton = document.getElementById('next-step');
            const finishButton = document.getElementById('finish-step');
            const finishNote = document.getElementById('finish-note');
            const progressFill = document.getElementById('progress-fill');
            const stepCount = document.getElementById('step-count');
            const percentText = document.getElementById('progress-percent');
            const progressTrack = document.querySelector('.progress-track');
            const totalSteps = steps.length;
            let current = 0;
            const serverErrors = @json($errors->keys());

            function schoolOtherState() {
                const school = document.getElementById('school');
                const wrapper = document.getElementById('other-school-wrap');
                const other = document.getElementById('school_other');
                const shown = school.value === 'Other';
                wrapper.hidden = !shown;
                other.required = shown && !steps[2].hidden;
            }

            function showStep(index) {
                current = index;
                steps.forEach((step, i) => {
                    step.hidden = i !== current;
                    step.querySelectorAll('[data-required]').forEach(control => {
                        control.required = i === current;
                    });
                    step.querySelectorAll('[data-required-when="other-school"]').forEach(control => {
                        control.required = i === current && document.getElementById('school').value === 'Other';
                    });
                });
                schoolOtherState();
                backButton.hidden = current === 0;
                nextButton.hidden = current === totalSteps - 1;
                finishButton.hidden = current !== totalSteps - 1;
                finishNote.hidden = current !== totalSteps - 1;
                const percent = Math.round(((current + 1) / totalSteps) * 100);
                progressFill.style.width = percent + '%';
                stepCount.textContent = 'Step ' + (current + 1) + ' of ' + totalSteps;
                percentText.textContent = percent + '% complete';
                progressTrack.setAttribute('aria-valuenow', current + 1);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            function stepIsValid(step) {
                const invalid = Array.from(step.querySelectorAll('input,select,textarea'))
                    .find(control => control.required && !control.checkValidity());
                if (invalid) {
                    invalid.reportValidity();
                    return false;
                }
                return true;
            }

            function setPageForServerError() {
                for (const name of serverErrors) {
                    const normalized = name.replace(/\[\]$/, '').replace(/\.\d+$/, '');
                    const control = Array.from(form.elements).find(element =>
                        element.name && element.name.replace(/\[\]$/, '') === normalized
                    );
                    if (control) {
                        const page = control.closest('[data-step]');
                        if (page) return Number(page.dataset.step);
                    }
                }
                return 0;
            }

            form.querySelectorAll('[data-skill-pager]').forEach(grid => {
                const options = Array.from(grid.querySelectorAll('[data-skill-option]'));
                for (let index = options.length - 1; index > 0; index--) {
                    const randomIndex = Math.floor(Math.random() * (index + 1));
                    [options[index], options[randomIndex]] = [options[randomIndex], options[index]];
                }
                grid.append(...options);
                const moreButton = grid.parentElement.querySelector('[data-more-skills]');
                const countText = grid.parentElement.querySelector('[data-skill-count]');
                const selectedIndex = options.reduce((last, option, index) =>
                    option.querySelector('input').checked ? index : last, -1
                );
                let visible = Math.max(10, Math.ceil((selectedIndex + 1) / 10) * 10);

                function updateSkills() {
                    options.forEach((option, index) => { option.hidden = index >= visible; });
                    moreButton.hidden = visible >= options.length;
                    countText.textContent = options.filter(option => option.querySelector('input').checked).length + ' selected';
                }

                moreButton.addEventListener('click', function () {
                    visible = Math.min(options.length, visible + 10);
                    updateSkills();
                    const firstNew = options[Math.max(0, visible - 10)];
                    firstNew?.querySelector('input')?.focus({ preventScroll: true });
                });
                grid.addEventListener('change', updateSkills);
                updateSkills();
            });

            document.getElementById('school').addEventListener('change', schoolOtherState);
            backButton.addEventListener('click', () => showStep(Math.max(0, current - 1)));
            nextButton.addEventListener('click', () => {
                if (stepIsValid(steps[current])) showStep(Math.min(totalSteps - 1, current + 1));
            });
            form.addEventListener('submit', function (event) {
                for (let i = 0; i < totalSteps; i++) {
                    steps[i].querySelectorAll('[data-required]').forEach(control => { control.required = true; });
                    steps[i].querySelectorAll('[data-required-when="other-school"]').forEach(control => {
                        control.required = document.getElementById('school').value === 'Other';
                    });
                    if (!steps[i].checkValidity()) {
                        event.preventDefault();
                        showStep(i);
                        const invalid = steps[i].querySelector(':invalid');
                        invalid?.reportValidity();
                        return;
                    }
                }
            });

            const errorStep = setPageForServerError();
            showStep(errorStep);
        })();
    </script>
</x-layout>
