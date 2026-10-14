<?php

namespace App\Services\Citations;

/**
 * Source markers in answer text: "[1]", "[2]" refer to the numbered sources of the same message.
 *
 * Not matched: checkboxes "[ ]" / "[x]", labels like "[NÄCHSTE SCHRITTE]" or "[SVNr geschwärzt]",
 * and markdown links "[1](https://…)".
 */
class CitationMarkers
{
    public const PATTERN = '/\[(\d{1,2})\](?!\()/u';

    /**
     * @return list<int> unique source numbers in order of first appearance
     */
    public static function extract(string $text): array
    {
        preg_match_all(self::PATTERN, $text, $matches);

        return array_values(array_unique(array_map('intval', $matches[1])));
    }

    public static function strip(string $text): string
    {
        return (string) preg_replace('/[ \t]?\[(\d{1,2})\](?!\()/u', '', $text);
    }

    /**
     * Turn valid markers in rendered HTML into superscript links to the message's source list.
     * Markers that point to no offered source are removed.
     *
     * @param  list<int>  $validNumbers
     */
    public static function linkifyHtml(string $html, array $validNumbers, string $anchorPrefix, string $locale = 'de'): string
    {
        return (string) preg_replace_callback('/[ \t]?\[(\d{1,2})\](?!\()/u', function (array $match) use ($validNumbers, $anchorPrefix, $locale) {
            $number = (int) $match[1];

            if (! in_array($number, $validNumbers, true)) {
                return '';
            }

            return '<sup class="ms-0.5"><a href="#'.e($anchorPrefix).'-'.$number.'" class="font-semibold text-kp-textred no-underline hover:underline dark:text-kp-bluegrey" aria-label="'.e(__('answer.source_n', ['n' => $number], $locale)).'">'.$number.'</a></sup>';
        }, $html);
    }
}
