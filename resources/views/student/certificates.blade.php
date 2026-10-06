{{-- resources/views/student/certificates.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Certificates | Upskill</title>
<link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
<link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">
<style>
    :root {
        --navy: #13176b;
        --navy-deep: #0c0f4d;
        --gold: #dba617;
        --cyan: #7fe9e3;
        --ink: #17205f;
        --muted: #69728a;
        --line: #e2e6ef;
        --surface: #ffffff;
        --page: #f5f7fb;
        --shadow: 0 8px 24px rgba(19, 23, 107, 0.07);
    }

    * { box-sizing: border-box; }
    body {
        margin: 0;
        font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        color: var(--ink);
        background: var(--page);
    }
    a { text-decoration: none; color: inherit; }
    button { font-family: inherit; }

    /* Main content */
    .main {
        padding: 34px 38px 64px;
    }

    .page-head {
        margin-bottom: 22px;
    }
    .page-head h2 {
        margin: 0;
        color: var(--navy);
        font-size: 30px;
        line-height: 1.2;
        font-weight: 800;
        letter-spacing: -.3px;
    }
    .page-head p {
        margin: 7px 0 0;
        color: var(--muted);
        font-size: 14px;
    }

    /* Certificate list */
    .certificate-table-wrap {
        overflow-x: auto;
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: 8px;
        box-shadow: var(--shadow);
    }
    .certificate-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        table-layout: fixed;
    }
    .certificate-table th:first-child,
    .certificate-table td:first-child { width: 60%; }
    .certificate-table th:nth-child(2),
    .certificate-table td:nth-child(2) {
        width: 20%;
        text-align: center;
    }
    .certificate-table th:last-child,
    .certificate-table td:last-child { width: 20%; }
    .certificate-table td:last-child { text-align: center; }
    .certificate-table th:last-child { text-align: center; }
    .certificate-table th {
        padding: 14px 22px;
        background: #172f94;
        color: #fff;
        font-size: 12px;
        font-weight: 750;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .certificate-table td {
        padding: 18px 22px;
        border-top: 1px solid var(--line);
        vertical-align: middle;
    }
    .certificate-table tbody tr:first-child td { border-top: 0; }
    .certificate-course {
        margin: 0;
        color: var(--navy);
        font-size: 17px;
        line-height: 1.35;
        font-weight: 750;
        overflow-wrap: anywhere;
    }
    .certificate-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        flex-shrink: 0;
    }
    .certificate-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 92px;
        height: 38px;
        padding: 0 17px;
        border: 1px solid var(--navy);
        border-radius: 6px;
        font-size: 13px;
        font-weight: 750;
        line-height: 1;
        transition: background-color .15s ease, color .15s ease, border-color .15s ease, transform .15s ease;
    }
    .certificate-btn:hover {
        transform: translateY(-1px);
    }
    .certificate-btn.view {
        color: #fff;
        background: var(--navy);
    }
    .certificate-btn.view:hover {
        background: var(--gold);
        border-color: var(--gold);
        color: var(--navy);
    }
    .certificate-btn.download {
        color: var(--navy);
        background: #fff;
    }
    .certificate-btn.download:hover {
        background: var(--gold);
        border-color: var(--gold);
        color: var(--navy);
    }

    .empty-state {
        background: var(--surface);
        border: 1px dashed #cfd5e2;
        border-radius: 8px;
        padding: 46px 28px;
        text-align: center;
        color: var(--muted);
    }
    .empty-state strong {
        display: block;
        color: var(--navy);
        font-size: 16px;
        margin-bottom: 5px;
    }
    .empty-state p {
        margin: 0;
        font-size: 14px;
    }

    @media (max-width: 760px) {
        .main { padding: 26px 20px 50px; }
        .page-head h2 { font-size: 26px; }
        .certificate-table thead {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
        .certificate-table,
        .certificate-table tbody,
        .certificate-table tr,
        .certificate-table td {
            display: block;
        }
        .certificate-table tr { padding: 8px 0; }
        .certificate-table th:first-child,
        .certificate-table td:first-child,
        .certificate-table th:nth-child(2),
        .certificate-table td:nth-child(2),
        .certificate-table th:last-child,
        .certificate-table td:last-child {
            width: auto;
            text-align: left;
        }
        .certificate-table th:nth-child(2),
        .certificate-table td:nth-child(2) {
            text-align: left;
        }
        .certificate-table th:last-child { text-align: left; }
        .certificate-table td {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 9px 14px;
            border-top: 0;
        }
        .certificate-table td::before {
            content: attr(data-label);
            flex: 0 0 100px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }
        .certificate-table td:first-child::before { content: "Course"; }
        .certificate-table td:nth-child(2)::before { content: "Date received"; }
        .certificate-table td:last-child::before { content: "Actions"; }
        .certificate-course { font-size: 15px; }
        .certificate-btn {
            min-width: 0;
            height: 34px;
            padding: 0 12px;
        }
        .certificate-actions {
            justify-content: flex-start;
            flex-wrap: wrap;
        }
    }
</style>
</head>
<body>

@include('components.student-navigation')

<div class="layout student-sidebar-layout">
    @include('components.student-sidebar')

    <main class="main">
        <header class="page-head student-page-heading">
            <h2>My Certificates</h2>
            <p>Your earned microcredential certificates.</p>
        </header>

        @if ($certificates->count())
            <div class="certificate-table-wrap">
                <table class="certificate-table" aria-label="Earned certificates">
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
                                        <a href="{{ $certificate->view_url }}" class="certificate-btn view">View</a>
                                        <a href="{{ $certificate->download_url }}" class="certificate-btn download">Download</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <strong>No certificates yet</strong>
                <p>Complete a microcredential course to receive your first certificate.</p>
            </div>
        @endif
    </main>
</div>

@include('components.responsive')
</body>
</html>
