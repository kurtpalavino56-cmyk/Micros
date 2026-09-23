<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Enrolled Students | Upskill</title>
<link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
<link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">
<style>
    :root{--navy:#13176b;--gold:#dba617;--muted:#6b7280;--line:#e5e7eb;--green:#15803d;--shadow:0 10px 25px rgba(19,23,107,.08);}
    *{box-sizing:border-box;}
    body{font-family:"Segoe UI",Roboto,Helvetica,Arial,sans-serif;color:var(--navy);margin:0;background:#f8faff;}
    a{text-decoration:none;color:inherit;} button{font-family:inherit;cursor:pointer;}
    .main{padding:28px 32px 48px;min-width:0;}
    .page-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;margin-bottom:22px;}
    .page-head h2{font-size:28px;margin:0 0 6px;color:var(--navy);}
    .page-head p{margin:0;color:var(--muted);font-size:14px;}
    .btn-outline{background:#fff;border:1.5px solid var(--navy);color:var(--navy);font-weight:800;padding:10px 20px;border-radius:12px;font-size:13px;}
    .btn-outline:hover{background:var(--navy);color:#fff;}
    .students-list{display:grid;gap:14px;max-width:1100px;}
    .student-card{width:100%;text-align:left;border:1px solid var(--line);border-radius:16px;background:#fff;box-shadow:var(--shadow);padding:18px 20px;display:flex;align-items:center;gap:16px;transition:transform .15s ease,box-shadow .15s ease,border-color .15s ease;}
    .student-card:hover{transform:translateY(-1px);box-shadow:0 14px 30px rgba(19,23,107,.12);border-color:#cbd3e8;}
    .avatar{width:50px;height:50px;border-radius:50%;background:#eef1fb;display:flex;align-items:center;justify-content:center;font-weight:800;color:var(--navy);font-size:19px;overflow:hidden;background-size:cover;background-position:center;flex:0 0 50px;}
    .student-main{min-width:0;flex:1;}
    .student-name{font-size:16px;font-weight:800;color:var(--navy);margin:0 0 3px;}
    .student-id{font-size:12.5px;color:var(--muted);margin:0;}
    .student-summary{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:7px;font-size:12px;color:#5f6b85;}
    .summary-pill{background:#eef2ff;color:#24346f;border-radius:999px;padding:4px 9px;font-weight:700;}
    .summary-pill.done{background:#e8f7ef;color:var(--green);}
    .view-student{color:var(--navy);font-weight:800;font-size:13px;white-space:nowrap;}
    .view-student span{margin-left:4px;}
    .empty-state{background:#fff;border:1px dashed var(--line);border-radius:16px;padding:38px;text-align:center;color:var(--muted);font-size:14px;}
    .modal{position:fixed;inset:0;background:rgba(8,15,55,.48);display:none;align-items:center;justify-content:center;padding:20px;z-index:1200;}
    .modal.is-open{display:flex;}
    .modal-card{width:min(720px,100%);max-height:min(720px,90vh);overflow:auto;background:#fff;border-radius:20px;box-shadow:0 24px 60px rgba(8,15,55,.28);}
    .modal-head{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:20px 22px;border-bottom:1px solid var(--line);position:sticky;top:0;background:#fff;z-index:1;}
    .modal-head h3{margin:0;font-size:20px;color:var(--navy);}
    .modal-head p{margin:4px 0 0;color:var(--muted);font-size:12px;}
    .modal-close{width:36px;height:36px;border:0;border-radius:10px;background:#f1f4fa;color:var(--navy);font-size:20px;line-height:1;}
    .modal-body{padding:20px 22px 24px;}
    .course-row{padding:15px 0;border-bottom:1px solid var(--line);}
    .course-row:last-child{border-bottom:0;}
    .course-top{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:9px;}
    .course-title{font-weight:800;font-size:14px;color:var(--navy);min-width:0;}
    .course-percent{font-weight:800;font-size:12px;color:var(--navy);white-space:nowrap;}
    .track{height:8px;border-radius:999px;background:#e8ecf7;overflow:hidden;}
    .fill{height:100%;border-radius:999px;background:linear-gradient(90deg,var(--navy),#3b41c8);}
    .fill.done{background:linear-gradient(90deg,var(--green),#34d399);}
    .course-status{display:inline-block;margin-top:7px;background:#eef2ff;color:#24346f;font-size:11px;font-weight:800;padding:4px 9px;border-radius:999px;}
    .course-status.done{background:#e8f7ef;color:var(--green);}
    @media(max-width:700px){.main{padding:22px 16px 36px;}.student-card{padding:16px;}.view-student{font-size:0;}.view-student span{font-size:13px;}.modal{padding:12px;}}
</style>
</head>
<body>
@include('components.authenticated-topbar')
<div class="layout faculty-sidebar-layout">
    @include('components.faculty-sidebar')
    <main class="main">
        @include('components.breadcrumbs', ['breadcrumbClass' => 'faculty-breadcrumbs', 'items' => [
            ['label' => 'My Courses', 'url' => route('faculty.courses')],
            ['label' => 'Enrolled Students'],
        ]])
        <div class="page-head">
            <div><h2>Enrolled Students</h2><p>View students enrolled in your courses and check their progress.</p></div>
            <a href="{{ route('faculty.courses') }}" class="btn-outline">My Courses</a>
        </div>

        <div class="students-list">
        @forelse ($students as $student)
            @php
                $courseCount = $student->courses->count();
                $completedCount = $student->courses->where('completed', true)->count();
                $modalId = 'student-details-' . $loop->index;
            @endphp
            <button type="button" class="student-card" data-student-modal="{{ $modalId }}" aria-haspopup="dialog">
                <div class="avatar" @if($student->avatar_url) style="background-image:url('{{ $student->avatar_url }}')" @endif>
                    @unless($student->avatar_url){{ strtoupper(substr($student->name ?? 'S', 0, 1)) }}@endunless
                </div>
                <div class="student-main">
                    <p class="student-name">{{ $student->name }}</p>
                    <p class="student-id">{{ $student->student_id ?: 'Student ID not provided' }}</p>
                    <div class="student-summary">
                        <span class="summary-pill">{{ $courseCount }} {{ $courseCount === 1 ? 'course' : 'courses' }}</span>
                        @if($completedCount) <span class="summary-pill done">{{ $completedCount }} completed</span> @endif
                    </div>
                </div>
                <span class="view-student">View courses <span>→</span></span>
            </button>
        @empty
            <div class="empty-state">No students are enrolled in your courses yet.</div>
        @endforelse
        </div>

        @foreach ($students as $student)
            @php $modalId = 'student-details-' . $loop->index; @endphp
            <div class="modal" id="{{ $modalId }}" role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}-title">
                <div class="modal-card">
                    <div class="modal-head">
                        <div><h3 id="{{ $modalId }}-title">{{ $student->name }}</h3><p>{{ $student->student_id ?: 'Student ID not provided' }}</p></div>
                        <button type="button" class="modal-close" data-close-student-modal aria-label="Close">&times;</button>
                    </div>
                    <div class="modal-body">
                        @foreach ($student->courses as $course)
                            <div class="course-row">
                                <div class="course-top"><span class="course-title">{{ $course->title }}</span><span class="course-percent">{{ $course->percent }}%</span></div>
                                <div class="track"><div class="fill {{ $course->completed ? 'done' : '' }}" style="width:{{ min(100, max(0, $course->percent)) }}%"></div></div>
                                <span class="course-status {{ $course->completed ? 'done' : '' }}">{{ $course->completed ? 'Completed' : 'In progress' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </main>
</div>
@include('components.responsive')
<script>
(function(){
    var openModal = function(id){ var m=document.getElementById(id); if(!m)return; m.classList.add('is-open'); document.body.style.overflow='hidden'; };
    var closeAll = function(){ document.querySelectorAll('.modal.is-open').forEach(function(m){m.classList.remove('is-open');}); document.body.style.overflow=''; };
    document.querySelectorAll('[data-student-modal]').forEach(function(btn){ btn.addEventListener('click',function(){openModal(btn.getAttribute('data-student-modal'));}); });
    document.querySelectorAll('[data-close-student-modal]').forEach(function(btn){ btn.addEventListener('click',closeAll); });
    document.querySelectorAll('.modal').forEach(function(m){ m.addEventListener('click',function(e){if(e.target===m)closeAll();}); });
    document.addEventListener('keydown',function(e){if(e.key==='Escape')closeAll();});
})();
</script>
</body>
</html>
