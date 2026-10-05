<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Credit Recognition Evidence | UpSkill</title>
<style>
body{margin:0;background:#f2f4f8;color:#17233c;font:14px/1.55 Arial,sans-serif}.report{max-width:980px;margin:28px auto;padding:34px;background:#fff;box-shadow:0 8px 30px #17233c12}.toolbar{display:flex;justify-content:space-between;gap:12px;margin-bottom:24px}.toolbar button{border:0;border-radius:8px;background:#14236b;color:#fff;padding:10px 16px;font-weight:700}h1,h2{color:#14236b}h1{margin:0}.subtitle{color:#667085}.summary{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin:20px 0}.box{padding:12px;background:#f5f7fb;border-radius:8px}.box strong{display:block;color:#14236b}.course{margin:16px 0;padding:16px;border:1px solid #dfe5ef;border-radius:10px;break-inside:avoid}.meta{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}.small{color:#667085;font-size:12px}.notice{padding:12px;background:#fff7dc;border-left:4px solid #dba617;margin-top:20px}@media print{body{background:white}.report{margin:0;max-width:none;box-shadow:none;padding:12mm}.toolbar{display:none}.course{break-inside:avoid}}@media(max-width:650px){.summary,.meta{grid-template-columns:1fr}}
</style></head><body><main class="report">
<div class="toolbar"><span>Generated {{ $generated_at->format('M j, Y g:i A') }}</span><button type="button" onclick="window.print()">Print / Save as PDF</button></div>
<p class="small">UPSKILL · MICRO-CREDENTIAL EVIDENCE</p><h1>Academic Credit Recognition Evidence</h1><p class="subtitle">This report summarizes verified micro-credential completion evidence for institutional review. It does not award or record academic credit.</p>
<section class="summary">
<div class="box"><span class="small">Student</span><strong>{{ $student->name }}</strong>{{ $student->student_id ?: $student->user_code ?: $student->email }}</div>
<div class="box"><span class="small">Framework</span><strong>{{ $framework->name }}</strong>{{ $framework->pqf_level ? 'PQF Level '.$framework->pqf_level : 'PQF level not specified' }}</div>
<div class="box"><span class="small">Platform progress</span><strong>{{ Illuminate\Support\Str::headline($progress->status ?? 'in progress') }}</strong>{{ $framework->target_recognition ?: 'Credit outcome to be determined by the institution' }}</div>
</section>
<h2>Framework completion evidence</h2>
@foreach($requirements as $item)
<article class="course">
<h3>{{ $loop->iteration }}. {{ $item['course']->title ?? 'Removed course' }}</h3>
<div class="meta">
<div><span class="small">Requirement</span><br>{{ $item['requirement']->is_required ? 'Required' : 'Optional' }}</div>
<div><span class="small">Completion</span><br>{{ $item['is_completed'] ? 'Completed · '.($item['enrollment']->completed_at?->format('M j, Y') ?? 'date unavailable') : 'Not completed' }}</div>
<div><span class="small">Credential evidence</span><br>@if($item['certificate'])Certificate {{ $item['certificate']->serial }} ({{ Illuminate\Support\Str::headline($item['certificate']->status ?? 'issued') }})@elseif($item['badge']){{ $item['badge']->badge?->name ?? 'Badge' }} · {{ $item['badge']->earned_at?->format('M j, Y') }}@else No issued credential found @endif</div>
<div><span class="small">PQF level</span><br>{{ $item['certificate']->pqf_level_snapshot ?? $item['course']->pqf_level ?? 'Not specified' }}</div>
<div><span class="small">Estimated learning hours</span><br>{{ $item['certificate']->learning_hours_snapshot ?? $item['course']->learning_hours ?? 'Not specified' }}</div>
<div><span class="small">Proposed equivalency</span><br>{{ $item['course']->credit_equivalency ?? 'Not specified' }}</div>
</div>
@if($item['outcomes']->isNotEmpty())<p><strong>Learning outcomes</strong></p><ul>@foreach($item['outcomes'] as $outcome)<li>{{ $outcome->description }}</li>@endforeach</ul>@endif
</article>
@endforeach
<div class="notice"><strong>Institutional decision required.</strong> This evidence report is for submission to the appropriate academic unit. Equivalency, credit acceptance, endorsement, and registrar recording are outside UpSkill and must be completed through the institution’s authorized process.</div>
</main></body></html>
