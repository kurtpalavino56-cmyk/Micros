<x-faculty.layout :shell="false" title="Faculty Profile | Upskill" data-faculty-page="faculty-profile-dedicated">
    <x-slot:head><link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    </x-slot:head>
@include('components.authenticated-topbar')

    <div class="page-shell">
        <div class="page-header faculty-page-heading">
            <div>
                <h1 class="faculty-page-title">Faculty Profile</h1>
                <p class="faculty-page-subtitle">Manage your faculty profile and teaching information.</p>
            </div>
        </div>

        <x-flash-toast :message="session('success')" />

        <div class="grid">
            <section class="card profile-card">
                <div class="avatar-row">
                    <div class="avatar">
                        @if (!empty($user->avatar_url))
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name ?? 'Faculty' }}">
                        @else
                            {{ strtoupper(substr(($user->name ?? 'F'), 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <h2>{{ $user->name ?? 'Faculty User' }}</h2>
                        <p class="role">{{ $user->role ?? 'Faculty' }}</p>
                        <p class="meta">{{ $user->email ?? 'faculty@example.com' }}</p>
                    </div>
                </div>

                <div class="info-list">
                    <div class="info-item"><strong>Phone</strong><span>{{ $user->phone ?? 'Not provided' }}</span></div>
                    <div class="info-item"><strong>Location</strong><span>{{ $user->location ?? 'Not provided' }}</span></div>
                    <div class="info-item"><strong>About</strong><span>{{ $user->about ?? 'No summary provided yet.' }}</span></div>
                    <div class="info-item"><strong>Bio</strong><span>{{ $user->bio ?? 'No bio provided yet.' }}</span></div>
                </div>
            </section>

            <section class="card form-card">
                <h3>Update Profile</h3>
                <form method="POST" action="{{ route('faculty.profile.update') }}">
                    @csrf
                    @method('PATCH')

                    <div class="field-row">
                        <label for="name">Full Name</label>
                        <input id="name" name="name" value="{{ old('name', $user->name ?? '') }}" required>
                    </div>

                    <div class="field-row">
                        <label for="email">Email</label>
                        <input class="profile-readonly-field" id="email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}" readonly>
                    </div>

                    <div class="field-row">
                        <label for="phone">Phone</label>
                        <input id="phone" name="phone" value="{{ old('phone', $user->phone ?? '') }}">
                    </div>

                    <div class="field-row">
                        <label for="location">Location</label>
                        <input id="location" name="location" value="{{ old('location', $user->location ?? '') }}">
                    </div>

                    <div class="field-row">
                        <label for="role">Role</label>
                        <input class="profile-readonly-field" id="role" name="role" value="{{ old('role', $user->role ?? 'Faculty') }}" readonly>
                    </div>

                    <div class="field-row">
                        <label for="about">About</label>
                        <textarea id="about" name="about">{{ old('about', $user->about ?? '') }}</textarea>
                    </div>

                    <div class="field-row">
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio">{{ old('bio', $user->bio ?? '') }}</textarea>
                    </div>

                    <div class="btn-row">
                        <button class="btn-save" type="submit">Save Changes</button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    {{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
    @include('components.responsive')
</x-faculty.layout>
