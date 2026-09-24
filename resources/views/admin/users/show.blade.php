{{-- resources/views/admin/User_Detail.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $u->name }} | User Details</title>

    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">

    <style>
        :root {
            --navy: #13176b;
            --blue: #0b4ea2;
            --blue-soft: #eef1fb;
            --gold: #dba617;
            --muted: #64748b;
            --line: #e8ebf2;
            --surface: #f5f7fb;
            --white: #ffffff;
            --text: #1e293b;
            --shadow: 0 1px 3px rgba(19, 23, 107, .05);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Inter", "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: var(--text);
            background: var(--surface);
            -webkit-font-smoothing: antialiased;
        }

        a { text-decoration: none; color: inherit; }

        /* ---------- Shared layout (topbar + sidebar offset) ---------- */
        .layout {
            display: block;
            min-height: calc(100vh - 66px);
        }

        .main.user-detail-wrap {
            margin-left: 238px !important;
            width: calc(100% - 238px) !important;
            min-width: 0;
            padding: 28px 32px 56px;
        }

        /* Single centered column, like the reference */
        .detail-column {
            width: 100%;
            max-width: 760px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            align-self: flex-start;
            color: var(--muted);
            font-size: 13px;
            font-weight: 600;
            transition: color .2s ease;
        }
        .back-link:hover { color: var(--navy); }

        /* ---------- Cards ---------- */
        .card {
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 20px 22px;
        }

        /* ---------- Profile header ---------- */
        .profile-top {
            display: flex;
            align-items: center;
            gap: 16px;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--line);
        }

        .avatar {
            width: 68px;
            height: 68px;
            flex: 0 0 68px;
            border-radius: 50%;
            background: var(--blue-soft) center / cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--blue);
            overflow: hidden;
        }
        .avatar svg { width: 28px; height: 28px; }

        .profile-main { min-width: 0; }

        .profile-main h1 {
            margin: 0;
            color: var(--navy);
            font-size: 20px;
            font-weight: 700;
            line-height: 1.3;
            overflow-wrap: anywhere;
        }

        .profile-role {
            margin-top: 3px;
            color: var(--muted);
            font-size: 13px;
        }

        .status-pill {
            display: inline-block;
            margin-top: 6px;
            padding: 2px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
        }
        .status-pill.status-on  { color: #166534; background: #e3f6ea; }
        .status-pill.status-off { color: #64748b; background: #eef1f5; }

        .profile-meta {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding-top: 16px;
        }

        .meta-row {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #475569;
            font-size: 13px;
            min-width: 0;
            overflow-wrap: anywhere;
        }
        .meta-row svg {
            width: 16px;
            height: 16px;
            flex: 0 0 16px;
            color: #64748b;
        }

        /* ---------- Section heading ---------- */
        .section-title {
            display: flex;
            align-items: center;
            gap: 9px;
            margin: 0;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--line);
            color: var(--navy);
            font-size: 14px;
            font-weight: 700;
        }
        .section-title svg {
            width: 16px;
            height: 16px;
            color: var(--blue);
        }

        /* ---------- Fields ---------- */
        .field-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px 24px;
            padding-top: 16px;
        }

        .field-stack {
            display: flex;
            flex-direction: column;
            gap: 16px;
            padding-top: 16px;
        }

        .field { min-width: 0; }

        .field .label {
            display: block;
            margin-bottom: 4px;
            color: var(--muted);
            font-size: 11.5px;
            font-weight: 500;
        }

        .field .value {
            color: var(--navy);
            font-size: 14px;
            font-weight: 700;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }
        .field .value.empty { color: #94a3b8; font-weight: 500; }
        .field .value.prose { font-weight: 500; color: var(--text); white-space: pre-wrap; }

        /* ---------- Courses rows ---------- */
        .stat-list { padding-top: 4px; }

        .stat-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 0;
            border-bottom: 1px solid var(--line);
            font-size: 13px;
            color: #475569;
        }
        .stat-row:last-child { border-bottom: 0; padding-bottom: 2px; }
        .stat-row strong { color: var(--navy); font-size: 14px; font-weight: 700; }

        /* ---------- Chips ---------- */
        .chips { display: flex; flex-wrap: wrap; gap: 6px; }

        .chip {
            padding: 4px 10px;
            border-radius: 6px;
            background: var(--blue-soft);
            color: var(--navy);
            font-size: 12px;
            font-weight: 600;
        }
        .chip.want { background: #fff6dd; color: #8a6400; }

        .empty-note { color: #94a3b8; font-size: 13px; font-style: italic; }

        /* ---------- Responsive ---------- */
        @media (max-width: 1024px) {
            .main.user-detail-wrap {
                margin-left: 0 !important;
                width: 100% !important;
                padding: 24px;
            }
        }

        @media (max-width: 560px) {
            .main.user-detail-wrap { padding: 18px 12px 40px; }
            .card { padding: 18px 16px; }
            .field-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>
    @include('components.authenticated-topbar', ['user' => auth()->user()])

    <div class="layout admin-layout">

        {{-- SHARED ADMIN SIDEBAR --}}
        @include('components.admin-sidebar')

        {{-- MAIN CONTENT --}}
        <main class="main user-detail-wrap">
            <div class="detail-column">

                <a class="back-link" href="{{ route('admin.usermanagement') }}">
                    <span aria-hidden="true">&larr;</span> Back to User Management
                </a>

                {{-- Profile summary --}}
                <section class="card">
                    <div class="profile-top">
                        <div class="avatar" @if($u->avatar_url) style="background-image:url('{{ $u->avatar_url }}')" @endif>
                            @unless($u->avatar_url)
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
                            @endunless
                        </div>

                        <div class="profile-main">
                            <h1>{{ $u->name }}</h1>
                            <div class="profile-role">{{ $u->role }}</div>
                            <span class="status-pill {{ $u->is_active ? 'status-on' : 'status-off' }}">
                                {{ $u->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    <div class="profile-meta">
                        <div class="meta-row">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                            <span>{{ $u->email }}</span>
                        </div>
                        <div class="meta-row">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M6 16c.6-1.4 1.7-2 3-2s2.4.6 3 2M15 10h3M15 13h3"/></svg>
                            <span>{{ $u->student_id ?? '—' }}</span>
                        </div>
                    </div>
                </section>

                {{-- Personal Information --}}
                <section class="card">
                    <h2 class="section-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
                        Personal Information
                    </h2>

                    <div class="field-grid">
                        <div class="field">
                            <span class="label">Username</span>
                            <div class="value {{ empty($u->username) ? 'empty' : '' }}">{{ $u->username ?? '—' }}</div>
                        </div>

                        <div class="field">
                            <span class="label">Phone Number</span>
                            <div class="value {{ empty($u->phone) ? 'empty' : '' }}">{{ $u->phone ?? '—' }}</div>
                        </div>

                        <div class="field">
                            <span class="label">Gender</span>
                            <div class="value {{ empty($u->gender) ? 'empty' : '' }}">{{ $u->gender ?? '—' }}</div>
                        </div>

                        <div class="field">
                            <span class="label">Date of Birth</span>
                            <div class="value {{ empty($u->date_of_birth) ? 'empty' : '' }}">{{ $u->date_of_birth ? \Illuminate\Support\Carbon::parse($u->date_of_birth)->format('M d, Y') : '—' }}</div>
                        </div>

                        <div class="field">
                            <span class="label">Date Joined</span>
                            <div class="value {{ empty($u->joined) ? 'empty' : '' }}">{{ $u->joined ?? '—' }}</div>
                        </div>
                    </div>
                </section>

                {{-- Education Information --}}
                <section class="card">
                    <h2 class="section-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m2 9 10-5 10 5-10 5z"/><path d="M6 11.5V16c0 1.5 2.7 3 6 3s6-1.5 6-3v-4.5"/></svg>
                        Education Information
                    </h2>

                    <div class="field-stack">
                        <div class="field">
                            <span class="label">School / Institution</span>
                            <div class="value {{ empty($u->school) ? 'empty' : '' }}">{{ $u->school ?? '—' }}</div>
                        </div>

                        <div class="field">
                            <span class="label">Program / Education</span>
                            <div class="value {{ empty($u->education) ? 'empty' : '' }}">{{ $u->education ?? '—' }}</div>
                        </div>

                        <div class="field">
                            <span class="label">Location</span>
                            <div class="value {{ empty($u->location) ? 'empty' : '' }}">{{ $u->location ?? '—' }}</div>
                        </div>
                    </div>
                </section>

                {{-- Courses --}}
                <section class="card">
                    <h2 class="section-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2 5h6a4 4 0 0 1 4 4v11a3 3 0 0 0-3-3H2z"/><path d="M22 5h-6a4 4 0 0 0-4 4v11a3 3 0 0 1 3-3h7z"/></svg>
                        Courses
                    </h2>

                    <div class="stat-list">
                        @if($u->role === 'Faculty')
                            <div class="stat-row">
                                <span>Courses Created</span>
                                <strong>{{ $u->courses_created }}</strong>
                            </div>
                        @else
                            <div class="stat-row">
                                <span>Courses Enrolled</span>
                                <strong>{{ $u->enrollments }}</strong>
                            </div>
                        @endif
                    </div>
                </section>

                {{-- Additional Information --}}
                <section class="card">
                    <h2 class="section-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>
                        Additional Information
                    </h2>

                    <div class="field-stack">
                        @if($u->bio)
                            <div class="field">
                                <span class="label">Bio</span>
                                <div class="value prose">{{ $u->bio }}</div>
                            </div>
                        @endif

                        @if(!empty($u->skills_have))
                            <div class="field">
                                <span class="label">Skills They Have</span>
                                <div class="chips">
                                    @foreach($u->skills_have as $s)
                                        <span class="chip">{{ $s }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(!empty($u->skills_want))
                            <div class="field">
                                <span class="label">Skills They Want to Learn</span>
                                <div class="chips">
                                    @foreach($u->skills_want as $s)
                                        <span class="chip want">{{ $s }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(!$u->bio && empty($u->skills_have) && empty($u->skills_want))
                            <div class="empty-note">No additional information provided.</div>
                        @endif
                    </div>
                </section>

            </div>
        </main>
    </div>

    @include('components.responsive')
</body>
</html>