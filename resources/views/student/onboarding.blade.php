{{-- chrome=auth: this page is a signed-in flow with its own student topbar;
     the layout must skip the public navbar + footer. --}}
@extends('layouts.app', ['chrome' => 'auth'])

@section('title', 'Welcome to UpSkill PSU')

@section('content')
@include('components.student-navigation')
<div class="onboarding-shell">
    <div class="onboarding-intro">
        <p class="eyebrow">Your learning journey starts here</p>
        <h1>Tell us where you want to go.</h1>
        <p>Complete your profile so we can personalize course recommendations and build a pathway around your goals.</p>
    </div>

    @if ($errors->any())
        <div class="onboarding-errors">Please review the highlighted fields and try again.</div>
    @endif

    <form method="POST" action="{{ route('profile.complete') }}" class="onboarding-card">
        @csrf
        <section class="onboarding-section">
            <div><span class="step">01</span><h2>About you</h2><p>Basic information helps us tailor your experience.</p></div>
            <div class="onboarding-grid">
                <label>Date of birth<input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" max="{{ now()->toDateString() }}" required></label>
                <label>Gender<select name="gender" required><option value="">Select…</option>@foreach(['Male','Female','Prefer not to say'] as $value)<option value="{{ $value }}" @selected(old('gender') === $value)>{{ $value }}</option>@endforeach</select></label>
                <label>Highest level of education<select name="education" required><option value="">Select…</option>@foreach(['Senior High School','Vocational / Technical','Undergraduate (College)',"Bachelor's Degree","Master's Degree",'Doctorate / PhD'] as $value)<option value="{{ $value }}" @selected(old('education') === $value)>{{ $value }}</option>@endforeach</select></label>
                <label>School or university<input type="text" name="school" value="{{ old('school') }}" required></label>
                <label class="full">Address<textarea name="address" rows="2" required>{{ old('address') }}</textarea></label>
            </div>
        </section>

        <section class="onboarding-section">
            <div><span class="step">02</span><h2>Your interests</h2><p>Select skills you already have and subjects you want to explore.</p></div>
            <div class="onboarding-grid">
                <label>Skills you have<input type="text" name="skills_have" value="{{ old('skills_have') }}" placeholder="e.g. communication, Excel, design"></label>
                <label>Subjects you are interested in<input type="text" name="skills_want" value="{{ old('skills_want') }}" placeholder="e.g. Python, cybersecurity, marketing"></label>
                <label class="full">What do you wish to accomplish?<textarea name="bio" rows="3" placeholder="Tell us what success looks like for you.">{{ old('bio') }}</textarea></label>
            </div>
        </section>

        <section class="onboarding-section">
            <div><span class="step">03</span><h2>Choose your direction</h2><p>Your choice is based on pathways created by the PSU administrators.</p></div>
            <div class="pathway-options">
                @forelse($pathways as $pathway)
                    <label class="pathway-option"><input type="radio" name="pathway_id" value="{{ $pathway->id }}" @checked((string) old('pathway_id') === (string) $pathway->id) required><span><strong>{{ $pathway->name }}</strong><small>{{ $pathway->description ?: 'A guided sequence of courses to help you reach your next goal.' }}</small><em>{{ $pathway->courses->count() }} courses</em></span></label>
                @empty
                    <p class="empty-pathways">Pathways are being prepared by the administrators. You can still complete your profile, then choose a pathway later.</p>
                @endforelse
            </div>
            <label class="career-field">What do you want to become?<input type="text" name="career_goal" value="{{ old('career_goal') }}" placeholder="e.g. Software Engineer" required></label>
        </section>

        <div class="onboarding-actions"><button type="submit">Save profile and continue</button></div>
    </form>
</div>
<style>
    .onboarding-shell{max-width:1040px;margin:0 auto;padding:56px 22px 80px;color:#111a46}.onboarding-intro{max-width:650px;margin-bottom:28px}.eyebrow{color:#b17800;font-weight:800;letter-spacing:.14em;text-transform:uppercase;font-size:11px}.onboarding-intro h1{font-size:clamp(32px,5vw,54px);line-height:1.05;margin:10px 0 14px;color:#071550}.onboarding-intro>p:not(.eyebrow){color:#637086;font-size:16px;line-height:1.7}.onboarding-card{background:#fff;border:1px solid #e2e8f3;border-radius:24px;box-shadow:0 16px 45px rgba(10,31,110,.08);overflow:hidden}.onboarding-section{display:grid;grid-template-columns:230px 1fr;gap:28px;padding:30px;border-bottom:1px solid #edf1f7}.onboarding-section h2{margin:4px 0 6px;color:#071550;font-size:21px}.onboarding-section p{margin:0;color:#738098;font-size:13px;line-height:1.55}.step{display:inline-grid;place-items:center;width:36px;height:36px;border-radius:12px;background:#eef3ff;color:#0a1f6e;font-weight:800;font-size:12px}.onboarding-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.onboarding-grid label,.career-field{display:grid;gap:7px;color:#43516d;font-weight:700;font-size:12px}.onboarding-grid label.full{grid-column:1/-1}.onboarding-grid input,.onboarding-grid select,.onboarding-grid textarea,.career-field input{width:100%;border:1px solid #dce4f0;border-radius:11px;padding:11px 12px;background:#fbfcfe;color:#17244d;font:inherit;font-size:14px}.pathway-options{display:grid;gap:10px}.pathway-option{display:flex;gap:12px;align-items:flex-start;padding:14px;border:1px solid #dce4f0;border-radius:14px;cursor:pointer;transition:.18s}.pathway-option:has(input:checked){border-color:#e8a800;background:#fffaf0;box-shadow:0 0 0 2px rgba(232,168,0,.12)}.pathway-option input{margin-top:3px;accent-color:#0a1f6e}.pathway-option span{display:grid;gap:4px}.pathway-option strong{color:#071550}.pathway-option small{color:#637086;line-height:1.45}.pathway-option em{font-size:11px;color:#b17800;font-style:normal;font-weight:800}.career-field{margin-top:16px}.onboarding-actions{padding:24px 30px;text-align:right}.onboarding-actions button{border:0;border-radius:999px;padding:13px 22px;background:#0a1f6e;color:#fff;font:800 14px inherit;cursor:pointer}.onboarding-actions button:hover{background:#071550}.onboarding-errors{margin-bottom:16px;padding:12px 14px;border-radius:12px;background:#fff1f2;color:#b42318;font-size:13px;font-weight:700}.empty-pathways{padding:14px;border-radius:12px;background:#f6f8fc}@media(max-width:720px){.onboarding-section{grid-template-columns:1fr;gap:16px;padding:22px}.onboarding-grid{grid-template-columns:1fr}.onboarding-grid label.full{grid-column:auto}.onboarding-actions{padding:20px 22px}.onboarding-actions button{width:100%}}
</style>
@endsection
