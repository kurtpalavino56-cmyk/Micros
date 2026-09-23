<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Learning Pathways | Upskill Admin</title>
<link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
<style>
    :root{--navy:#0d1b6e;--gold:#dba617;--text:#101b49;--muted:#68758c;--line:#e2e8f3;}
    *{box-sizing:border-box;} body{margin:0;background:#f7f9fc;color:var(--text);font-family:"Segoe UI",Roboto,Arial,sans-serif;}
    .main{padding:36px 32px 60px;min-width:0;}
    .page{max-width:1180px;margin:0 auto;}
    .page-head{display:flex;justify-content:space-between;gap:20px;align-items:end;margin-bottom:28px;flex-wrap:wrap;}
    .eyebrow{color:#b17800;font-weight:800;letter-spacing:.12em;text-transform:uppercase;font-size:11px;margin:0 0 6px;}
    h1{font-size:32px;margin:0 0 7px;} .lead{color:var(--muted);margin:0;font-size:14px;}
    .alert{padding:12px 14px;border-radius:10px;margin-bottom:18px;font-size:13px;}
    .alert.success{background:#ecfdf3;color:#067647;} .alert.error{background:#fff1f2;color:#b42318;}
    .panel{background:#fff;border:1px solid var(--line);border-radius:18px;padding:22px;margin-bottom:20px;box-shadow:0 12px 30px rgba(10,31,110,.06);}
    .panel h2{font-size:19px;margin:0 0 16px;}
    .form-grid{display:grid;grid-template-columns:1fr 2fr;gap:14px;}
    input[type=text]{width:100%;padding:11px 12px;border:1px solid #dce4f0;border-radius:9px;font:inherit;}
    .course-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:8px;}
    .course-option{padding:10px;border:1px solid #e3e8f1;border-radius:9px;font-size:13px;background:#fff;}
    .course-option:has(input:checked){background:#eef2ff;border-color:#cbd5f5;}
    .btn{margin-top:18px;background:var(--navy);color:#fff;border:0;border-radius:999px;padding:11px 18px;font-weight:800;cursor:pointer;}
    .pathway-card{background:#fff;border:1px solid var(--line);border-radius:18px;padding:22px;}
    .pathway-grid{display:grid;grid-template-columns:1fr 2fr auto;gap:12px;align-items:center;}
    .active-label{font-size:13px;white-space:nowrap;}
    .pathway-actions{display:flex;gap:10px;margin-top:18px;}
    @media(max-width:700px){.main{padding:24px 16px 40px;}.form-grid,.pathway-grid{grid-template-columns:1fr;}.panel{padding:18px;}}
</style>
</head>
<body>
@include('components.authenticated-topbar', ['user' => $user])
<div class="layout">
    @include('components.admin-sidebar')
    <main class="main">
        <div class="page">
            <div class="page-head">
                <div><p class="eyebrow">Admin configuration</p><h1>Learning pathways</h1><p class="lead">Group published courses into guided journeys for students.</p></div>
            </div>
            @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert error">{{ $errors->first() }}</div>@endif

            <form method="POST" action="{{ route('admin.pathways.store') }}" class="panel">
                @csrf
                <h2>Create a pathway</h2>
                <div class="form-grid"><input type="text" name="name" required placeholder="Pathway name"><input type="text" name="description" placeholder="Short description"></div>
                <p style="font-weight:700;font-size:13px;margin:18px 0 9px">Assign courses</p>
                <div class="course-grid">
                    @foreach($courses as $course)
                        <label class="course-option"><input type="checkbox" name="course_ids[]" value="{{ $course->id }}"> {{ $course->title }}</label>
                    @endforeach
                </div>
                <button class="btn">Create pathway</button>
            </form>

            <div style="display:grid;gap:18px">
            @forelse($pathways as $pathway)
                <form method="POST" action="{{ route('admin.pathways.update', $pathway->id) }}" class="pathway-card">
                    @csrf @method('PATCH')
                    <div class="pathway-grid">
                        <input type="text" name="name" value="{{ $pathway->name }}" required>
                        <input type="text" name="description" value="{{ $pathway->description }}">
                        <label class="active-label"><input type="checkbox" name="is_active" value="1" @checked($pathway->is_active)> Active</label>
                    </div>
                    <p style="font-weight:700;font-size:13px;margin:18px 0 9px">Courses in this pathway</p>
                    <div class="course-grid">
                        @foreach($courses as $course)
                            <label class="course-option"><input type="checkbox" name="course_ids[]" value="{{ $course->id }}" @checked($pathway->courses->contains($course->id))> {{ $course->title }}</label>
                        @endforeach
                    </div>
                    <div class="pathway-actions"><button class="btn" style="margin-top:0">Save changes</button></div>
                </form>
            @empty
                <div class="panel" style="color:var(--muted)">No pathways yet.</div>
            @endforelse
            </div>
        </div>
    </main>
</div>
@include('components.responsive')
</body>
</html>
