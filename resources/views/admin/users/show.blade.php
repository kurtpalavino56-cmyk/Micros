{{-- resources/views/admin/User_Detail.blade.php --}}
<x-admin.layout title="{{ $u->name }} | User Details" page="admin-users-show">
    <x-slot:head>
        <link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
            <link rel="apple-touch-icon" href="{{ asset('Images/PSU-Logo.png') }}">
    </x-slot:head>
<div class="layout admin-layout">

        {{-- SHARED ADMIN SIDEBAR --}}
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
</x-admin.layout>