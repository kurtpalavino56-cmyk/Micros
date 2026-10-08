<x-layout title="My Certificates | Upskill" shell="student" page="certificates">
    <header class="page-header ui-page-header student-page-heading">
        <div class="ui-page-header__content">
            <h2 class="ui-page-title">My Certificates</h2>
            <p class="ui-page-subtitle">Your earned microcredential certificates.</p>
        </div>
    </header>

    @if ($certificates->count())
        <div class="certificate-table-wrap ui-table-wrap">
            <table class="certificate-table ui-table" aria-label="Earned certificates">
                <thead>
                    <tr>
                        <th scope="col">Course</th>
                        <th scope="col">Date received</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($certificates as $certificate)
                        <tr>
                            <td data-label="Course">
                                <h3 class="certificate-course">{{ $certificate->course_name }}</h3>
                            </td>
                            <td data-label="Date received">{{ $certificate->issued_date }}</td>
                            <td data-label="Actions">
                                <div class="certificate-actions">
                                    <a href="{{ $certificate->view_url }}" class="certificate-btn view ui-button ui-button--primary">View</a>
                                    <a href="{{ $certificate->download_url }}" class="certificate-btn download ui-button ui-button--secondary">Download</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state ui-empty-state">
            <strong>No certificates yet</strong>
            <p>Complete a microcredential course to receive your first certificate.</p>
        </div>
    @endif
</x-layout>
