<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Student Onboarding | UpSkill PSU</title>
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: light;
            --navy: #111b3a;
            --blue: #1736c8;
            --gold: #ffd400;
            --gold-soft: #fff7d2;
            --ink: #182442;
            --muted: #73809a;
            --line: #e3e8f1;
            --canvas: #f4f6fb;
            --white: #fff;
            --shadow: 0 18px 42px rgba(23, 41, 87, .08);
        }
        *, *::before, *::after { box-sizing: border-box; }
        html { min-height:100%; background:var(--canvas); }
        body {
            min-height:100vh; margin:0; color:var(--ink);
            background:
                radial-gradient(ellipse at 2% 8%, rgba(255,244,185,.78), transparent 30rem),
                var(--canvas);
            font: 400 15px/1.55 'Inter', sans-serif;
        }
        button, input, select, textarea { font:inherit; }
        .onboard-brand {
            position:absolute; top:26px; left:clamp(20px,4vw,56px);
            display:flex; align-items:center; gap:8px; color:var(--blue);
            font:900 16px/1 'Poppins',sans-serif; letter-spacing:-.04em;
        }
        .onboard-brand .brand-gold { color:#efc400; }
        .onboard-brand .brand-psu { margin-left:1px; color:#67738b; font:500 10px/1 'Inter',sans-serif; letter-spacing:.04em; }
        .onboard-brand .brand-label { margin-left:3px; color:#102b8f; font:700 10px/1 'Inter',sans-serif; letter-spacing:.2em; }
        .wizard-wrap { width:min(100% - 36px, 674px); margin:0 auto; padding:76px 0 48px; }
        .wizard-progress { margin-bottom:25px; }
        .progress-copy { display:flex; justify-content:space-between; gap:12px; margin-bottom:12px; color:#8a96aa; font-size:11px; font-weight:700; }
        .progress-track { height:6px; overflow:hidden; border-radius:999px; background:#e0e5ed; }
        .progress-fill { width:11.12%; height:100%; border-radius:inherit; background:var(--gold); transition:width .25s ease; }
        .step-title { margin:0; color:#111a31; font:800 clamp(30px,5vw,46px)/1.06 'Poppins',sans-serif; letter-spacing:-.045em; }
        .step-subtitle { margin:9px 0 21px; color:#75819a; font-size:15px; }
        .illustration {
            min-height:176px; margin:0 0 21px; display:flex; flex-direction:column;
            align-items:center; justify-content:center; gap:11px; border:1px solid #e9edf4;
            border-radius:29px; background:linear-gradient(115deg,#fffbe6,#fff 62%,#f4f6fc);
            box-shadow:0 2px 2px rgba(21,36,65,.08); color:#25375b; font-size:13px; font-weight:600;
        }
        .sparkle { display:grid; width:56px; height:56px; place-items:center; border-radius:50%; background:#fff3b5; color:#122dc4; }
        .sparkle svg { width:25px; height:25px; }
        .step-panel { min-height:250px; }
        .step-panel[hidden] { display:none !important; }
        .field-label { display:block; margin:0 0 9px; color:#34415b; font-size:13px; font-weight:700; }
        .field-control {
            display:block; width:100%; min-height:58px; padding:14px 18px;
            border:1.5px solid #dfe5ef; border-radius:17px; outline:none;
            background:var(--white); color:var(--ink); font-size:15px;
            transition:border-color .18s, box-shadow .18s;
        }
        .field-control:focus { border-color:#4a63ff; box-shadow:0 0 0 3px rgba(74,99,255,.12); }
        textarea.field-control { min-height:130px; resize:vertical; }
        .field-hint { margin:9px 0 0; color:var(--muted); font-size:12px; }
        .validation-message {
            margin:0 0 17px; padding:12px 15px; border:1px solid #ffd8d8;
            border-radius:13px; background:#fff4f4; color:#a33232; font-size:13px; font-weight:600;
        }
        .validation-message:empty { display:none; }
        .validation-message ul { margin:0; padding-left:19px; }
        .skills-grid { display:flex; flex-wrap:wrap; gap:10px; }
        .skill-option { position:relative; display:inline-flex; }
        .skill-option[hidden] { display:none !important; }
        .skill-option input { position:absolute; width:1px; height:1px; opacity:0; }
        .skill-bubble {
            display:inline-flex; min-height:43px; align-items:center; justify-content:center;
            padding:9px 16px; border:1px solid #dfe5ef; border-radius:999px;
            background:#fff; color:#34415b; font-size:13px; font-weight:600;
            cursor:pointer; user-select:none; transition:transform .16s,border-color .16s,background .16s,box-shadow .16s,color .16s;
        }
        .skill-bubble:hover { transform:translateY(-1px); border-color:#aab8ff; background:#f8f9ff; }
        .skill-option input:checked + .skill-bubble { border-color:#263fd0; background:#e9edff; color:#172eae; box-shadow:0 0 0 2px rgba(38,63,208,.08); }
        .skill-option input:focus-visible + .skill-bubble { outline:3px solid rgba(74,99,255,.35); outline-offset:2px; }
        .more-skills {
            min-height:43px; padding:9px 17px; border:1px dashed #8998e9; border-radius:999px;
            background:#f7f8ff; color:#1835bf; font-size:13px; font-weight:800; cursor:pointer;
            transition:background .18s,transform .18s;
        }
        .more-skills:hover { transform:translateY(-1px); background:#edf0ff; }
        .skills-count { margin:13px 0 0; color:var(--muted); font-size:12px; }
        .other-school { margin-top:14px; }
        .other-school[hidden] { display:none; }
        .wizard-actions {
            display:flex; justify-content:space-between; align-items:center; gap:14px;
            margin-top:27px;
        }
        .wizard-actions .right-actions { display:flex; gap:10px; margin-left:auto; }
        .wizard-button {
            min-height:48px; padding:12px 23px; border:0; border-radius:15px;
            background:var(--gold); color:#10235d; font-size:13px; font-weight:800;
            cursor:pointer; box-shadow:0 8px 16px rgba(255,212,0,.2);
            transition:transform .16s,background .16s,box-shadow .16s;
        }
        .wizard-button:hover { transform:translateY(-2px); background:#ffe04d; box-shadow:0 11px 19px rgba(255,212,0,.3); }
        .wizard-button:active { transform:translateY(0); }
        .wizard-button.secondary { border:1px solid #dfe5ef; background:#fff; color:#394660; box-shadow:none; }
        .wizard-button.secondary:hover { background:#f7f8fb; box-shadow:0 6px 15px rgba(30,45,70,.08); }
        .wizard-button[hidden] { display:none; }
        .finish-note { margin:12px 0 0; color:var(--muted); font-size:12px; text-align:right; }
        @media (max-width:620px) {
            .onboard-brand { top:19px; left:18px; }
            .wizard-wrap { width:min(100% - 30px, 674px); padding-top:72px; }
            .illustration { min-height:143px; border-radius:23px; }
            .step-title { font-size:34px; }
            .step-subtitle { font-size:14px; }
            .step-panel { min-height:260px; }
            .skill-bubble { min-height:40px; padding:8px 13px; font-size:12px; }
            .wizard-actions { margin-top:18px; }
            .wizard-button { min-height:46px; padding:11px 17px; }
        }
        @media (prefers-reduced-motion:reduce) { *,*::before,*::after { scroll-behavior:auto !important; transition-duration:.01ms !important; } }
    </style>
</head>
<body>
    <div class="onboard-brand" aria-label="UpSkill PSU onboarding">
        <span>UP<span class="brand-gold">SKILL</span></span>
        <span class="brand-psu">PSU</span>
        <span class="brand-label">ONBOARDING</span>
    </div>

    <main class="wizard-wrap">
        <div class="wizard-progress" aria-live="polite">
            <div class="progress-copy"><span id="step-count">Step 1 of 9</span><span id="progress-percent">11% complete</span></div>
            <div class="progress-track" role="progressbar" aria-label="Onboarding progress" aria-valuemin="1" aria-valuemax="9" aria-valuenow="1">
                <div class="progress-fill" id="progress-fill"></div>
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
                <h1 class="step-title">When were you born?</h1>
                <p class="step-subtitle">This helps us personalize your learning experience.</p>
                <div class="illustration" aria-hidden="true"><span class="sparkle"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5 14.4 9.6 21.5 12l-7.1 2.4L12 21.5l-2.4-7.1L2.5 12l7.1-2.4z"/></svg></span><span>One step at a time</span></div>
                <div>
                    <label class="field-label" for="date_of_birth">Date of birth</label>
                    <input class="field-control" id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" max="{{ now()->toDateString() }}" data-required>
                </div>
            </section>

            <section class="step-panel" data-step="1" hidden>
                <h1 class="step-title">How do you describe your gender?</h1>
                <p class="step-subtitle">Choose the option you’re most comfortable sharing.</p>
                <div class="illustration" aria-hidden="true"><span class="sparkle"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5 14.4 9.6 21.5 12l-7.1 2.4L12 21.5l-2.4-7.1L2.5 12l7.1-2.4z"/></svg></span><span>Your profile, your choice</span></div>
                <div>
                    <label class="field-label" for="gender">Gender</label>
                    <select class="field-control" id="gender" name="gender" data-required>
                        <option value="">Select your answer</option>
                        @foreach (['Male', 'Female', 'Prefer not to say'] as $value)
                            <option value="{{ $value }}" @selected(old('gender') === $value)>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </section>

            <section class="step-panel" data-step="2" hidden>
                <h1 class="step-title">Where did you study?</h1>
                <p class="step-subtitle">Choose your school or university.</p>
                <div class="illustration" aria-hidden="true"><span class="sparkle"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5 14.4 9.6 21.5 12l-7.1 2.4L12 21.5l-2.4-7.1L2.5 12l7.1-2.4z"/></svg></span><span>Your education background</span></div>
                <div>
                    <label class="field-label" for="school">School or university</label>
                    <select class="field-control" id="school" name="school" data-required>
                        <option value="">Select your school</option>
                        @foreach (($schools ?? []) as $school)
                            <option value="{{ $school }}" @selected(old('school') === $school)>{{ $school }}</option>
                        @endforeach
                        <option value="Other" @selected(old('school') === 'Other')>Other</option>
                    </select>
                    <div class="other-school" id="other-school-wrap" @if(old('school') !== 'Other') hidden @endif>
                        <label class="field-label" for="school_other">Enter your school name</label>
                        <input class="field-control" id="school_other" name="school_other" type="text" maxlength="255" value="{{ old('school_other') }}" data-required-when="other-school">
                    </div>
                </div>
            </section>

            <section class="step-panel" data-step="3" hidden>
                <h1 class="step-title">Your education</h1>
                <p class="step-subtitle">This helps us tailor the right starting point.</p>
                <div class="illustration" aria-hidden="true"><span class="sparkle"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5 14.4 9.6 21.5 12l-7.1 2.4L12 21.5l-2.4-7.1L2.5 12l7.1-2.4z"/></svg></span><span>Choose your current level</span></div>
                <div>
                    <label class="field-label" for="education">Highest level of education</label>
                    <select class="field-control" id="education" name="education" data-required>
                        <option value="">Select education level</option>
                        @foreach (['Senior High School','Vocational / Technical','Undergraduate (College)',"Bachelor's Degree","Master's Degree",'Doctorate / PhD'] as $value)
                            <option value="{{ $value }}" @selected(old('education') === $value)>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </section>

            <section class="step-panel" data-step="4" hidden>
                <h1 class="step-title">Where are you based?</h1>
                <p class="step-subtitle">Share the address you want associated with your profile.</p>
                <div class="illustration" aria-hidden="true"><span class="sparkle"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5 14.4 9.6 21.5 12l-7.1 2.4L12 21.5l-7.1 2.4z"/></svg></span><span>Your profile details</span></div>
                <div>
                    <label class="field-label" for="address">Address</label>
                    <textarea class="field-control" id="address" name="address" rows="3" maxlength="500" data-required>{{ old('address') }}</textarea>
                </div>
            </section>

            <section class="step-panel" data-step="5" hidden>
                <h1 class="step-title">What do you want to learn?</h1>
                <p class="step-subtitle">Choose every skill area that sparks your curiosity.</p>
                <div class="illustration" aria-hidden="true"><span class="sparkle"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5 14.4 9.6 21.5 12l-7.1 2.4L12 21.5l-7.1 2.4z"/></svg></span><span>Explore your interests</span></div>
                <div class="skills-grid" data-skill-pager>
                    @foreach (($skill_options ?? []) as $skill)
                        <label class="skill-option" data-skill-option>
                            <input type="checkbox" name="skills_want[]" value="{{ $skill }}" @checked(in_array($skill, (array) old('skills_want', []), true))>
                            <span class="skill-bubble">{{ $skill }}</span>
                        </label>
                    @endforeach
                </div>
                <button class="more-skills" type="button" data-more-skills>More +</button>
                <p class="skills-count" data-skill-count aria-live="polite"></p>
            </section>

            <section class="step-panel" data-step="6" hidden>
                <h1 class="step-title">What skills do you already have?</h1>
                <p class="step-subtitle">Choose every skill you have some experience with.</p>
                <div class="illustration" aria-hidden="true"><span class="sparkle"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5 14.4 9.6 21.5 12l-7.1 2.4L12 21.5l-7.1 2.4z"/></svg></span><span>Recognize what you already know</span></div>
                <div class="skills-grid" data-skill-pager>
                    @foreach (($skill_options ?? []) as $skill)
                        <label class="skill-option" data-skill-option>
                            <input type="checkbox" name="skills_have[]" value="{{ $skill }}" @checked(in_array($skill, (array) old('skills_have', []), true))>
                            <span class="skill-bubble">{{ $skill }}</span>
                        </label>
                    @endforeach
                </div>
                <button class="more-skills" type="button" data-more-skills>More +</button>
                <p class="skills-count" data-skill-count aria-live="polite"></p>
            </section>

            <section class="step-panel" data-step="7" hidden>
                <h1 class="step-title">What do you want to become?</h1>
                <p class="step-subtitle">Tell us about the career goal you’re working toward.</p>
                <div class="illustration" aria-hidden="true"><span class="sparkle"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5 14.4 9.6 21.5 12l-7.1 2.4L12 21.5l-7.1 2.4z"/></svg></span><span>Picture your next milestone</span></div>
                <div>
                    <label class="field-label" for="career_goal">Career goal</label>
                    <input class="field-control" id="career_goal" name="career_goal" type="text" maxlength="255" placeholder="e.g. Software Engineer" value="{{ old('career_goal') }}" data-required>
                </div>
            </section>

            <section class="step-panel" data-step="8" hidden>
                <h1 class="step-title">Anything else you’d like us to know?</h1>
                <p class="step-subtitle">This is optional. You can update it later in your profile.</p>
                <div class="illustration" aria-hidden="true"><span class="sparkle"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5 14.4 9.6 21.5 12l-7.1 2.4L12 21.5l-7.1 2.4z"/></svg></span><span>A little context goes a long way</span></div>
                <div>
                    <label class="field-label" for="bio">About you <span style="font-weight:400;color:#8290a7">(optional)</span></label>
                    <textarea class="field-control" id="bio" name="bio" rows="4" maxlength="2000" placeholder="Share what success looks like for you.">{{ old('bio') }}</textarea>
                </div>
            </section>

            <div class="wizard-actions">
                <button class="wizard-button secondary" type="button" id="previous-step" hidden>Back</button>
                <div class="right-actions">
                    <button class="wizard-button" type="button" id="next-step">Continue <span aria-hidden="true">→</span></button>
                    <button class="wizard-button" type="submit" id="finish-step" hidden>Complete profile <span aria-hidden="true">→</span></button>
                </div>
            </div>
            <p class="finish-note" id="finish-note" hidden>Your pathway is optional and can be selected later.</p>
        </form>
    </main>

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
</body>
</html>

