<?php

namespace App\Services\AustrianGov;

use App\Enums\Language;
use App\Services\Citations\CitationMarkers;
use App\Support\Translate;
use Illuminate\Support\Str;

class MessageParser
{
    /**
     * Parse raw markdown assistant content into structured blocks (prose, steps, checklist).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function parse(string $content): array
    {
        $parsed = self::parseWithNextSteps($content);

        return $parsed['blocks'];
    }

    public const STEP_PATTERN = '/^(\d+)\.\s+(?:\*\*([^*]+)\*\*[:\s-]*)?(.*)/';

    public const CHECKLIST_PATTERN = '/^(?:-\s*\[([ xX]?)\]|\*|-)\s+(?:\*\*([^*]+)\*\*[:\s-]*)?(.*)/';

    /**
     * Canonical labels the prompt asks for in every language, plus legacy German ones from older answers.
     * Translated labels of all languages (answer.field_*) are accepted too, in case a model translates them.
     */
    public const FIELD_LABELS = [
        'where' => ['Where', 'Wo'],
        'deadline' => ['Deadline', 'Frist'],
        'documents' => ['Documents', 'Dokumente', 'Unterlagen'],
    ];

    /**
     * Words meaning "no documents" (canonical "none", legacy "keine"), plus every language's answer.documents_none.
     */
    public const NO_DOCUMENT_WORDS = ['none', 'no', 'keine', 'nein'];

    /**
     * @var array{pattern: string, keys: array<string, string>, none: list<string>}|null
     */
    protected static ?array $fieldCache = null;

    public const CLARIFY_MARKER = '/^\[(?:RÜCKFRAGE|RUECKFRAGE|CLARIFY)\]\s*$/iu';

    /**
     * Parse markdown assistant content into blocks AND extracted next-step buttons.
     *
     * A reply that starts with [RÜCKFRAGE] / [CLARIFY] is a clarification (CI phase 1) and returns
     * kind "clarify" with its intro and the listed concerns instead of blocks.
     *
     * Steps may carry source markers [n] and the fields where / deadline / documents (null when the
     * answer did not use the field). Old content without these parses exactly as before.
     *
     * @return array{
     *     kind: 'answer'|'clarify',
     *     clarify?: array{intro: string, intro_html: string, items: list<string>},
     *     blocks: array<int, array<string, mixed>>,
     *     next_steps: array<int, string>
     * }
     */
    public static function parseWithNextSteps(string $content, string $locale = 'de'): array
    {
        $content = str_replace("\r\n", "\n", $content);

        if ($clarify = self::parseClarification($content)) {
            return ['kind' => 'clarify', 'clarify' => $clarify, 'blocks' => [], 'next_steps' => []];
        }

        $content = (string) preg_replace('/^\s*\[(?:ANTWORT|ANSWER)\]\s*\n/iu', '', $content);

        // Source lists written by the model ("[1]: …" or "[1] title – https://…") are replaced by the app's own list.
        $content = (string) preg_replace('/^[ \t]*\[\d{1,2}\](?::[ \t].*|[ \t].*https?:\/\/\S+.*)$\n?/mu', '', $content);

        $nextSteps = [];

        // 1. Extract explicit [NÄCHSTE SCHRITTE] or [NEXT STEPS] or ### Nächste Schritte block
        $pattern = "/(?:\[(?:NÄCHSTE SCHRITTE|NEXT STEPS)\]|#{2,4}\s*(?:Nächste Schritte|Next Steps|Empfohlene nächste Schritte|Recommended Next Steps))\s*(.*?)(?=\n#{1,4}|\z)/si";

        if (preg_match($pattern, $content, $m)) {
            $lines = explode("\n", trim($m[1]));
            foreach ($lines as $line) {
                if (preg_match('/^(?:-|\*|\d+\.)\s+(.+)/', trim($line), $lm)) {
                    $cleaned = trim(CitationMarkers::strip($lm[1]), " \t\n\r\0\x0B*");
                    if (! empty($cleaned)) {
                        $nextSteps[] = $cleaned;
                    }
                }
            }
            $content = trim(str_replace($m[0], '', $content));
        }

        // 2. If no explicit next steps were provided by the model, generate contextual smart next steps
        if (empty($nextSteps)) {
            $nextSteps = self::inferContextualNextSteps($content, $locale);
        }

        $lines = explode("\n", $content);
        $lineCount = count($lines);
        $blocks = [];
        $currentProse = '';

        for ($i = 0; $i < $lineCount; $i++) {
            $line = $lines[$i];
            $trimmed = trim($line);

            // Detect Numbered Step (e.g. "1. **Title** [1]" or "1. **Title**: Detail" or "1. Title")
            if (preg_match(self::STEP_PATTERN, $trimmed)) {
                $heading = self::extractPrecedingHeading($currentProse);

                if (trim($currentProse) !== '') {
                    $blocks[] = self::proseBlock($currentProse);
                    $currentProse = '';
                }

                $stepItems = [];
                while ($i < $lineCount && preg_match(self::STEP_PATTERN, CitationMarkers::strip(trim($lines[$i])), $sm)) {
                    $hasBold = ! empty($sm[2]);

                    $item = [
                        'number' => (int) $sm[1],
                        'title' => $hasBold ? trim($sm[2]) : trim($sm[3]),
                        'detail' => $hasBold ? trim($sm[3]) : '',
                        'where' => null,
                        'deadline' => null,
                        'documents' => null,
                        'sources' => CitationMarkers::extract($lines[$i]),
                    ];
                    $i++;

                    $i = self::absorbStepDetails($lines, $i, $item);
                    $stepItems[] = $item;
                }
                $i--; // compensate loop increment

                $blocks[] = [
                    'type' => 'steps',
                    'heading' => $heading,
                    'structured' => collect($stepItems)->contains(fn (array $s) => $s['where'] !== null || $s['deadline'] !== null || $s['documents'] !== null),
                    'items' => $stepItems,
                ];

                continue;
            }

            // Detect Checklist / Task Item (e.g. "- [ ] **Title**: Detail" or "* **Title**: Detail")
            if (preg_match(self::CHECKLIST_PATTERN, $trimmed, $cm)) {
                $hasBold = ! empty($cm[2]);
                $hasTaskBox = $cm[1] !== '';

                if ($hasBold || $hasTaskBox) {
                    $heading = self::extractPrecedingHeading($currentProse);

                    if (trim($currentProse) !== '') {
                        $blocks[] = self::proseBlock($currentProse);
                        $currentProse = '';
                    }

                    $checkItems = [];
                    while ($i < $lineCount && preg_match(self::CHECKLIST_PATTERN, CitationMarkers::strip(trim($lines[$i])), $scm)) {
                        $sources = CitationMarkers::extract($lines[$i]);
                        $title = ! empty($scm[2]) ? trim($scm[2]) : trim($scm[3]);
                        $detail = ! empty($scm[2]) ? trim($scm[3]) : '';
                        $isChecked = strtolower($scm[1]) === 'x';

                        $checkItems[] = [
                            'id' => Str::slug($title),
                            'title' => $title,
                            'detail' => $detail,
                            'checked' => $isChecked,
                            'sources' => $sources,
                        ];
                        $i++;
                    }
                    $i--; // compensate loop increment

                    $blocks[] = [
                        'type' => 'checklist',
                        'heading' => $heading,
                        'items' => $checkItems,
                    ];

                    continue;
                }
            }

            $currentProse .= $line."\n";
        }

        if (trim($currentProse) !== '') {
            $blocks[] = self::proseBlock($currentProse);
        }

        return [
            'kind' => 'answer',
            'blocks' => $blocks,
            'next_steps' => array_values(array_unique(array_slice($nextSteps, 0, 4))),
        ];
    }

