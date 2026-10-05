<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <title>Faculty Profile | Upskill</title>
    <style>
        :root {
            --navy: #12284B;
            --blue: #2563EB;
            --line: #E5E7EB;
            --muted: #6B7280;
            --bg: #F8FAFC;
            --white: #FFFFFF;
            --shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
            --accent: #0F766E;
            --danger: #DC2626;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #f7fefc 0%, #eefcf8 100%);
            color: var(--navy);
        }

        .page-shell {
            max-width: 1180px;
            margin: 0 auto;
            padding: 24px 20px 48px;
            margin-top: calc(var(--topbar-h) + 20px);
        }

        .search-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.13);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 999px;
            padding: 8px 14px;
        }

        .search-wrap svg {
            width: 18px;
            height: 18px;
            color: rgba(255,255,255,0.72);
            flex-shrink: 0;
        }

        .search-wrap input {
            background: transparent;
            border: none;
            outline: none;
            color: var(--white);
            width: 180px;
            font-size: 0.88rem;
        }

        .search-wrap input::placeholder {
            color: rgba(255,255,255,0.68);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            gap: 16px;
        }

        .page-header h1 {
            margin: 0 0 6px;
            font-size: 28px;
            color: var(--navy);
        }

        .page-header p {
            margin: 0;
            color: var(--muted);
        }

        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            border: none;
            border-radius: 999px;
            padding: 10px 16px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-back {
            background: #ECFEF9;
            color: var(--accent);
        }

        .btn-logout {
            background: var(--danger);
            color: white;
        }

        .grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 24px;
        }

        .card {
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: var(--shadow);
            padding: 24px;
        }

        .profile-card {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .avatar-row {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .avatar {
            width: 78px;
            height: 78px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), #14B8A6);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 700;
            overflow: hidden;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-row h2 {
            margin: 0 0 4px;
            font-size: 22px;
        }

        .role {
            margin: 0;
            color: var(--accent);
            font-weight: 700;
        }

        .meta {
            margin: 4px 0 0;
            color: var(--muted);
        }

        .info-list {
            display: grid;
            gap: 10px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--line);
        }

        .info-item strong {
            color: var(--muted);
            font-weight: 600;
        }

        .form-card h3 {
            margin-top: 0;
            margin-bottom: 16px;
            color: var(--navy);
        }

        form {
            display: grid;
            gap: 12px;
        }

        .field-row {
            display: grid;
            gap: 6px;
        }

        label {
            font-weight: 700;
            color: var(--navy);
            font-size: 14px;
        }

        input, textarea {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 14px;
            background: #FCFFFE;
        }

        textarea {
            min-height: 92px;
            resize: vertical;
        }

        .btn-row {
            display: flex;
            justify-content: flex-end;
            margin-top: 8px;
        }

        .btn-save {
            border: none;
            background: linear-gradient(135deg, var(--accent), #14B8A6);
            color: white;
            padding: 11px 17px;
            border-radius: 999px;
            font-weight: 700;
            cursor: pointer;
        }

        .alert {
            padding: 10px 12px;
            border-radius: 12px;
            background: #ECFDF3;
            color: #065F46;
            border: 1px solid #A7F3D0;
            margin-bottom: 16px;
        }

        @media (max-width: 900px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    @include('components.authenticated-topbar')

    <div class="page-shell">
        <div class="page-header faculty-page-heading">
            <div>
                <h1 class="faculty-page-title">Faculty Profile</h1>
                <p class="faculty-page-subtitle">Manage your faculty profile and teaching information.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert">{{ session('success') }}</div>
        @endif

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
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}" required>
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
                        <input id="role" name="role" value="{{ old('role', $user->role ?? 'Faculty') }}">
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
</body>
</html>

