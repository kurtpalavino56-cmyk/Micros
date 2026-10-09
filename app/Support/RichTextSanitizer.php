<?php

namespace App\Support;

class RichTextSanitizer
{
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
            .'<figure><figcaption><oembed>';

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

                    if (in_array($name, ['href', 'src', 'url'], true)) {
                        $value = trim($value);

                        if (preg_match('/^(javascript|vbscript|data):/i', $value)) {
                            continue;
                        }

                        if (in_array($name, ['src', 'href'], true) && ! preg_match('/^(https?:\/\/|\/)/i', $value)) {
                            continue;
                        }
                    }

                    $safe[] = $name.'="'.e($value).'"';
                }

                return '<'.$tag.($safe ? ' '.implode(' ', $safe) : '').'>';
            },
            $html
        );

        return trim($html);
    }
}
