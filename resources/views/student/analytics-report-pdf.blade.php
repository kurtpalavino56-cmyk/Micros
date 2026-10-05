<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Student Learning Report</title>
    <style>
        @page { margin: 38px; }
        body { color: #24314d; font: 10px DejaVu Sans, sans-serif; }
        h1 { color: #13176b; font-size: 20px; margin: 0 0 4px; }
        h2 { color: #13176b; font-size: 13px; margin: 20px 0 8px; }
        p { color: #667085; margin: 0 0 12px; }
        .meta { border-bottom: 2px solid #dba617; padding-bottom: 10px; }
        .stats { width: 100%; border-collapse: separate; border-spacing: 6px; margin: 10px 0; }
        .stats td { width: 25%; background: #f1f4fb; border: 1px solid #e1e6f0; padding: 10px; }
        .stats strong { display: block; color: #13176b; font-size: 17px; margin-top: 4px; }
        table.data { width: 100%; border-collapse: collapse; }
        .data th { background: #f1f4fb; color: #475467; text-align: left; }
        .data th, .data td { border: 1px solid #e1e6f0; padding: 7px; }
        .right { text-align: right; }
        .note { margin-top: 8px; font-size: 9px; }
        .footer { border-top: 1px solid #e1e6f0; margin-top: 22px; padding-top: 8px; color: #667085; }
    </style>
</head>
<body>
    @php
        $weekly = $analyticsSeries['week'] ?? ['labels' => [], 'hours' => [], 'active' => []];
        $weeklyHours = round(collect($weekly['hours'])->sum(), 1);
        $activeDays = collect($weekly['active'])->filter()->count();
        $progressRows = collect($completionCourses ?? []);
        $averageProgress = $progressRows->count() ? round($progressRows->avg('percent')) : 0;
    @endphp
    <div class="meta">
        <h1>My Learning Analytics</h1>
        <p>Prepared for {{ $preparedBy->name }} · Generated {{ $generatedAt->format('F j, Y g:i A') }}</p>
    </div>
    <table class="stats">
        <tr>
            <td>Average course progress<strong>{{ $averageProgress }}%</strong></td>
            <td>Completed courses<strong>{{ $stats['completed_courses'] ?? 0 }}</strong></td>
            <td>Assessment average<strong>{{ isset($stats['score_avg']) ? number_format((float) $stats['score_avg'], 1) . '%' : 'No attempts' }}</strong></td>
            <td>Estimated study time this week<strong>{{ $weeklyHours }}h</strong></td>
        </tr>
    </table>
    <h2>Course progress</h2>
    <table class="data"><thead><tr><th>Course</th><th class="right">Progress</th><th>Status</th></tr></thead><tbody>
        @forelse($progressRows as $course)
            <tr><td>{{ $course->title }}</td><td class="right">{{ (int) $course->percent }}%</td><td>{{ $course->status ?? 'In progress' }}</td></tr>
        @empty
            <tr><td colspan="3">No enrolled course progress is available.</td></tr>
        @endforelse
    </tbody></table>
    <h2>Recent assessment attempts</h2>
    <table class="data"><thead><tr><th>Assessment</th><th>Course</th><th class="right">Score</th><th>Result</th><th>Date</th></tr></thead><tbody>
        @forelse($recentQuizAttempts as $attempt)
            <tr>
                <td>{{ $attempt->quiz->title ?? 'Quiz' }}</td>
                <td>{{ $attempt->quiz->course->title ?? 'Course' }}</td>
                <td class="right">{{ $attempt->score === null ? '—' : number_format((float) $attempt->score, 1) . '%' }}</td>
                <td>{{ $attempt->passed === null ? 'Not scored' : ($attempt->passed ? 'Passed' : 'Not passed') }}</td>
                <td>{{ ($attempt->submitted_at ?? $attempt->created_at)?->format('M j, Y') ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="5">No quiz attempts recorded.</td></tr>
        @endforelse
    </tbody></table>
    <h2>Weekly learning activity</h2>
    <table class="data"><thead><tr><th>Day</th><th class="right">Estimated lesson hours</th><th class="right">Lessons completed</th></tr></thead><tbody>
        @foreach($weekly['labels'] as $index => $label)
            <tr><td>{{ $label }}</td><td class="right">{{ number_format((float) ($weekly['hours'][$index] ?? 0), 2) }}</td><td class="right">{{ $weekly['lessons'][$index] ?? 0 }}</td></tr>
        @endforeach
    </tbody></table>
    <p class="note">Study time is estimated from the duration of completed lessons; it is not a timer of time spent in the platform. Active learning days this week: {{ $activeDays }}.</p>
    <h2>Assessment and competency summary</h2>
    <table class="data"><tbody>
        <tr><th>Quiz attempts recorded</th><td>{{ $stats['quiz_attempts_count'] ?? 0 }}</td></tr>
        <tr><th>Competencies mastered</th><td>{{ $stats['competencies_mastered'] ?? 0 }}</td></tr>
        <tr><th>Badges earned</th><td>{{ $stats['badges_earned'] ?? 0 }}</td></tr>
        <tr><th>Certificates earned</th><td>{{ $stats['certificates'] ?? 0 }}</td></tr>
    </tbody></table>
    <div class="footer">UPSKILL · Personal learning report · Contains only your learning records.</div>
</body>
</html>
