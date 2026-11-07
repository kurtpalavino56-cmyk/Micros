<?php

use App\Support\RichTextSanitizer;

test('safe table markup and table attributes survive sanitization', function () {
    $html = '<figure class="table"><table><caption>Plan</caption>'
        .'<thead><tr><th scope="col" colspan="2">Module</th></tr></thead>'
        .'<tbody><tr><td rowspan="2" colspan="1">Lesson</td><td>Activity</td></tr></tbody>'
        .'</table></figure>';

    $clean = RichTextSanitizer::sanitize($html);

    expect($clean)
        ->toContain('<figure class="table">')
        ->toContain('<table>')
        ->toContain('<caption>Plan</caption>')
        ->toContain('<thead>')
        ->toContain('<tbody>')
        ->toContain('<th scope="col" colspan="2">Module</th>')
        ->toContain('<td rowspan="2" colspan="1">Lesson</td>')
        ->not->toContain('table-scroll');
});

test('malicious table cell payloads are stripped', function () {
    $html = '<table><tbody><tr>'
        .'<td onclick="alert(1)" style="background:url(javascript:alert(1))">'
        .'<script>alert(1)</script>'
        .'<img src="x" onerror="alert(1)">'
        .'<a href="javascript:alert(1)">click</a>'
        .'<span style="position:fixed">safe</span>'
        .'</td>'
        .'</tr></tbody></table>';

    $clean = RichTextSanitizer::sanitize($html);

    expect($clean)
        ->not->toContain('<script')
        ->not->toContain('onerror')
        ->not->toContain('onclick')
        ->not->toContain('javascript:')
        ->not->toContain('style="')
        ->not->toContain('url(')
        ->toContain('<div class="table-scroll">')
        ->toContain('<td>')
        ->toContain('<span>safe</span>');
});

test('a bare table is wrapped in the shared scroll container', function () {
    $html = '<p>Before</p><table><tbody><tr><td>Cell</td></tr></tbody></table><p>After</p>';

    expect(RichTextSanitizer::sanitize($html))->toBe(
        '<p>Before</p><div class="table-scroll"><table><tbody><tr><td>Cell</td></tr></tbody></table></div><p>After</p>'
    );
});

test('a table inside figure.table is not wrapped again', function () {
    $html = '<figure class="table"><table><tbody><tr><td>Cell</td></tr></tbody></table></figure>';

    expect(RichTextSanitizer::sanitize($html))
        ->toBe($html)
        ->not->toContain('table-scroll');
});

test('re-wrapping already wrapped output stays single wrapped', function () {
    $html = '<table><tbody><tr><td>Cell</td></tr></tbody></table>';

    $once = RichTextSanitizer::sanitize($html);

    expect(RichTextSanitizer::wrapTables($once))->toBe($once)
        ->and(RichTextSanitizer::sanitize($once))->toBe($once);
});

test('figure classes keep only known safe values', function () {
    $html = '<figure class="table evil"><table><tr><td>Cell</td></tr></table></figure>'
        .'<figure class="image image-style-side"><img src="/x.png" alt="x"></figure>'
        .'<figure class="upskill-pdf"><oembed url="https://example.com/a.pdf"></oembed></figure>'
        .'<figure class="javascript:alert(1)"><img src="/y.png" alt="y"></figure>';

    $clean = RichTextSanitizer::sanitize($html);

    expect($clean)
        ->toContain('<figure class="table">')
        ->toContain('<figure class="image image-style-side">')
        ->toContain('<figure class="upskill-pdf">')
        ->toContain('<figure><img src="/y.png" alt="y"></figure>')
        ->not->toContain('evil')
        ->not->toContain('javascript:');
});

test('table attributes with unsafe values are dropped', function () {
    $html = '<table><tr><td colspan="nope" rowspan="0" scope="all">Cell</td></tr></table>';

    $clean = RichTextSanitizer::sanitize($html);

    expect($clean)
        ->not->toContain('colspan')
        ->not->toContain('rowspan')
        ->not->toContain('scope')
        ->toContain('<td>Cell</td>');
});

test('legacy plain text keeps every line and is escaped before wrapping', function () {
    $legacy = "Line one\nLine two\n- Bullet A & B\n- <script>alert(1)</script>";

    $html = RichTextSanitizer::toHtml($legacy);

    expect($html)->toBe(
        '<p>Line one<br>Line two<br>- Bullet A &amp; B<br>- &lt;script&gt;alert(1)&lt;/script&gt;</p>'
    );
    expect($html)->not->toContain('<script>');
});

test('legacy plain text with windows and bare carriage returns keeps its breaks', function () {
    $html = RichTextSanitizer::toHtml("One\r\nTwo\rThree");

    expect($html)->toBe('<p>One<br>Two<br>Three</p>');
});

test('html values pass through sanitization without double escaping', function () {
    $html = RichTextSanitizer::toHtml('<p>A &amp; B</p><table><tr><td>Cell</td></tr></table>');

    expect($html)
        ->toBe('<p>A &amp; B</p><div class="table-scroll"><table><tr><td>Cell</td></tr></table></div>');
});

test('blank values convert to an empty string', function () {
    expect(RichTextSanitizer::toHtml(null))->toBe('')
        ->and(RichTextSanitizer::toHtml("  \n "))->toBe('');
});

test('non-table rich text is unchanged by the widened allowlist', function () {
    $html = '<h2>Heading</h2><p><strong>Bold</strong> and <em>italic</em></p>'
        .'<ul><li>One</li><li>Two</li></ul>'
        .'<blockquote>Quote</blockquote>';

    expect(RichTextSanitizer::sanitize($html))->toBe($html);
});