    /**
     * Render model markdown without letting raw HTML or unsafe links through.
     */
    public static function markdown(string $markdown): string
    {
        return Str::markdown($markdown, ['html_input' => 'strip', 'allow_unsafe_links' => false]);
    }

    /**
     * @return array{intro: string, intro_html: string, items: list<string>}|null
     */
    protected static function parseClarification(string $content): ?array
    {
        $lines = explode("\n", trim($content));

        if (! preg_match(self::CLARIFY_MARKER, trim($lines[0]))) {
            return null;
        }

        $intro = [];
        $items = [];

        foreach (array_slice($lines, 1) as $line) {
            $trimmed = trim($line);

            if (preg_match('/^\[(?:NÄCHSTE SCHRITTE|NEXT STEPS)\]/iu', $trimmed)) {
                break;
            }

            if ($trimmed === '') {
                continue;
            }

            if (preg_match('/^(?:\d+\.|[-*])\s+(.+)$/u', $trimmed, $m)) {
                $items[] = trim(str_replace('**', '', CitationMarkers::strip($m[1])));

                continue;
            }

            $intro[] = CitationMarkers::strip($trimmed);
        }

        $introText = implode("\n", $intro);

        return ['intro' => $introText, 'intro_html' => self::markdown($introText), 'items' => $items];
    }

