<x-faculty.layout title="Import quiz questions | Upskill" data-faculty-page="quiz-csv-import" use-app-css use-app-js>
    <main class="main quiz-csv-page">
        <a class="quiz-csv-back-link" href="{{ $builderUrl }}">&larr; Back to quiz builder</a>
        <header class="quiz-csv-header">
            <span class="quiz-csv-eyebrow">Question pool</span>
            <h1>Import questions from Excel, CSV, or QUIK STAK</h1>
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
                    <p>Download either template or upload an Excel, CSV, or QUIK STAK file. Review the preview before anything is imported.</p>
                </div>
                <div class="quiz-csv-template-links">
                    <a class="quiz-csv-template-link" href="{{ $templateUrl }}">Download Excel template</a>
                    <a class="quiz-csv-template-link" href="{{ $quikStakTemplateUrl }}">Download QUIK STAK template</a>
                </div>
            </div>

            <div class="quiz-csv-guidance">
                <h3>Prepare your file</h3>
                <p>Upload the filled-in Excel template (.xlsx), a UTF-8 CSV file, or a QUIK STAK text file (.txt). Files may be up to 2 MB and contain no more than 500 questions. Only the first sheet of a workbook is read. The preview checks the entire file before anything is saved.</p>
                <p>QUIK STAK starts with <code># QUIK STAK v1</code>, lists numbered questions with optional lettered choices, then ends with <code>=== ANSWER KEY ===</code> and one numbered answer per question. Add optional points as <code>@N</code>.</p>
                <p class="quiz-csv-columns"><strong>Excel and CSV columns:</strong> <code>type, question, choice_a, choice_b, choice_c, choice_d, correct_answer, points</code></p>
                <ul>
                    <li>Excel and CSV multiple choice answers use A-D letters or exact choice text. QUIK STAK answers use the letter of the matching choice. True/false accepts True or False.</li>
                    <li>Identification questions accept one correct answer. Matching trims the submitted answer, ignores case, and collapses repeated whitespace; it does not accept multiple answer variants.</li>
                    <li>For Excel and CSV, rephrase any answer starting with <code>-</code> to avoid spreadsheet formula injection.</li>
                    <li>Blank points and points of 0 count as 1. Points below 1 are normalized to 1 and called out in the preview. Uploads are limited to 2 MB and 500 questions.</li>
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
                <label class="quiz-csv-file-label" for="csv-file">Excel (.xlsx), CSV, or QUIK STAK (.txt) file</label>
                <input class="quiz-csv-file-input" id="csv-file" type="file" name="csv_file" accept=".xlsx,.csv,.txt,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv,text/plain" required>
                @error('csv_file')<small class="quiz-csv-field-error" role="alert">{{ $message }}</small>@enderror
                <button class="quiz-csv-primary-button" type="submit">Preview import</button>
            </form>
        </section>
    </main>
</x-faculty.layout>
