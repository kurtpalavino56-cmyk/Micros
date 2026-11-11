<x-faculty.layout title="Import quiz questions | Upskill" data-faculty-page="quiz-csv-import">
    <main class="container" style="max-width:900px;padding:32px 16px;">
        <p><a href="{{ $builderUrl }}">&larr; Back to quiz builder</a></p>
        <h1>Import questions from CSV</h1>
        <p><strong>{{ $course->title }}</strong> · {{ $quiz->title }}</p>
        <p>Upload a UTF-8 CSV file up to 2 MB with no more than 500 data rows. Preview checks every row before anything is saved.</p>

        @if($errors->any())
            <div role="alert" style="padding:12px;border:1px solid #b42318;background:#fee4e2;color:#7a1a12;border-radius:8px;margin:16px 0;">
                <ul style="margin:0;padding-left:20px;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <p><a class="btn-secondary-action" href="{{ $templateUrl }}">Download CSV template</a></p>
        <p>Columns: <code>type, question, choice_a, choice_b, choice_c, choice_d, correct_answer, points</code>. Multiple choice uses A-D letters or exact choice text. True/false accepts True or False.</p>
        <ul>
            <li>Identification accepts one answer. Matching trims the submitted answer, ignores case, and collapses repeated whitespace; it does not accept multiple answer variants.</li>
            <li>Rephrase any answer starting with <code>-</code> to avoid spreadsheet formula injection.</li>
            <li>Blank points and points of 0 count as 1. Uploads are limited to 2 MB and 500 data rows.</li>
        </ul>
        @unless($replaceAllowed)
            <p role="note">Replace mode is unavailable because students have already attempted this quiz. Imports will append questions.</p>
        @endunless

        <form method="POST" action="{{ $previewUrl }}" enctype="multipart/form-data" style="display:grid;gap:14px;max-width:620px;">
            @csrf
            <label for="csv-file"><strong>CSV file</strong></label>
            <input id="csv-file" type="file" name="csv_file" accept=".csv,text/csv" required>
            @error('csv_file')<small role="alert">{{ $message }}</small>@enderror
            <button class="btn-save-lesson" type="submit">Preview import</button>
        </form>
        <p style="margin-top:18px;">Blank points and points of 0 count as 1. Points below 1 are normalized to 1 and called out in the preview.</p>
    </main>
</x-faculty.layout>
