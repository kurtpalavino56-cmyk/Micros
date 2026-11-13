<x-faculty.layout title="Import quiz questions | Upskill" data-faculty-page="quiz-csv-import" use-app-css use-app-js>
    <main class="main quiz-csv-page">
        <a class="quiz-csv-back-link" href="{{ $builderUrl }}">&larr; Back to quiz builder</a>
        <header class="quiz-csv-header">
            <span class="quiz-csv-eyebrow">Question pool</span>
            <h1>Import questions from Excel or CSV</h1>
            <p><strong>{{ $course->title }}</strong><span aria-hidden="true"> · </span>{{ $quiz->title }}</p>
        </header>

        @if($errors->any())
            <div class="quiz-csv-alert" role="alert">
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <section class="quiz-csv-card quiz-csv-upload-card" aria-labelledby="csv-upload-heading">
            <div class="quiz-csv-card-heading">
                <div>
                    <h2 id="csv-upload-heading">Choose your question file</h2>
                    <p>Download the Excel template, fill in your questions, then upload it. Review the preview before anything is imported.</p>
                </div>
                <a class="quiz-csv-template-link" href="{{ $templateUrl }}">Download Excel template</a>
            </div>

            <div class="quiz-csv-guidance">
                <h3>Prepare your file</h3>
                <p>Upload the filled-in Excel template (.xlsx) or a UTF-8 CSV file, up to 2 MB with no more than 500 data rows. Only the first sheet of a workbook is read. The preview checks every row before anything is saved.</p>
                <p class="quiz-csv-columns"><strong>Columns:</strong> <code>type, question, choice_a, choice_b, choice_c, choice_d, correct_answer, points</code></p>
                <ul>
                    <li>Multiple choice uses A-D letters or exact choice text. True/false accepts True or False.</li>
                    <li>Identification questions accept one correct answer. Matching trims the submitted answer, ignores case, and collapses repeated whitespace; it does not accept multiple answer variants.</li>
                    <li>Rephrase any answer starting with <code>-</code> to avoid spreadsheet formula injection.</li>
                    <li>Blank points and points of 0 count as 1. Points below 1 are normalized to 1 and called out in the preview. Uploads are limited to 2 MB and 500 data rows.</li>
                </ul>
            </div>

            @unless($replaceAllowed)
                <div class="quiz-csv-notice" role="note">
                    <strong>Append-only for this quiz</strong>
                    <span>Replace mode is unavailable because students have already attempted this quiz. Imports will append to the question pool.</span>
                </div>
            @endunless

            <form class="quiz-csv-upload-form" method="POST" action="{{ $previewUrl }}" enctype="multipart/form-data">
                @csrf
                <label class="quiz-csv-file-label" for="csv-file">Excel (.xlsx) or CSV file</label>
                <input class="quiz-csv-file-input" id="csv-file" type="file" name="csv_file" accept=".xlsx,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv" required>
                @error('csv_file')<small class="quiz-csv-field-error" role="alert">{{ $message }}</small>@enderror
                <button class="quiz-csv-primary-button" type="submit">Preview import</button>
            </form>
        </section>
    </main>
</x-faculty.layout>
