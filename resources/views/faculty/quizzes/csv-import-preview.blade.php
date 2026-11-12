<x-faculty.layout title="Preview CSV import | Upskill" data-faculty-page="quiz-csv-import-preview" use-app-css use-app-js>
    @php
        $validCount = count($preview['valid']);
        $duplicateCount = count($preview['duplicates']);
        $errorCount = count($preview['errors']);
    @endphp
    <main class="main quiz-csv-page quiz-csv-preview-page">
        <a class="quiz-csv-back-link" href="{{ $builderUrl }}">&larr; Back to quiz builder</a>
        <header class="quiz-csv-header">
            <span class="quiz-csv-eyebrow">Question pool</span>
            <h1>Preview CSV import</h1>
            <p><strong>{{ $course->title }}</strong><span aria-hidden="true"> · </span>{{ $quiz->title }}</p>
        </header>
        <p class="quiz-csv-intro">Review the rows below before saving. Identification accepts one answer; matching trims it, ignores case, and collapses repeated whitespace. Rephrase answers starting with <code>-</code> to avoid spreadsheet formula injection. Blank points and points of 0 count as 1. Limits: 2 MB and 500 data rows.</p>

        @if($errors->any())
            <div class="quiz-csv-alert" role="alert">
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <section class="quiz-csv-summary" aria-label="Import summary">
            <div class="quiz-csv-stat quiz-csv-stat--valid"><strong>{{ $validCount }}</strong><span>valid rows</span></div>
            <div class="quiz-csv-stat {{ $errorCount ? 'quiz-csv-stat--error' : '' }}"><strong>{{ $errorCount }}</strong><span>errors</span></div>
            <div class="quiz-csv-stat"><strong>{{ $duplicateCount }}</strong><span>duplicates</span></div>
            <div class="quiz-csv-stat"><strong>{{ $preview['pool_size'] }}</strong><span>current pool</span></div>
            <div class="quiz-csv-stat quiz-csv-stat--result"><strong id="resulting-pool-size">{{ $preview['pool_size'] + $validCount }}</strong><span>resulting pool</span></div>
        </section>
        <p class="quiz-csv-points-note">Points note: blank values become 1; 0 or negative values are normalized to 1. {{ $preview['points_normalized'] }} row(s) will be normalized.</p>

        @if($preview['valid'])
            <section class="quiz-csv-results-card" aria-labelledby="valid-rows-heading">
                <h2 id="valid-rows-heading">Valid rows</h2>
                <div class="quiz-csv-table-wrap"><table class="quiz-csv-table">
                    <thead><tr><th>Row</th><th>Question</th><th>Type</th><th>Answer</th><th>Points</th></tr></thead>
                    <tbody>@foreach($preview['valid'] as $item)
                        <tr><td>{{ $item['row'] }}</td><td>{{ $item['question']['question'] }}</td><td>{{ $item['question']['type'] }}</td><td>{{ $item['question']['correct_answer'] }}</td><td>{{ $item['question']['points'] }}</td></tr>
                    @endforeach</tbody>
                </table></div>
            </section>
        @endif

        @if($preview['duplicates'])
            <section class="quiz-csv-results-card" aria-labelledby="duplicate-rows-heading">
                <h2 id="duplicate-rows-heading">Duplicates <span>Skipped by default</span></h2>
                <div class="quiz-csv-table-wrap"><table class="quiz-csv-table">
                    <thead><tr><th>Row</th><th>Question</th><th>Reason</th></tr></thead>
                    <tbody>@foreach($preview['duplicates'] as $item)
                        <tr><td>{{ $item['row'] }}</td><td>{{ $item['question']['question'] }}</td><td>{{ $item['reason'] }}</td></tr>
                    @endforeach</tbody>
                </table></div>
            </section>
        @endif

        @if($preview['errors'])
            <section class="quiz-csv-results-card quiz-csv-errors-card" aria-labelledby="error-rows-heading">
                <h2 id="error-rows-heading">Rows with errors</h2>
                <ul>@foreach($preview['errors'] as $item)<li><strong>Row {{ $item['row'] }}:</strong> {{ $item['reason'] }}</li>@endforeach</ul>
            </section>
        @endif

        <form class="quiz-csv-confirm-card" method="POST" action="{{ $confirmUrl }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            @if($duplicateCount > 0)
                <label class="quiz-csv-option"><input type="checkbox" name="include_duplicates" value="1" id="include-duplicates"><span><strong>Include duplicate rows</strong><small>These are skipped by default to avoid adding the same question twice.</small></span></label>
            @endif
            @if($preview['replace_allowed'])
                <label class="quiz-csv-option"><input type="checkbox" name="replace" value="1" id="replace-mode"><span><strong>Replace the existing question pool</strong><small>Remove existing questions before adding the valid rows above.</small></span></label>
            @else
                <p class="quiz-csv-notice" role="note"><strong>Append-only for this quiz</strong><span>Replace mode is unavailable because students have already attempted this quiz. New valid rows will be appended.</span></p>
            @endif
            <div class="quiz-csv-form-actions">
                <a class="quiz-csv-secondary-button" href="{{ $uploadUrl }}">Upload a different CSV</a>
                <button class="quiz-csv-primary-button" type="submit">Confirm import</button>
            </div>
        </form>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var output = document.getElementById('resulting-pool-size');
            var duplicates = document.getElementById('include-duplicates');
            var replace = document.getElementById('replace-mode');
            if (!output) return;
            var current = {{ (int) $preview['pool_size'] }};
            var valid = {{ $validCount }};
            var duplicateCount = {{ $duplicateCount }};
            function updateSize() {
                output.textContent = (replace && replace.checked ? 0 : current) + valid + (duplicates && duplicates.checked ? duplicateCount : 0);
            }
            if (duplicates) duplicates.addEventListener('change', updateSize);
            if (replace) replace.addEventListener('change', updateSize);
            updateSize();
        });
    </script>
</x-faculty.layout>
