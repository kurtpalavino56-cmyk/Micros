<x-faculty.layout title="Preview CSV import | Upskill" data-faculty-page="quiz-csv-import-preview">
    @php
        $validCount = count($preview['valid']);
        $duplicateCount = count($preview['duplicates']);
        $errorCount = count($preview['errors']);
    @endphp
    <main class="container" style="max-width:1100px;padding:32px 16px;">
        <p><a href="{{ $builderUrl }}">&larr; Back to quiz builder</a></p>
        <h1>Preview CSV import</h1>
        <p>Identification accepts one answer; matching trims it, ignores case, and collapses repeated whitespace. Rephrase answers starting with <code>-</code> to avoid spreadsheet formula injection. Blank points and points of 0 count as 1. Limits: 2 MB and 500 data rows.</p>
        <p><strong>{{ $course->title }}</strong> · {{ $quiz->title }}</p>

        @if($errors->any())
            <div role="alert" style="padding:12px;border:1px solid #b42318;background:#fee4e2;color:#7a1a12;border-radius:8px;margin:16px 0;">
                <ul style="margin:0;padding-left:20px;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <section aria-label="Import summary" style="display:flex;flex-wrap:wrap;gap:18px;padding:16px;background:#f3f6fc;border-radius:10px;">
            <strong>{{ $validCount }} valid</strong>
            <strong>{{ $errorCount }} errors</strong>
            <strong>{{ $duplicateCount }} duplicates</strong>
            <strong>Current pool: {{ $preview['pool_size'] }}</strong>
            <strong>Resulting pool: <span id="resulting-pool-size">{{ $preview['pool_size'] + $validCount }}</span></strong>
        </section>
        <p>Points note: blank values become 1; 0 or negative values are normalized to 1. {{ $preview['points_normalized'] }} row(s) will be normalized.</p>

        @if($preview['valid'])
            <h2>Valid rows</h2>
            <div style="overflow-x:auto;"><table style="width:100%;border-collapse:collapse;">
                <thead><tr><th>Row</th><th>Question</th><th>Type</th><th>Answer</th><th>Points</th></tr></thead>
                <tbody>@foreach($preview['valid'] as $item)
                    <tr><td>{{ $item['row'] }}</td><td>{{ $item['question']['question'] }}</td><td>{{ $item['question']['type'] }}</td><td>{{ $item['question']['correct_answer'] }}</td><td>{{ $item['question']['points'] }}</td></tr>
                @endforeach</tbody>
            </table></div>
        @endif

        @if($preview['duplicates'])
            <h2>Duplicates (skipped by default)</h2>
            <div style="overflow-x:auto;"><table style="width:100%;border-collapse:collapse;">
                <thead><tr><th>Row</th><th>Question</th><th>Reason</th></tr></thead>
                <tbody>@foreach($preview['duplicates'] as $item)
                    <tr><td>{{ $item['row'] }}</td><td>{{ $item['question']['question'] }}</td><td>{{ $item['reason'] }}</td></tr>
                @endforeach</tbody>
            </table></div>
        @endif

        @if($preview['errors'])
            <h2>Rows with errors</h2>
            <ul>@foreach($preview['errors'] as $item)<li>Row {{ $item['row'] }}: {{ $item['reason'] }}</li>@endforeach</ul>
        @endif

        <form method="POST" action="{{ $confirmUrl }}" style="display:grid;gap:12px;margin-top:24px;">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            @if($duplicateCount > 0)
                <label><input type="checkbox" name="include_duplicates" value="1" id="include-duplicates"> Include duplicate rows</label>
            @endif
            @if($preview['replace_allowed'])
                <label><input type="checkbox" name="replace" value="1" id="replace-mode"> Replace the existing pool</label>
            @else
                <p role="note">Replace mode is unavailable because students have already attempted this quiz. New valid rows will be appended.</p>
            @endif
            <button class="btn-save-lesson" type="submit">Confirm import</button>
        </form>
        <p><a href="{{ $uploadUrl }}">Upload a different CSV</a></p>
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
