{{--
    resources/views/admin/User_Detail.blade.php
    Full read-only profile of a single user (student or faculty),
    opened by clicking a row in Admin › User Management.
    Expects: $u (object) — see AdminController::showUser().
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $u->name }} | User Details</title>
    {{-- Browser tab icon (favicon) --}}
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">
<style>
    :root{--navy:#13176b;--gold:#dba617;--muted:#6b7280;--line:#e5e7eb;--shadow:0 10px 25px rgba(19,23,107,.08);}
    *{box-sizing:border-box;}
    body{font-family:"Segoe UI",Roboto,Helvetica,Arial,sans-serif;color:var(--navy);margin:0;background:#f5f7ff;}
    a{text-decoration:none;color:inherit;}
    .topbar{position:sticky;top:0;height:58px;background:var(--navy);display:flex;align-items:center;justify-content:space-between;padding:0 22px;z-index:200;box-shadow:0 2px 8px rgba(0,0,0,.25);}
    .topbar-brand{display:flex;align-items:center;gap:10px;color:#fff;font-size:1.05rem;font-weight:800;letter-spacing:2px;text-transform:uppercase;}
    .brand-logo{width:34px;height:34px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;}
    .brand-logo img{width:100%;height:100%;object-fit:contain;}
    .wrap{max-width:880px;margin:34px auto;padding:0 20px 60px;}
    .back{display:inline-block;margin-bottom:18px;font-weight:700;color:var(--navy);}
    .back:hover{color:var(--gold);}
    .card{background:#fff;border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow);padding:26px 30px;}
    .head{display:flex;align-items:center;gap:18px;border-bottom:1px solid var(--line);padding-bottom:20px;margin-bottom:20px;}
    .avatar{width:74px;height:74px;border-radius:50%;background:#eef1fb;display:flex;align-items:center;justify-content:center;font-size:30px;font-weight:800;color:var(--navy);overflow:hidden;background-size:cover;background-position:center;flex-shrink:0;}
    .head h1{margin:0;font-size:24px;}
    .head .sub{color:var(--muted);font-size:13.5px;margin-top:4px;}
    .role-pill{display:inline-block;background:#eef1fb;color:var(--navy);font-weight:800;font-size:12px;padding:4px 14px;border-radius:999px;margin-top:8px;}
    .status-pill{font-size:12px;font-weight:800;padding:4px 14px;border-radius:999px;margin-left:8px;}
    .status-on{background:#e8f7ef;color:#15803d;}
    .status-off{background:#f1f5f9;color:#475569;}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:16px 32px;}
    .field label{display:block;font-size:11.5px;font-weight:800;letter-spacing:.4px;text-transform:uppercase;color:var(--muted);margin-bottom:4px;}
    .field .val{font-size:14.5px;font-weight:600;color:var(--navy);}
    .field.full{grid-column:1 / -1;}
    .chips{display:flex;flex-wrap:wrap;gap:7px;}
    .chip{padding:5px 12px;border-radius:999px;font-size:12.5px;font-weight:600;background:#e9ebfb;color:var(--navy);border:1px solid #c9cdf0;}
    .chip.want{background:#fdf3d7;color:#c4930f;border-color:#f0dc9a;}
    .muted{color:var(--muted);font-style:italic;font-size:13px;}
    @media(max-width:640px){.grid{grid-template-columns:1fr;}}
</style>
</head>
<body>
@include('components.authenticated-topbar', ['user' => auth()->user()])
<div class="wrap">
    <a class="back" href="{{ route('admin.usermanagement') }}">&larr; Back to User Management</a>

    <div class="card">
        <div class="head">
            <div class="avatar" @if($u->avatar_url) style="background-image:url('{{ $u->avatar_url }}')" @endif>
                @unless($u->avatar_url){{ strtoupper(substr($u->name ?? 'U', 0, 1)) }}@endunless
            </div>
            <div>
                <h1>{{ $u->name }}</h1>
                <div class="sub">{{ $u->email }}</div>
                <span class="role-pill">{{ $u->role }}</span>
                <span class="status-pill {{ $u->is_active ? 'status-on' : 'status-off' }}">{{ $u->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
        </div>

        <div class="grid">
            <div class="field"><label>{{ $u->role === 'Faculty' ? 'Faculty Code' : 'Student ID' }}</label><div class="val">{{ $u->student_id ?? '—' }}</div></div>
            <div class="field"><label>Username</label><div class="val">{{ $u->username ?? '—' }}</div></div>
            <div class="field"><label>Joined</label><div class="val">{{ $u->joined ?? '—' }}</div></div>
            <div class="field"><label>Phone</label><div class="val">{{ $u->phone ?? '—' }}</div></div>
            <div class="field"><label>Gender</label><div class="val">{{ $u->gender ?? '—' }}</div></div>
            <div class="field"><label>Date of Birth</label><div class="val">{{ $u->date_of_birth ? \Illuminate\Support\Carbon::parse($u->date_of_birth)->format('M d, Y') : '—' }}</div></div>
            <div class="field"><label>Education</label><div class="val">{{ $u->education ?? '—' }}</div></div>
            <div class="field"><label>School Attended</label><div class="val">{{ $u->school ?? '—' }}</div></div>
            <div class="field"><label>Location</label><div class="val">{{ $u->location ?? '—' }}</div></div>
            @if($u->role === 'Faculty')
                <div class="field"><label>Courses Created</label><div class="val">{{ $u->courses_created }}</div></div>
            @else
                <div class="field"><label>Courses Enrolled</label><div class="val">{{ $u->enrollments }}</div></div>
            @endif
            @if($u->bio)
                <div class="field full"><label>Bio</label><div class="val" style="font-weight:500;">{{ $u->bio }}</div></div>
            @endif
            @if(!empty($u->skills_have))
                <div class="field full"><label>Skills They Have</label>
                    <div class="chips">@foreach($u->skills_have as $s)<span class="chip">{{ $s }}</span>@endforeach</div>
                </div>
            @endif
            @if(!empty($u->skills_want))
                <div class="field full"><label>Skills They Want to Learn</label>
                    <div class="chips">@foreach($u->skills_want as $s)<span class="chip want">{{ $s }}</span>@endforeach</div>
                </div>
            @endif
        </div>
    </div>
</div>


{{-- Shared Admin sidebar styling (floating card + section pills) --}}
    @include('components.admin-sidebar')
    
        {{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
    @include('components.responsive')
</body>
</html>