{{--
    resources/views/student/certificate-view.blade.php

    Browser view of a single already-issued certificate (Phase 3, Step 6).
    $cert comes from CertificateBuilder::pdfData() — snapshot data only,
    the same data the PDF and public verification use.
--}}
<x-layout :title="($cert['certificate_title'] ?: $cert['course_title']) . ' — Certificate | Upskill'" shell="student" page="certificate-view">
<div class="bar">
    <div class="actions certificate-toolbar">
        @include('components.breadcrumbs', ['items' => [
            ['label' => 'My Certificates', 'url' => route('certificates.index')],
            ['label' => $cert['course_title'] ?: 'Certificate'],
        ]])
        <a class="btn btn-gold ui-button ui-button--primary" href="{{ route('certificates.download', $cert['serial']) }}">Download PDF</a>
    </div>
</div>
<div class="wrap">
    <span class="status-pill ui-badge {{ ($cert['status'] ?? 'active') === 'revoked' ? 'ui-badge--danger status-revoked' : 'ui-badge--success status-active' }}">
        {{ ($cert['status'] ?? 'active') === 'revoked' ? 'Revoked' : 'Active' }}
    </span>

    @include('components.certificate', ['cert' => $cert])

    @if (!empty($cert['learning_outcomes']))
    <div class="certificate-view-detail ui-card-surface certificate-view-detail--outcomes">
        <h3 class="ui-section-title">Learning Outcomes Achieved</h3>
        <ul>
            @foreach ($cert['learning_outcomes'] as $lo)
                <li>@if(!empty($lo['code'])){{ $lo['code'] }}: @endif{{ $lo['description'] ?? '' }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if (!empty($cert['competencies']))
    <div class="certificate-view-detail ui-card-surface certificate-view-detail--competencies">
        <h3 class="ui-section-title">Competencies Achieved</h3>
        <ul>
            @foreach ($cert['competencies'] as $unit)
                <li>{{ $unit['title'] ?? '' }}</li>
            @endforeach
        </ul>
    </div>
    @endif
</div>

</x-layout>
