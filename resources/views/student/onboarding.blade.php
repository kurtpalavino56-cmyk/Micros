<x-layout title="Student Onboarding | UpSkill PSU" shell="auth" page="onboarding">
    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800;900&display=swap" rel="stylesheet">
    @endpush



    <div class="onboard-brand" aria-label="UpSkill PSU onboarding">
        <span>UP<span class="brand-gold">SKILL</span></span>
        <span class="brand-psu">PSU</span>
        <span class="brand-label">ONBOARDING</span>
    </div>

    <div class="wizard-wrap">
        <div class="wizard-progress" aria-live="polite">
            <div class="progress-copy"><span id="step-count">Step 1 of 9</span><span id="progress-percent">11% complete</span></div>
            <div class="progress-track" role="progressbar" aria-label="Onboarding progress" aria-valuemin="1" aria-valuemax="9" aria-valuenow="1">
                <div class="progress-fill" id="progress-fill"></div>
            </div>
        </div>

        @if ($errors->any())
            @include('components.flash-toast', [
                'message' => implode(' · ', $errors->all()),
                'type' => 'error',
            ])
        @endif

        <form method="POST" action="{{ route('profile.complete') }}" id="student-onboarding-form" novalidate>
            @csrf

            <section class="step-panel" data-step="0">
                <h1 class="step-title">When were you born?</h1>
                <p class="step-subtitle">This helps us personalize your learning experience.</p>
                <div class="illustration"><img src="{{ asset('Images/Date of birth.png') }}" alt="Date of birth"></div>
                <div>
                    <label class="field-label" for="date_of_birth">Date of birth</label>
                    <input class="field-control" id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" max="{{ now()->toDateString() }}" data-required>
                </div>
            </section>

            <section class="step-panel" data-step="1" hidden>
                <h1 class="step-title">How do you describe your gender?</h1>
                <p class="step-subtitle">Choose the option you’re most comfortable sharing.</p>
                <div class="illustration"><img src="{{ asset('Images/Gender.png') }}" alt="Gender"></div>
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
                <div class="illustration"><img src="{{ asset('Images/School or university.png') }}" alt="School or university"></div>
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
                <div class="illustration"><img src="{{ asset('Images/Highest education level.png') }}" alt="Highest education level"></div>
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
                <div class="illustration"><img src="{{ asset('Images/Address.png') }}" alt="Address"></div>
                <div class="address-fields">
                    <div>
                        <label class="field-label" for="address_province">Province</label>
                        <select class="field-control" id="address_province" name="address_province" data-required>
                            <option value="">Select your province</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label" for="address_city">City / Municipality</label>
                        <select class="field-control" id="address_city" name="address_city" data-required>
                            <option value="">Select your city or municipality</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label" for="address_barangay">Barangay</label>
                        <select class="field-control" id="address_barangay" name="address_barangay" data-required>
                            <option value="">Select your barangay</option>
                        </select>
                    </div>
                    <input type="hidden" id="address" name="address" value="{{ old('address') }}">
                    <p class="field-hint" id="address-status" role="status" aria-live="polite">Location options use PSGC data.</p>
                    <button class="wizard-button secondary" type="button" id="address-retry" hidden>Retry loading locations</button>
                </div>
            </section>

            <section class="step-panel" data-step="5" hidden>
                <h1 class="step-title">What do you want to learn?</h1>
                <p class="step-subtitle">Choose every skill area that sparks your curiosity.</p>
                <div class="illustration"><img src="{{ asset('Images/Skills you want to learn.png') }}" alt="Skills you want to learn"></div>
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
                <div class="illustration"><img src="{{ asset('Images/Skills you already have.png') }}" alt="Skills you already have"></div>
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
                <div class="illustration"><img src="{{ asset('Images/Career goal.png') }}" alt="Career goal"></div>
                <div>
                    <label class="field-label" for="career_goal">Career goal</label>
                    <select class="field-control career-goal-control" id="career_goal" name="career_goal" data-required>
                        <option value="">Select a career goal</option>
                        @foreach (($career_options ?? []) as $career)
                            <option value="{{ $career }}" @selected(old('career_goal') === $career)>{{ $career }}</option>
                        @endforeach
                    </select>
                    <div class="other-school" id="other-career-wrap" @if(old('career_goal') !== 'Other') hidden @endif>
                        <label class="field-label" for="career_goal_other">Enter your career goal</label>
                        <input class="field-control career-goal-control" id="career_goal_other" name="career_goal_other" type="text" maxlength="255" placeholder="e.g. Architect" value="{{ old('career_goal_other') }}" data-required-when="other-career">
                    </div>
                </div>
            </section>

            <section class="step-panel" data-step="8" hidden>
                <h1 class="step-title">What is your reason for taking microcredentials?</h1>
                <p class="step-subtitle">What is your main goal?</p>
                <div class="illustration"><img src="{{ asset('Images/About you.png') }}" alt="About you"></div>
                <div class="main-goal-grid" role="radiogroup" aria-label="What is your main goal?">
                    @foreach ([
                        ['Advance my career', 'Grow in your current career', '↗'],
                        ['Start a new career', 'Prepare for a new career path', '✦'],
                        ['Build skills for a new career', 'Learn skills for your next step', '⌘'],
                        ['Advance my education', 'Continue your education', '▤'],
                        ['Learn for fun', 'Explore a topic you enjoy', '♡'],
                        ['Other', 'Share another goal', '+'],
                    ] as [$goal, $description, $icon])
                        <label class="main-goal-card">
                            <input type="radio" name="main_goal" value="{{ $goal }}" @checked(old('main_goal') === $goal) data-required>
                            <span class="main-goal-card-content">
                                <span class="main-goal-icon" aria-hidden="true">{{ $icon }}</span>
                                <span class="main-goal-title">{{ $goal }}</span>
                                <span class="main-goal-description">{{ $description }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                <div class="main-goal-other" id="other-main-goal-wrap" @if(old('main_goal') !== 'Other') hidden @endif>
                    <label class="field-label" for="main_goal_other">Tell us your main goal</label>
                    <input class="field-control" id="main_goal_other" name="main_goal_other" type="text" maxlength="255" placeholder="Enter your reason for taking microcredentials" value="{{ old('main_goal_other') }}" data-required-when="other-main-goal">
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
            const provinceSelect = document.getElementById('address_province');
            const citySelect = document.getElementById('address_city');
            const barangaySelect = document.getElementById('address_barangay');
            const addressStatus = document.getElementById('address-status');
            const addressRetry = document.getElementById('address-retry');
            const totalSteps = steps.length;
            let current = 0;
            let provincesLoaded = false;
            let provincesLoading = false;
            const savedAddress = {
                province: @json(old('address_province')),
                city: @json(old('address_city')),
                barangay: @json(old('address_barangay')),
            };
            const serverErrors = @json($errors->keys());

            function setLocationOptions(select, locations, placeholder) {
                select.replaceChildren(new Option(placeholder, ''));
                locations
                    .sort((left, right) => left.name.localeCompare(right.name))
                    .forEach(location => {
                        const option = new Option(location.name, location.name);
                        option.dataset.code = location.code;
                        select.add(option);
                    });
            }

            async function fetchLocations(path) {
                const response = await fetch('https://psgc.gitlab.io/api/' + path, {
                    headers: { Accept: 'application/json' },
                });
                if (!response.ok) {
                    throw new Error('The location service returned an error.');
                }
                const locations = await response.json();
                if (!Array.isArray(locations) || locations.some(location =>
                    typeof location.name !== 'string' || typeof location.code !== 'string'
                )) {
                    throw new Error('The location service returned invalid data.');
                }
                return locations;
            }

            function showLocationError(error) {
                console.error(error);
                addressStatus.textContent = 'Location options could not be loaded. Check your connection and try again.';
                addressRetry.hidden = false;
            }

            async function loadBarangays(selectedName = '') {
                const cityOption = citySelect.selectedOptions[0];
                const cityCode = cityOption?.dataset.code;
                barangaySelect.replaceChildren(new Option('Select your barangay', ''));
                if (!cityCode) {
                    addressStatus.textContent = 'Choose your city or municipality to load barangays.';
                    return;
                }

                addressStatus.textContent = 'Loading barangays...';
                try {
                    const locations = await fetchLocations('cities-municipalities/' + encodeURIComponent(cityCode) + '/barangays/');
                    if (citySelect.selectedOptions[0]?.dataset.code !== cityCode) {
                        return;
                    }
                    setLocationOptions(barangaySelect, locations, 'Select your barangay');
                    if (selectedName && locations.some(location => location.name === selectedName)) {
                        barangaySelect.value = selectedName;
                    }
                    addressRetry.hidden = true;
                    addressStatus.textContent = 'Select your barangay.';
                } catch (error) {
                    showLocationError(error);
                }
            }

            async function loadCities(selectedName = '', selectedBarangay = '') {
                const provinceOption = provinceSelect.selectedOptions[0];
                const provinceCode = provinceOption?.dataset.code;
                citySelect.replaceChildren(new Option('Select your city or municipality', ''));
                barangaySelect.replaceChildren(new Option('Select your barangay', ''));
                if (!provinceCode) {
                    addressStatus.textContent = 'Choose your province to load cities and municipalities.';
                    return;
                }

                addressStatus.textContent = 'Loading cities and municipalities...';
                try {
                    const locations = await fetchLocations('provinces/' + encodeURIComponent(provinceCode) + '/cities-municipalities/');
                    if (provinceSelect.selectedOptions[0]?.dataset.code !== provinceCode) {
                        return;
                    }
                    setLocationOptions(citySelect, locations, 'Select your city or municipality');
                    addressRetry.hidden = true;
                    if (selectedName && locations.some(location => location.name === selectedName)) {
                        citySelect.value = selectedName;
                        await loadBarangays(selectedBarangay);
                    } else {
                        addressStatus.textContent = 'Select your city or municipality.';
                    }
                } catch (error) {
                    showLocationError(error);
                }
            }

            async function loadProvinces() {
                if (provincesLoaded || provincesLoading) {
                    return;
                }
                provincesLoading = true;
                addressStatus.textContent = 'Loading provinces...';
                try {
                    const locations = await fetchLocations('provinces/');
                    setLocationOptions(provinceSelect, locations, 'Select your province');
                    provincesLoaded = true;
                    addressRetry.hidden = true;
                    if (savedAddress.province && locations.some(location => location.name === savedAddress.province)) {
                        provinceSelect.value = savedAddress.province;
                        await loadCities(savedAddress.city, savedAddress.barangay);
                    } else {
                        addressStatus.textContent = 'Select your province.';
                    }
                } catch (error) {
                    showLocationError(error);
                } finally {
                    provincesLoading = false;
                }
            }

            function schoolOtherState() {
                const school = document.getElementById('school');
                const wrapper = document.getElementById('other-school-wrap');
                const other = document.getElementById('school_other');
                const shown = school.value === 'Other';
                wrapper.hidden = !shown;
                other.required = shown && !steps[2].hidden;
            }

            function careerOtherState() {
                const career = document.getElementById('career_goal');
                const wrapper = document.getElementById('other-career-wrap');
                const other = document.getElementById('career_goal_other');
                const shown = career.value === 'Other';
                wrapper.hidden = !shown;
                other.required = shown && !steps[7].hidden;
            }

            function mainGoalOtherState() {
                const selectedGoal = form.querySelector('input[name="main_goal"]:checked');
                const wrapper = document.getElementById('other-main-goal-wrap');
                const other = document.getElementById('main_goal_other');
                const shown = selectedGoal?.value === 'Other';
                wrapper.hidden = !shown;
                other.required = shown && !steps[8].hidden;
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
                    step.querySelectorAll('[data-required-when="other-career"]').forEach(control => {
                        control.required = i === current && document.getElementById('career_goal').value === 'Other';
                    });
                    step.querySelectorAll('[data-required-when="other-main-goal"]').forEach(control => {
                        control.required = i === current && form.querySelector('input[name="main_goal"]:checked')?.value === 'Other';
                    });
                });
                schoolOtherState();
                careerOtherState();
                mainGoalOtherState();
                backButton.hidden = current === 0;
                nextButton.hidden = current === totalSteps - 1;
                finishButton.hidden = current !== totalSteps - 1;
                finishNote.hidden = current !== totalSteps - 1;
                const percent = Math.round(((current + 1) / totalSteps) * 100);
                progressFill.style.width = percent + '%';
                stepCount.textContent = 'Step ' + (current + 1) + ' of ' + totalSteps;
                percentText.textContent = percent + '% complete';
                progressTrack.setAttribute('aria-valuenow', current + 1);
                if (current === 4) {
                    loadProvinces();
                }
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
            document.getElementById('career_goal').addEventListener('change', careerOtherState);
            form.querySelectorAll('input[name="main_goal"]').forEach(control => {
                control.addEventListener('change', mainGoalOtherState);
            });
            provinceSelect.addEventListener('change', () => loadCities());
            citySelect.addEventListener('change', () => loadBarangays());
            addressRetry.addEventListener('click', () => {
                addressRetry.hidden = true;
                if (!provincesLoaded) {
                    loadProvinces();
                } else if (citySelect.value) {
                    loadBarangays();
                } else if (provinceSelect.value) {
                    loadCities();
                }
            });
            backButton.addEventListener('click', () => showStep(Math.max(0, current - 1)));
            nextButton.addEventListener('click', () => {
                if (stepIsValid(steps[current])) showStep(Math.min(totalSteps - 1, current + 1));
            });
            form.addEventListener('submit', function (event) {
                document.getElementById('address').value = [
                    barangaySelect.value,
                    citySelect.value,
                    provinceSelect.value,
                ].filter(Boolean).join(', ');
                for (let i = 0; i < totalSteps; i++) {
                    steps[i].querySelectorAll('[data-required]').forEach(control => { control.required = true; });
                    steps[i].querySelectorAll('[data-required-when="other-school"]').forEach(control => {
                        control.required = document.getElementById('school').value === 'Other';
                    });
                    steps[i].querySelectorAll('[data-required-when="other-career"]').forEach(control => {
                        control.required = document.getElementById('career_goal').value === 'Other';
                    });
                    steps[i].querySelectorAll('[data-required-when="other-main-goal"]').forEach(control => {
                        control.required = form.querySelector('input[name="main_goal"]:checked')?.value === 'Other';
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
