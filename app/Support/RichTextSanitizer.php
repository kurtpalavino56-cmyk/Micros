<?php

namespace App\Support;

class RichTextSanitizer
{
    /**
     * Convert editor input to safe HTML.
     *
     * Values written before the rich-text editors were introduced are plain
     * text. They are escaped and their newlines become <br> so no line is
     * lost; values that already contain markup are sanitised as usual.
     */
    public static function toHtml(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        if (self::containsHtml($value)) {
            return self::sanitize($value);
        }

        $escaped = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');

        return '<p>'.implode('<br>', preg_split('/\r\n|\r|\n/', $escaped) ?: [$escaped]).'</p>';
    }

    private static function containsHtml(string $value): bool
    {
        return preg_match(
            '/<\s*\/?\s*(?:p|br|ul|ol|li|h[1-6]|strong|b|em|i|u|s|del|a|img|table|thead|tbody|tr|th|td|caption|figure|figcaption|blockquote|pre|code|span|div|oembed)\b/i',
            $value
        ) === 1;
    }

    public static function sanitize(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $allowed = '<p><br>'
            .'<strong><b><em><i><u><s><del>'
            .'<sub><sup><span>'
            .'<h1><h2><h3><h4>'
            .'<ul><ol><li>'
            .'<blockquote><pre><code>'
            .'<a><img>'
            .'<figure><figcaption><oembed>'
            .'<table><thead><tbody><tr><th><td><caption>';

        $html = strip_tags($html, $allowed);

        $html = preg_replace_callback(
            '/<([a-z0-9]+)\b([^>]*)>/i',
            function (array $match): string {
                $tag = strtolower($match[1]);
                $attributes = $match[2] ?? '';
                $allowedAttributes = match ($tag) {
                    'a' => ['href', 'target', 'rel', 'title', 'class', 'data-file-name', 'data-file-type'],
                    'img' => ['src', 'alt', 'title', 'width', 'height'],
                    'figure' => ['class', 'data-file-name'],
                    'figcaption' => ['class'],
                    'oembed' => ['url'],
                    'td', 'th' => ['colspan', 'rowspan', 'scope'],
                    default => [],
                };

                preg_match_all(
                    '/([a-zA-Z_:][-a-zA-Z0-9_:.]*)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/u',
                    $attributes,
                    $parts,
                    PREG_SET_ORDER
                );

                $safe = [];

                foreach ($parts as $part) {
                    $name = strtolower($part[1]);

                    if ($name === 'style') {
                        if (! in_array($tag, ['p', 'h1', 'h2', 'h3', 'h4', 'span'], true)) {
                            continue;
                        }

                        $value = $part[2] !== '' ? $part[2] : ($part[3] !== '' ? $part[3] : $part[4]);
                        $safeStyles = [];

                        foreach (explode(';', trim($value)) as $declaration) {
                            $styleParts = explode(':', $declaration, 2);
                            if (count($styleParts) !== 2) {
                                continue;
                            }

                            $property = strtolower(trim($styleParts[0]));
                            $styleValue = trim($styleParts[1]);

                            if ($property === 'text-align' && preg_match('/^(left|center|right|justify)$/i', $styleValue)) {
                                $safeStyles[] = 'text-align:'.strtolower($styleValue);
                            } elseif (in_array($property, ['color', 'background-color'], true)
                                && preg_match('/^(#[0-9a-f]{3,8}|(?:rgb|hsl)a?\([0-9.%+,\s-]+\))$/i', $styleValue)) {
                                $safeStyles[] = $property.':'.$styleValue;
                            }
                        }

                        if ($safeStyles) {
                            $safe[] = 'style="'.e(implode(';', $safeStyles)).'"';
                        }

                        continue;
                    }

                    if (! in_array($name, $allowedAttributes, true)) {
                        continue;
                    }

                    $value = $part[2] !== '' ? $part[2] : ($part[3] !== '' ? $part[3] : $part[4]);

                    if ($tag === 'figure' && $name === 'class') {
                        $classes = preg_split('/\s+/', trim($value)) ?: [];
                        $classes = array_values(array_filter(
                            $classes,
                            static fn (string $class): bool => in_array($class, ['table', 'image', 'media', 'upskill-pdf', 'ck-widget'], true)
                                || str_starts_with($class, 'image-style-')
                        ));

                        if ($classes === []) {
                            continue;
                        }

                        $value = implode(' ', $classes);
                    }

                    if (in_array($name, ['colspan', 'rowspan'], true)) {
                        if (! preg_match('/^\d{1,4}$/', $value) || (int) $value < 1 || (int) $value > 1000) {
                            continue;
                        }
                    }

                    if ($name === 'scope') {
                        if (! preg_match('/^(col|row|colgroup|rowgroup)$/i', $value)) {
                            continue;
                        }

                        $value = strtolower($value);
                    }

                    if (in_array($name, ['href', 'src', 'url'], true)) {
                        $value = trim($value);

                        if (preg_match('/^(javascript|vbscript|data):/i', $value)) {
                            continue;
                        }

                        if (! preg_match('/^(https?:\/\/|\/)/i', $value)) {
                            continue;
                        }
                    }

                    $safe[] = $name.'="'.e($value).'"';
                }

                return '<'.$tag.($safe ? ' '.implode(' ', $safe) : '').'>';
            },
            $html
        );

        return self::wrapTables(trim($html));
    }

    /**
     * Put every bare <table> in a scroll container.
     *
     * Rich-text tables are commonly stored as bare <table> elements (legacy
     * content, sanitised saves). CSS alone cannot scroll a table with
     * overflow-x unless it is display:block, so a wrapper gives every table
     * a scroll box without touching the table markup itself.
     *
     * Tables already inside <figure class="table"> keep the figure as their
     * scroll container and are not wrapped again.
     */
    public static function wrapTables(?string $html): string
    {
        $html = (string) $html;

        if (stripos($html, '<table') === false) {
            return $html;
        }

        $protected = [];

        $html = preg_replace_callback(
            '/<figure\b[^>]*\bclass\s*=\s*(?:"[^"]*\btable\b[^"]*"|\'[^\']*\btable\b[^\']*\')[^>]*>.*?<\/figure>/is',
            function (array $match) use (&$protected): string {
                $placeholder = "\x1A".'WRAP'.count($protected)."\x1A";
                $protected[$placeholder] = $match[0];

                return $placeholder;
            },
            $html
        ) ?? $html;

        $html = preg_replace_callback(
            '/<div\b[^>]*\bclass\s*=\s*(?:"[^"]*\btable-scroll\b[^"]*"|\'[^\']*\btable-scroll\b[^\']*\')[^>]*>.*?<\/div>/is',
            function (array $match) use (&$protected): string {
                $placeholder = "\x1A".'WRAP'.count($protected)."\x1A";
                $protected[$placeholder] = $match[0];

                return $placeholder;
            },
            $html
        ) ?? $html;

        $html = preg_replace(
            '/(<table\b[^>]*>.*?<\/table>)/is',
            '<div class="table-scroll">$1</div>',
            $html
        ) ?? $html;

        return $protected === [] ? $html : strtr($html, $protected);
    }
}
