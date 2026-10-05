<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Platform Analytics Report</title>
    <style>
        @page { margin: 34px; }
        body { color: #24314d; font: 10px DejaVu Sans, sans-serif; }
        h1 { color: #0d1b6e; font-size: 20px; margin: 0 0 4px; }
        h2 { color: #0d1b6e; font-size: 13px; margin: 20px 0 8px; }
        p { color: #667085; margin: 0 0 12px; }
        .meta { border-bottom: 2px solid #dba617; padding-bottom: 10px; }
        .stats { width: 100%; border-collapse: separate; border-spacing: 6px; margin: 10px 0; }
        .stats td { width: 16.6%; background: #f1f4fb; border: 1px solid #e1e6f0; padding: 9px; }
        .stats strong { display: block; color: #0d1b6e; font-size: 17px; margin-top: 4px; }
        table.data { width: 100%; border-collapse: collapse; }
        .data th { background: #f1f4fb; color: #475467; text-align: left; }
        .data th, .data td { border: 1px solid #e1e6f0; padding: 6px; }
        .right { text-align: right; }
        .footer { border-top: 1px solid #e1e6f0; margin-top: 22px; padding-top: 8px; color: #667085; }
    </style>
</head>
<body>
    <div class="meta">
        <h1>Platform Analytics</h1>
        <p>Generated {{ $generatedAt->format('F j, Y g:i A') }} · Prepared by {{ $preparedBy->name }}</p>
        <p>Period: {{ ucfirst($period) }} · Category: {{ $filters['category'] ?: 'All' }} · Course: {{ optional($courseOptions->firstWhere('id', $filters['course']))->title ?? 'All' }}</p>
    </div>
    <table class="stats">
        <tr>
            <td>Total learners<strong>{{ number_format($stats['total_students']) }}</strong></td>
            <td>Active learners<strong>{{ number_format($stats['active_students']) }}</strong></td>
            <td>Courses<strong>{{ number_format($stats['total_courses']) }}</strong></td>
            <td>Completions<strong>{{ number_format($stats['completions']) }}</strong></td>
            <td>Credentials<strong>{{ number_format($stats['credentials']) }}</strong></td>
            <td>Average quiz score<strong>{{ $stats['course_score_avg'] === null ? 'No attempts' : $stats['course_score_avg'] . '%' }}</strong></td>
        </tr>
    </table>
    <h2>Course performance</h2>
    <table class="data"><thead><tr><th>Course</th><th class="right">Enrollments</th><th class="right">Average progress</th><th class="right">Average quiz score</th></tr></thead><tbody>
        @forelse($coursePerformance as $course)
            <tr><td>{{ $course->title }}</td><td class="right">{{ $course->enrolled }}</td><td class="right">{{ $course->completion }}%</td><td class="right">{{ $course->score }}%</td></tr>
        @empty
            <tr><td colspan="4">No course data matches these filters.</td></tr>
        @endforelse
    </tbody></table>
    <h2>Learner progress distribution</h2>
    <table class="data"><thead><tr><th>Status</th><th class="right">Share of learners</th></tr></thead><tbody>
        <tr><td>Completed</td><td class="right">{{ $progress['completed'] }}%</td></tr>
        <tr><td>In progress</td><td class="right">{{ $progress['in_progress'] }}%</td></tr>
        <tr><td>Not started</td><td class="right">{{ $progress['not_started'] }}%</td></tr>
    </tbody></table>
    <h2>Assessment outcomes</h2>
    <table class="data"><tbody>
        <tr><th>Scored quiz attempts</th><td>{{ number_format($assessment['total']) }}</td><th>Average score</th><td>{{ $assessment['average'] === null ? 'No attempts' : $assessment['average'] . '%' }}</td></tr>
        <tr><th>Pass rate</th><td>{{ $assessment['pass_rate'] === null ? 'No attempts' : $assessment['pass_rate'] . '%' }}</td><th>Score range</th><td>{{ $assessment['lowest'] === null ? '—' : $assessment['lowest'] . '%–' . $assessment['highest'] . '%' }}</td></tr>
    </tbody></table>
    <h2>Competency mastery</h2>
    <table class="data"><thead><tr><th>Competency category</th><th class="right">Average mastery</th></tr></thead><tbody>
        @forelse($competencies as $competency)
            <tr><td>{{ $competency->name }}</td><td class="right">{{ $competency->mastery }}%</td></tr>
        @empty
            <tr><td colspan="2">No competency data is linked to these courses.</td></tr>
        @endforelse
    </tbody></table>
    <h2>Credential outcomes</h2>
    <table class="data"><tbody>
        <tr><th>Active certificates</th><td>{{ number_format($credentialMetrics['certificates']) }}</td><th>Badges issued</th><td>{{ number_format($credentialMetrics['badges']) }}</td></tr>
        <tr><th>Verifiable certificates</th><td>{{ number_format($credentialMetrics['verified']) }}</td><th>Courses awaiting approval</th><td>{{ number_format($credentialMetrics['pending']) }}</td></tr>
    </tbody></table>
    <h2>{{ ucfirst($activityMode) }} activity by month</h2>
    <table class="data"><thead><tr><th>Month</th><th class="right">Count</th></tr></thead><tbody>
        @foreach($activity['labels'] as $index => $label)
            <tr><td>{{ $label }}</td><td class="right">{{ $activity['values'][$index] }}</td></tr>
        @endforeach
    </tbody></table>
    <div class="footer">UPSKILL · Platform analytics report · Figures reflect the selected filters and period.</div>
</body>
</html>
