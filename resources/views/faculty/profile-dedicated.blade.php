<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

        .topbar {
            position: fixed;
            inset: 0 0 auto 0;
            height: var(--topbar-h);
            width: 100%;
            padding: 0 20px;
            background: var(--navy);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            z-index: 200;
            box-shadow: 0 2px 8px rgba(0,0,0,0.16);
        }

        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-transform: uppercase;
            color: var(--white);
            font-weight: 800;
            letter-spacing: 2px;
            text-decoration: none;
            font-size: 0.95rem;
        }

        .brand-logo {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--navy);
            font-weight: 800;
            font-size: 0.9rem;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
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

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar-link,
        .topbar-button {
            border: 1px solid rgba(255,255,255,0.22);
            background: rgba(255,255,255,0.12);
            color: var(--white);
            border-radius: 999px;
            padding: 9px 14px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .topbar-button {
            background: rgba(255,255,255,0.16);
            border-color: rgba(255,255,255,0.22);
        }

        .topbar-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.22);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 700;
            text-decoration: none;
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
        <div class="page-header">
            <div>
                <h1>Faculty Profile</h1>
                <p>Manage your faculty profile and teaching information.</p>
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