    /**
     * Read the labelled and indented lines that belong to the step just parsed.
     * A blank line only ends the step list when no further numbered step follows.
     *
     * @param  array<int, string>  $lines
     * @param  array<string, mixed>  $item
     * @return int index of the first line that does not belong to the step
     */
    protected static function absorbStepDetails(array $lines, int $i, array &$item): int
    {
        $lineCount = count($lines);
        $lastField = null;

        while ($i < $lineCount) {
            $line = $lines[$i];

            if (trim($line) === '') {
                $next = $i + 1;
                while ($next < $lineCount && trim($lines[$next]) === '') {
                    $next++;
                }

                $continuesSteps = $next < $lineCount && preg_match(self::STEP_PATTERN, trim($lines[$next]));

                return $continuesSteps ? $next : $i;
            }

            if (preg_match(self::fields()['pattern'], $line, $fm)) {
                $item['sources'] = array_values(array_unique([...$item['sources'], ...CitationMarkers::extract($fm[2])]));
                $value = trim(CitationMarkers::strip($fm[2]), " \t*");
                $lastField = self::fieldKey($fm[1]);

                if ($lastField === 'documents') {
                    $item['documents'] = self::splitDocuments($value);
                } else {
                    $item[$lastField] = $value;
                }

                $i++;

                continue;
            }

            // Indented continuation (e.g. document bullets under "Dokumente:" or wrapped detail text).
            if (preg_match('/^\s{2,}\S/', $line) && ! preg_match(self::STEP_PATTERN, trim($line))) {
                $item['sources'] = array_values(array_unique([...$item['sources'], ...CitationMarkers::extract($line)]));
                $text = trim(CitationMarkers::strip($line));

                if ($lastField === 'documents' && preg_match('/^[-*]\s+(.+)$/u', $text, $dm)) {
                    $item['documents'] = [...($item['documents'] ?? []), ...self::splitDocuments(trim($dm[1], ' *'))];
                } else {
                    $item['detail'] = trim($item['detail'].' '.ltrim($text, '-* '));
                }

                $i++;

                continue;
            }

            return $i;
        }

        return $i;
    }

    protected static function fieldKey(string $label): string
    {
        return self::fields()['keys'][mb_strtolower($label)] ?? 'documents';
    }

    /**
     * Field-label regex and lookup built once from the canonical, legacy and all translated labels.
     *
     * @return array{pattern: string, keys: array<string, string>, none: list<string>}
     */
    protected static function fields(): array
    {
        if (self::$fieldCache !== null) {
            return self::$fieldCache;
        }

        $keys = [];
        $none = self::NO_DOCUMENT_WORDS;

        foreach (self::FIELD_LABELS as $field => $labels) {
            foreach ($labels as $label) {
                $keys[mb_strtolower($label)] = $field;
            }
        }

        foreach (Language::cases() as $language) {
            foreach (['where', 'deadline', 'documents'] as $field) {
                $keys[mb_strtolower(Translate::text("answer.field_{$field}", [], $language->value))] = $field;
            }
            $none[] = mb_strtolower(Translate::text('answer.documents_none', [], $language->value));
        }

        $alternatives = implode('|', array_map(fn (string $label) => preg_quote($label, '/'), array_keys($keys)));

        return self::$fieldCache = [
            'pattern' => '/^\s*(?:[-*]\s+)?\**('.$alternatives.')\**\s*:\**\s*(.*)$/iu',
            'keys' => $keys,
            'none' => array_values(array_unique($none)),
        ];
    }

    /**
     * @return list<string> an empty list means the answer said explicitly that no documents are needed
     */
    protected static function splitDocuments(string $value): array
    {
        if ($value === '' || in_array(mb_strtolower(rtrim($value, '.')), self::fields()['none'], true)) {
            return [];
        }

        return array_values(array_filter(array_map(fn (string $doc) => trim($doc, " \t*."), preg_split('/\s*;\s*/u', $value) ?: [])));
    }

    /**
     * @return array{type: 'prose', content: string, html: string, sources: list<int>}
     */
    protected static function proseBlock(string $prose): array
    {
        $prose = trim($prose);

        return [
            'type' => 'prose',
            'content' => $prose,
            'html' => self::markdown($prose),
            'sources' => CitationMarkers::extract($prose),
        ];
    }

    /**
     * Infer contextual next step follow-up questions based on the content of the response.
     *
     * @return array<int, string>
     */
    public static function inferContextualNextSteps(string $content, string $locale = 'de'): array
    {
        $lower = mb_strtolower($content);

        $topic = match (true) {
            // Residence / Moving / Meldezettel / Residence Permit (Aufenthaltstitel)
            str_contains($lower, 'wohnsitz') || str_contains($lower, 'meld') || str_contains($lower, 'umzug') || str_contains($lower, 'residence') || str_contains($lower, 'aufenthalt') || str_contains($lower, 'permit') => 'residence',
            // Passport / ID Card
            str_contains($lower, 'pass') || str_contains($lower, 'ausweis') || str_contains($lower, 'passport') => 'passport',
            str_contains($lower, 'id austria') || str_contains($lower, 'signatur') => 'id_austria',
            default => 'general',
        };

        $steps = __("answer.inferred.{$topic}", [], $locale);

        return is_array($steps) ? array_values(array_filter($steps, 'is_string')) : [];
    }

    /**
     * Extract and remove the trailing markdown heading from prose if present.
     */
    protected static function extractPrecedingHeading(string &$prose): ?string
    {
        $lines = explode("\n", trim($prose));
        $lastLine = end($lines);

        if (preg_match('/^#{2,6}\s*(.+)/', trim($lastLine), $m)) {
            $heading = trim($m[1]);
            array_pop($lines);
            $prose = implode("\n", $lines);

            return $heading;
        }

        return null;
    }
}
