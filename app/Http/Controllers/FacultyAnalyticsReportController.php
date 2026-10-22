<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * FacultyAnalyticsReportController
 *
 * Streams the Faculty Analytics page as a downloadable PDF report.
 *
 * Reuses the analytics page data so the PDF reflects the current course scope
 * and the same metrics shown on the dashboard.
 */
class FacultyAnalyticsReportController extends Controller
{
    /**
     * GET /Faculty-analytics/report  (name: faculty.analytics.report)
     */
    public function download(Request $request)
    {
        $page = app(FacultyController::class)->analytics($request);

        if (! $page instanceof View) {
            return redirect()->route('faculty.analytics');
        }

        $data = $page->getData();
        $data['generatedAt'] = now();
        $data['preparedBy'] = auth()->user();
        $data['reportCourseTitle'] = $data['selectedCourseId'] === null
            ? 'All my courses'
            : $data['facultyCourses']->firstWhere('id', $data['selectedCourseId'])?->title;

        $pdf = Pdf::loadView('faculty.analytics-report-pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('Faculty-Analytics-Report-'.now()->format('Y-m-d').'.pdf');
    }
}
