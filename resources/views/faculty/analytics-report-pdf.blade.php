<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Faculty Analytics Report | Upskill</title>
<style>
    @page {
        margin: 34px 36px 44px;
    }

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        color: #172033;
        font-family: "DejaVu Sans", sans-serif;
        font-size: 9px;
        line-height: 1.45;
    }

    .report-header {
        width: 100%;
        border-collapse: collapse;
        background: #101c3a;
        color: #fff;
    }

    .report-header td {
        padding: 18px 22px;
        vertical-align: middle;
    }

    .brand {
        margin: 0 0 5px;
        color: #fff;
        font-size: 11px;
        font-weight: bold;
        letter-spacing: 2px;
    }

    .brand span {
        color: #e3b52d;
    }

    .report-title {
        margin: 0;
        color: #fff;
        font-size: 22px;
        font-weight: bold;
    }

    .report-subtitle {
        margin: 4px 0 0;
        color: #c9d4e8;
        font-size: 9px;
    }

    .report-header-meta {
        color: #e3e9f4;
        font-size: 8px;
        line-height: 1.8;
        text-align: right;
    }

    .meta-strip {
        width: 100%;
        border-collapse: collapse;
        margin: 12px 0 14px;
    }

    .meta-strip td {
        padding: 7px 10px;
        border: 1px solid #e4e8f0;
        background: #f7f9fc;
        color: #57647a;
        font-size: 8px;
    }

    .meta-strip strong {
        color: #172033;
    }

    .kpi-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 8px 0;
        margin: 0 -8px;
    }

    .kpi {
        width: 25%;
        padding: 12px 14px;
        border: 1px solid #e5e9f1;
        border-top: 3px solid #233d78;
        background: #fff;
        vertical-align: top;
    }

    .kpi.gold {
        border-top-color: #d3a51f;
    }

    .kpi.teal {
        border-top-color: #249b92;
    }

    .kpi.blue {
        border-top-color: #6276c5;
    }

    .kpi-label {
        color: #647086;
        font-size: 8px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: .45px;
    }

    .kpi-value {
        display: block;
        margin-top: 4px;
        color: #101c3a;
        font-size: 22px;
        font-weight: bold;
    }

    .kpi-note {
        margin-top: 3px;
        color: #748096;
        font-size: 7px;
    }

    .section {
        margin-top: 18px;
        page-break-inside: avoid;
    }

    .section-title {
        margin: 0 0 8px;
        padding: 0 0 6px;
        border-bottom: 1px solid #dfe5ef;
        color: #101c3a;
        font-size: 12px;
        font-weight: bold;
    }

    .section-title span {
        color: #77839a;
        font-size: 8px;
        font-weight: normal;
    }

    .two-column {
        width: 100%;
        border-collapse: separate;
        border-spacing: 14px 0;
        margin: 0 -14px;
    }

    .two-column > tbody > tr > td {
        width: 50%;
        padding: 0 14px;
        vertical-align: top;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th {
        padding: 6px 6px;
        border-bottom: 1px solid #dfe5ef;
        background: #f5f7fb;
        color: #637087;
        font-size: 7px;
        font-weight: bold;
        text-align: left;
        text-transform: uppercase;
        letter-spacing: .3px;
    }

    .data-table td {
        padding: 6px;
        border-bottom: 1px solid #edf0f5;
        color: #27344b;
        font-size: 8px;
        vertical-align: middle;
    }

    .data-table tr:last-child td {
        border-bottom: none;
    }

    .data-table .num {
        text-align: right;
        white-space: nowrap;
    }

    .bar-track {
        width: 100%;
        height: 7px;
        background: #edf1f7;
    }

    .bar-fill {
        height: 7px;
        background: #334f91;
    }

    .bar-fill.gold {
        background: #d3a51f;
    }

    .demographic-label {
        width: 29%;
        color: #344158;
    }

    .demographic-track-cell {
        width: 51%;
    }

    .demographic-value {
        width: 20%;
        color: #43516a;
        text-align: right;
        white-space: nowrap;
    }

    .subsection-title {
        margin: 2px 0 5px;
        color: #43516a;
        font-size: 8px;
        font-weight: bold;
    }

    .summary-row td {
        border-top: 1px solid #dfe5ef;
        color: #101c3a;
        font-weight: bold;
    }

    .muted {
        color: #77839a;
    }

    .rating {
        color: #9a7412;
        font-weight: bold;
    }

    .empty {
        padding: 12px;
        border: 1px dashed #d5dce8;
        color: #77839a;
        text-align: center;
    }

    .comment-course {
        color: #233d78;
        font-weight: bold;
    }

    .comment-text {
        color: #43516a;
    }

    .report-footer {
        margin-top: 20px;
        padding-top: 7px;
        border-top: 1px solid #dfe5ef;
        color: #77839a;
        font-size: 7px;
    }
</style>
</head>
<body>
@php
    $stats = is_array($stats ?? null) ? $stats : [];
    $demographics = $demographics ?? ['total' => 0, 'age' => [], 'gender' => []];
    $success = collect($learnerSuccess ?? []);
    $successTotal = (int) $success->sum('count');
    $points = collect($academyStats ?? []);
    $maxPointValue = max(1, (int) ($points->max('value') ?? 0));
    $peakValue = $points->max('value');
    $learners = (int) ($stats['total_learners'] ?? 0);
    $certificates = (int) ($stats['certificates'] ?? 0);
    $lessons = (int) ($stats['lessons_done'] ?? 0);
    $liveLearners = (int) ($onlineNow ?? 0);
    $courseRows = method_exists($courseAnalytics ?? null, 'getCollection')
        ? $courseAnalytics->getCollection()
        : collect($courseAnalytics ?? []);
    $courseTotal = method_exists($courseAnalytics ?? null, 'total')
        ? $courseAnalytics->total()
        : $courseRows->count();
    $coursePage = method_exists($courseAnalytics ?? null, 'currentPage')
        ? $courseAnalytics->currentPage()
        : 1;
    $courseLastPage = method_exists($courseAnalytics ?? null, 'lastPage')
        ? $courseAnalytics->lastPage()
        : 1;
    $minReviews = \App\Support\CourseEvaluationTemplate::MINIMUM_REVIEWS_FOR_RATING;
    $generatedAt = $generatedAt ?? now();
    $preparedName = trim(($preparedBy->first_name ?? '').' '.($preparedBy->last_name ?? ''));
    $pointValues = $points->map(fn ($point) => (int) (is_array($point) ? ($point['value'] ?? 0) : ($point->value ?? 0)));
    $growthLatest = (int) ($pointValues->last() ?? 0);
    $growthPrevious = $pointValues->count() > 1 ? (int) $pointValues->slice(-2, 1)->first() : $growthLatest;
    $growthDelta = $growthLatest - $growthPrevious;
    $growthAverage = $pointValues->isNotEmpty() ? round($pointValues->avg(), 1) : 0;
    $averageOf = function (string $key) use ($courseRows): ?float {
        $values = $courseRows->pluck($key)->filter(fn ($value) => $value !== null);

        return $values->isEmpty() ? null : round($values->avg(), 1);
    };
@endphp

<table class="report-header">
    <tr>
        <td>
            <p class="brand">UPSKILL<span>.</span></p>
            <h1 class="report-title">Faculty analytics</h1>
            <p class="report-subtitle">A clear overview of learner activity, outcomes, and course performance.</p>
        </td>
        <td class="report-header-meta">
            <strong>ANALYTICS REPORT</strong><br>
            {{ $generatedAt->format('F j, Y') }}<br>
            {{ $generatedAt->format('g:i A') }}
        </td>
    </tr>
</table>

<table class="meta-strip">
    <tr>
        <td><strong>Course scope</strong> &nbsp; {{ $reportCourseTitle ?? 'All my courses' }}</td>
        <td><strong>Prepared for</strong> &nbsp; {{ $preparedName !== '' ? $preparedName : 'Faculty' }}</td>
        <td><strong>Data snapshot</strong> &nbsp; {{ $generatedAt->format('M j, Y g:i A') }}</td>
    </tr>
</table>

<table class="kpi-table">
    <tr>
        <td class="kpi">
            <span class="kpi-label">Live learners</span>
            <span class="kpi-value">{{ number_format($liveLearners) }}</span>
            <div class="kpi-note">{{ $learners > 0 ? number_format($liveLearners / $learners * 100, 1) : '0.0' }}% of {{ number_format($learners) }} learners active in the last 5 minutes</div>
        </td>
        <td class="kpi gold">
            <span class="kpi-label">Total learners</span>
            <span class="kpi-value">{{ number_format($learners) }}</span>
            <div class="kpi-note">Unique learners across the selected course scope</div>
        </td>
        <td class="kpi teal">
            <span class="kpi-label">Certificates issued</span>
            <span class="kpi-value">{{ number_format($certificates) }}</span>
            <div class="kpi-note">{{ $learners > 0 ? number_format($certificates / $learners * 100, 1) : '0.0' }} per 100 learners</div>
        </td>
        <td class="kpi blue">
            <span class="kpi-label">Lessons completed</span>
            <span class="kpi-value">{{ number_format($lessons) }}</span>
            <div class="kpi-note">{{ $learners > 0 ? number_format($lessons / $learners, 1) : '0.0' }} per learner</div>
        </td>
    </tr>
</table>

<table class="two-column section">
    <tr>
        <td>
            <h2 class="section-title">Enrolled learner growth <span>Last 12 months</span></h2>
            @if($points->isNotEmpty())
                <table class="data-table">
                    <thead><tr><th>Month</th><th>Growth</th><th class="num">Learners</th></tr></thead>
                    <tbody>
                    @foreach($points as $point)
                        @php
                            $value = (int) (is_array($point) ? ($point['value'] ?? 0) : ($point->value ?? 0));
                            $label = is_array($point) ? ($point['label'] ?? '') : ($point->label ?? '');
                            $width = min(100, max(0, round($value / $maxPointValue * 100)));
                        @endphp
                        <tr>
                            <td>{{ str_replace("\n", ' ', $label) }}</td>
                            <td><div class="bar-track"><div class="bar-fill {{ $value === $peakValue ? 'gold' : '' }}" style="width: {{ $width }}%;"></div></div></td>
                            <td class="num">{{ number_format($value) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <p class="kpi-note">Latest {{ $growthLatest }} · Change {{ $growthDelta > 0 ? '+' : '' }}{{ $growthDelta }} · Peak {{ $peakValue }} · 12-month average {{ $growthAverage }}</p>
            @else
                <div class="empty">No enrollment history available.</div>
            @endif
        </td>
        <td>
            <h2 class="section-title">Learner demographics <span>{{ number_format((int) ($demographics['total'] ?? 0)) }} unique learners</span></h2>
            @if(($demographics['total'] ?? 0) > 0)
                @foreach(['Age distribution' => ($demographics['age'] ?? []), 'Gender distribution' => ($demographics['gender'] ?? [])] as $groupTitle => $group)
                    <h3 class="subsection-title">{{ $groupTitle }}</h3>
                    <table class="data-table">
                        <tbody>
                        @foreach($group as $label => $count)
                            @php
                                $percentage = (int) round($count / $demographics['total'] * 100);
                                $width = min(100, max(0, $percentage));
                            @endphp
                            <tr>
                                <td class="demographic-label">{{ $label }}</td>
                                <td class="demographic-track-cell"><div class="bar-track"><div class="bar-fill" style="width: {{ $width }}%;"></div></div></td>
                                <td class="demographic-value">{{ number_format($count) }} · {{ $percentage }}%</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endforeach
            @else
                <div class="empty">No enrolled learner demographics are available for this course scope.</div>
            @endif
        </td>
    </tr>
</table>

<table class="two-column section">
    <tr>
        <td>
            <h2 class="section-title">Learner success <span>All time · {{ number_format($successTotal) }} learners</span></h2>
            @if($success->isNotEmpty())
                <table class="data-table">
                    <thead><tr><th>Progress segment</th><th class="num">Learners</th><th class="num">Share</th></tr></thead>
                    <tbody>
                    @foreach($success as $segment)
                        @php
                            $count = (int) ($segment['count'] ?? 0);
                            $share = $successTotal > 0 ? round($count / $successTotal * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td>{{ $segment['label'] ?? '' }}</td>
                            <td class="num">{{ number_format($count) }}</td>
                            <td class="num">{{ number_format($share, 1) }}%</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty">No learner results available.</div>
            @endif
        </td>
        <td>
            <h2 class="section-title">Recent course evaluation comments <span>Latest 5</span></h2>
            @if(($recentCourseComments ?? collect())->isNotEmpty())
                <table class="data-table">
                    <thead><tr><th>Course and rating</th><th>Comment</th></tr></thead>
                    <tbody>
                    @foreach($recentCourseComments as $review)
                        <tr>
                            <td><span class="comment-course">{{ $review->course?->title ?? 'Course' }}</span><br><span class="rating">{{ number_format((float) $review->rating, 1) }} / 5</span></td>
                            <td class="comment-text">{{ $review->comment }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty">No written course evaluation comments yet.</div>
            @endif
        </td>
    </tr>
</table>

<div class="section">
    <h2 class="section-title">Course outcomes <span>{{ number_format($courseTotal) }} {{ \Illuminate\Support\Str::plural('course', $courseTotal) }} · page {{ $coursePage }} of {{ $courseLastPage }}</span></h2>
    @if($courseRows->isNotEmpty())
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 22%;">Course</th>
                    <th class="num">Enrolled</th>
                    <th class="num">Completed</th>
                    <th class="num">Completion</th>
                    <th class="num">Avg. quiz</th>
                    <th class="num">Quiz pass</th>
                    <th class="num">Avg. rating</th>
                    <th class="num">Reviews</th>
                    <th class="num">To grade</th>
                </tr>
            </thead>
            <tbody>
            @foreach($courseRows as $course)
                <tr>
                    <td>{{ $course->title }}</td>
                    <td class="num">{{ number_format($course->enrolled) }}</td>
                    <td class="num">{{ number_format($course->completed) }}</td>
                    <td class="num">{{ $course->completion_rate === null ? '—' : $course->completion_rate.'%' }}</td>
                    <td class="num">{{ $course->average_score === null ? '—' : $course->average_score.'%' }}</td>
                    <td class="num">{{ $course->pass_rate === null ? '—' : $course->pass_rate.'%' }}</td>
                    <td class="num">
                        @if($course->review_count >= $minReviews)
                            <span class="rating">{{ number_format($course->rating_average, 1) }} / 5</span>
                        @else
                            <span class="muted">New</span>
                        @endif
                    </td>
                    <td class="num">{{ number_format($course->review_count) }}</td>
                    <td class="num">{{ number_format($course->pending_reviews) }}</td>
                </tr>
            @endforeach
            </tbody>
            @if($courseRows->count() > 1)
                <tfoot>
                    <tr class="summary-row">
                        <td>Average on this page</td>
                        <td class="num">{{ $averageOf('enrolled') ?? '—' }}</td>
                        <td class="num">{{ $averageOf('completed') ?? '—' }}</td>
                        <td class="num">{{ $averageOf('completion_rate') === null ? '—' : $averageOf('completion_rate').'%' }}</td>
                        <td class="num">{{ $averageOf('average_score') === null ? '—' : $averageOf('average_score').'%' }}</td>
                        <td class="num">{{ $averageOf('pass_rate') === null ? '—' : $averageOf('pass_rate').'%' }}</td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @else
        <div class="empty">No courses are available for this faculty account.</div>
    @endif
</div>

<div class="report-footer">
    Upskill · Faculty Analytics · This report reflects the selected dashboard scope and visible course-outcomes page. Live learner count is a snapshot taken when the report was generated.
</div>
</body>
</html>
