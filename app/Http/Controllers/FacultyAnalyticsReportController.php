<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * FacultyAnalyticsReportController
 *
 * Streams the Faculty Analytics page as a downloadable PDF report.
 *
 * Instead of duplicating the analytics queries, this controller re-uses the
 * exact view data assembled by FacultyController::analytics() — the same
 * numbers the faculty member sees on screen are the numbers in the PDF.
 * Only the Live Monitoring Feed snapshot is queried here, because on the
 * page that list is filled by JavaScript polling /monitoring/live.
 */
class FacultyAnalyticsReportController extends Controller
{
    /**
     * GET /Faculty-analytics/report  (name: faculty.analytics.report)
     */
    public function download()
    {
        // Run the same action that renders the on-screen Analytics page and
        // harvest the data it passed to the Blade view ($onlineNow, $stats,
        // $academyStats, $learnerSuccess, ...). Passing request() is harmless
        // whether the method declares a Request parameter or not.
        $page = app(FacultyController::class)->analytics(request());

        // If the action ever redirects (e.g. incomplete profile), follow it
        // instead of rendering a report.
        if (! $page instanceof View) {
            return redirect()->route('faculty.analytics');
        }

        $data = $page->getData();

        $data['generatedAt']    = now();
        $data['preparedBy']     = auth()->user();
        $data['recentActivity'] = $this->recentActivity();

        $pdf = Pdf::loadView('faculty.analytics-report-pdf', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->download('Faculty-Analytics-Report-'.now()->format('Y-m-d').'.pdf');
    }

    /**
     * Snapshot of the Live Monitoring Feed for the report: the most recent
     * analytics events, resolved to readable titles and learner names.
     */
    private function recentActivity(int $limit = 15): array
    {
        return DB::table('analytics_events')
            ->leftJoin('users', 'users.id', '=', 'analytics_events.user_id')
            ->orderByDesc('analytics_events.occurred_at')
            ->limit($limit)
            ->get([
                'analytics_events.event_type',
                'analytics_events.metadata',
                'analytics_events.occurred_at',
                'users.first_name',
                'users.last_name',
            ])
            ->map(function ($event) {
                $metadata   = json_decode($event->metadata ?? 'null', true) ?: [];
                $name       = trim(($event->first_name ?? '').' '.($event->last_name ?? '')) ?: 'A learner';
                $occurredAt = Carbon::parse($event->occurred_at);

                return [
                    'title'  => Str::headline(str_replace(['_', '-'], ' ', (string) $event->event_type)),
                    'detail' => $metadata['detail'] ?? $metadata['description'] ?? $name,
                    'time'   => $occurredAt->diffForHumans(),
                    'date'   => $occurredAt->format('M j, Y g:i A'),
                ];
            })
            ->all();
    }
}
