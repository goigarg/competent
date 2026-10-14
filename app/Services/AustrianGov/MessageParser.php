<?php

namespace App\Services\AustrianGov;

use Illuminate\Support\Str;

class MessageParser
{
    /**
     * Parse raw markdown assistant content into structured blocks (prose, steps, checklist).
     *
     * @return array<int, array{
     *     type: 'prose'|'steps'|'checklist',
     *     content?: string,
     *     html?: string,
     *     heading?: ?string,
     *     items?: array<int, array<string, mixed>>
     * }>
     */
    public static function parse(string $content): array
    {
        $parsed = self::parseWithNextSteps($content);

        return $parsed['blocks'];
    }

    /**
     * Parse markdown assistant content into blocks AND extracted next-step buttons.
     *
     * @return array{
     *     blocks: array<int, array{type: 'prose'|'steps'|'checklist', content?: string, html?: string, heading?: ?string, items?: array<int, array<string, mixed>>}>,
     *     next_steps: array<int, string>
     * }
     */
    public static function parseWithNextSteps(string $content, string $locale = 'de'): array
    {
        $nextSteps = [];

        // 1. Extract explicit [NÄCHSTE SCHRITTE] or [NEXT STEPS] or ### Nächste Schritte block
        $pattern = "/(?:\[(?:NÄCHSTE SCHRITTE|NEXT STEPS)\]|#{2,4}\s*(?:Nächste Schritte|Next Steps|Empfohlene nächste Schritte|Recommended Next Steps))\s*(.*?)(?=\n#{1,4}|\z)/si";

        if (preg_match($pattern, $content, $m)) {
            $lines = explode("\n", trim($m[1]));
            foreach ($lines as $line) {
                if (preg_match('/^(?:-|\*|\d+\.)\s+(.+)/', trim($line), $lm)) {
                    $cleaned = trim($lm[1], " \t\n\r\0\x0B*");
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
        $blocks = [];
        $currentProse = '';

        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            $trimmed = trim($line);

            // Detect Numbered Step (e.g. "1. **Title**: Detail" or "1. Title")
            if (preg_match('/^(\d+)\.\s+(?:\*\*([^*]+)\*\*[:\s-]*)?(.*)/', $trimmed, $m)) {
                $heading = self::extractPrecedingHeading($currentProse);

                if (trim($currentProse) !== '') {
                    $blocks[] = [
                        'type' => 'prose',
                        'content' => trim($currentProse),
                        'html' => Str::markdown(trim($currentProse)),
                    ];
                    $currentProse = '';
                }

                $stepItems = [];
                while ($i < count($lines) && preg_match('/^(\d+)\.\s+(?:\*\*([^*]+)\*\*[:\s-]*)?(.*)/', trim($lines[$i]), $sm)) {
                    $title = ! empty($sm[2]) ? trim($sm[2]) : trim($sm[3]);
                    $detail = ! empty($sm[2]) ? trim($sm[3]) : '';

                    $stepItems[] = [
                        'number' => (int) $sm[1],
                        'title' => $title,
                        'detail' => $detail,
                    ];
                    $i++;
                }
                $i--; // compensate loop increment

                $blocks[] = [
                    'type' => 'steps',
                    'heading' => $heading,
                    'items' => $stepItems,
                ];

                continue;
            }

            // Detect Checklist / Task Item (e.g. "- [ ] **Title**: Detail" or "* **Title**: Detail")
            if (preg_match('/^(?:-\s*\[([ xX]?)\]|\*|-)\s+(?:\*\*([^*]+)\*\*[:\s-]*)?(.*)/', $trimmed, $cm)) {
                $hasBold = ! empty($cm[2]);
                $hasTaskBox = isset($cm[1]) && $cm[1] !== '';

                if ($hasBold || $hasTaskBox) {
                    $heading = self::extractPrecedingHeading($currentProse);

                    if (trim($currentProse) !== '') {
                        $blocks[] = [
                            'type' => 'prose',
                            'content' => trim($currentProse),
                            'html' => Str::markdown(trim($currentProse)),
                        ];
                        $currentProse = '';
                    }

                    $checkItems = [];
                    while ($i < count($lines) && preg_match('/^(?:-\s*\[([ xX]?)\]|\*|-)\s+(?:\*\*([^*]+)\*\*[:\s-]*)?(.*)/', trim($lines[$i]), $scm)) {
                        $title = ! empty($scm[2]) ? trim($scm[2]) : trim($scm[3]);
                        $detail = ! empty($scm[2]) ? trim($scm[3]) : '';
                        $isChecked = strtolower($scm[1] ?? '') === 'x';

                        $checkItems[] = [
                            'id' => Str::slug($title),
                            'title' => $title,
                            'detail' => $detail,
                            'checked' => $isChecked,
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
            $blocks[] = [
                'type' => 'prose',
                'content' => trim($currentProse),
                'html' => Str::markdown(trim($currentProse)),
            ];
        }

        return [
            'blocks' => $blocks,
            'next_steps' => array_values(array_unique(array_slice($nextSteps, 0, 4))),
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
        $isGerman = str_starts_with(strtolower($locale), 'de');

        // Case 1: Residence / Moving / Meldezettel / Residence Permit (Aufenthaltstitel)
        if (str_contains($lower, 'wohnsitz') || str_contains($lower, 'meld') || str_contains($lower, 'umzug') || str_contains($lower, 'residence') || str_contains($lower, 'aufenthalt') || str_contains($lower, 'permit')) {
            if ($isGerman) {
                return [
                    'Haben Sie bereits einen gültigen Reisepass?',
                    'Wie melde ich mich bei der Krankenversicherung (ÖGK) an?',
                    'Benötige ich die Unterschrift meiner Vermieterin?',
                    'Dossier für das Meldeamt erstellen',
                ];
            }

            return [
                'Do you already have a valid passport?',
                'How do I apply for health insurance (ÖGK)?',
                'Do I need a landlord confirmation signature?',
                'Generate dossier for the registration office',
            ];
        }

        // Case 2: Passport / ID Card
        if (str_contains($lower, 'pass') || str_contains($lower, 'ausweis') || str_contains($lower, 'passport')) {
            if ($isGerman) {
                return [
                    'Haben Sie bereits ein biometrisches Passfoto (35x45mm)?',
                    'Brauchen Sie einen Expresspass (2–3 Tage)?',
                    'Wie buche ich einen Online-Termin beim Bezirksamt?',
                    'Termin-Dossier für die Passbehörde erstellen',
                ];
            }

            return [
                'Do you have a biometric photo (35x45mm)?',
                'Do you need an express passport (2–3 days)?',
                'How do I book an appointment online?',
                'Generate passport appointment dossier',
            ];
        }

        // Case 3: ID Austria
        if (str_contains($lower, 'id austria') || str_contains($lower, 'signatur')) {
            if ($isGerman) {
                return [
                    'Haben Sie bereits einen österreichischen Reisepass?',
                    'Wie aktiviere ich die Vollfunktion am Amt?',
                    'Welche Smartphone-Apps benötige ich?',
                ];
            }

            return [
                'Do you have an Austrian passport or photo ID?',
                'How do I activate full functionality in person?',
                'Which smartphone apps do I need?',
            ];
        }

        // Default General
        if ($isGerman) {
            return [
                'Reisepass erneuern (Ablauf & Gebühren)',
                'Wohnsitz ummelden (3-Tage-Frist)',
                'Dossier für die Behörde generieren',
            ];
        }

        return [
            'Passport renewal (process & fees)',
            'Change of residence (3-day deadline)',
            'Generate official citizen dossier',
        ];
    }

    /**
     * Extract and remove the trailing markdown heading from prose if present.
     */
    protected static function extractPrecedingHeading(string &$prose): ?string
    {
        $lines = explode("\n", trim($prose));
        $lastLine = end($lines);

        if ($lastLine !== false && preg_match('/^#{2,6}\s*(.+)/', trim($lastLine), $m)) {
            $heading = trim($m[1]);
            array_pop($lines);
            $prose = implode("\n", $lines);

            return $heading;
        }

        return null;
    }
}
