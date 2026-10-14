<?php

namespace App\Services\AustrianGov;

use App\Enums\Language;
use App\Services\Citations\CitedSource;
use App\Services\Content\ContentRepository;
use App\Support\Translate;

class KnowledgeBaseService
{
    /**
     * Topics with a stored fallback article in resources/content/{locale}/fallbacks/.
     */
    public const FALLBACK_TOPICS = ['passport', 'permit', 'work_permit', 'residence', 'id_austria', 'tax', 'health', 'driving_vehicle', 'family', 'employment', 'citizenship', 'human'];

    protected ContentRepository $content;

    public function __construct(?ContentRepository $content = null)
    {
        $this->content = $content ?? app(ContentRepository::class);
    }

    /**
     * Build the grounded system prompt: one language-neutral base (persona, CI answer pattern, German fact
     * block with the official terms) plus the answer-language block from the language registry.
     */
    public function buildSystemPrompt(string $locale = 'de'): string
    {
        $language = Language::fromLocale($locale);
        $base = str_replace(
            '{{clarify_intro}}',
            Translate::text('answer.clarify_intro', [], $language->value),
            $this->content->neutral('prompt/system.md'),
        );

        return implode("\n\n", [
            $base."\n".$this->content->neutral('prompt/facts.md'),
            $this->buildLanguageInstruction($language),
        ]);
    }

    /**
     * Answer-language rules (CI chapter 14): reply in the language of the question, keep official German terms.
     */
    public function buildLanguageInstruction(Language $language): string
    {
        return implode("\n", [
            'OUTPUT LANGUAGE:',
            "- Reply in the language of the citizen's latest message. If it is unclear or mixed, reply in {$language->promptName()}.",
            '- Keep official German terms in brackets after the translation, e.g. registration form (Meldezettel), so people recognise them at the office and on forms.',
            '- Use the number and currency format of the reply language. Translate the German facts above without changing their meaning.',
            '- '.$language->promptStyle(),
        ]);
    }

    /**
     * Extra instruction for the current step of the answer pattern.
     *
     * auto: the model decides (new concern → clarification); answer: the person confirmed the clarification;
     * followup: a next-step button on a confirmed concern; correct: the person chose "Nicht ganz".
     */
    public function buildPhaseInstruction(string $phase, string $locale = 'de'): string
    {
        $yes = Translate::text('answer.confirm_yes', [], $locale);
        $no = Translate::text('answer.confirm_no', [], $locale);

        return match ($phase) {
            'answer' => "PHASE: The person confirmed your summary with \"{$yes}\". Now answer fully in phase 2 for all confirmed concerns. No further clarification.",
            'followup' => 'PHASE: Follow-up on an already confirmed concern. Answer directly in phase 2, without clarification.',
            'correct' => "PHASE: The person chose \"{$no}\". Start with [CLARIFY] and ask exactly ONE targeted question about which point is wrong or what is missing. Do not start over or repeat everything.",
            default => '',
        };
    }

    /**
     * Numbered source list the answer may cite with [n] markers.
     *
     * @param  list<CitedSource>  $sources
     */
    public function buildSourcesSection(array $sources, string $locale = 'de'): string
    {
        if ($sources === []) {
            return '';
        }

        $lines = ['SOURCES FOR THIS ANSWER (only use these numbers):'];

        foreach ($sources as $source) {
            $lines[] = "[{$source->number}] {$source->label} – {$source->url}";
        }

        return implode("\n", $lines);
    }

    /**
     * Retrieve official verified sources for a given topic.
     *
     * @return array<int, array{title: string, url: string, description: string, tag: string}>
     */
    public function getOfficialSources(string $topic = 'general', string $locale = 'de'): array
    {
        $catalog = $this->content->data('sources');
        $topics = isset($catalog[$topic]) ? [$topic] : ['passport', 'residence', 'human'];
        $sources = [];

        foreach ($topics as $name) {
            foreach (is_array($catalog[$name] ?? null) ? $catalog[$name] : [] as $entry) {
                if (! is_array($entry) || ! is_string($entry['id'] ?? null)) {
                    continue;
                }

                $sources[] = [
                    'title' => Translate::text("sources.{$entry['id']}.title", [], $locale),
                    'url' => (string) ($entry['url'] ?? ''),
                    'description' => Translate::text("sources.{$entry['id']}.description", [], $locale),
                    'tag' => (string) ($entry['tag'] ?? ''),
                ];
            }
        }

        return $sources;
    }

    /**
     * Detect the most relevant Austrian administrative domain using intelligent keyword & synonym scoring.
     */
    public function detectDomain(string $query): string
    {
        return $this->detectDomains($query, 1)[0] ?? 'human';
    }

    /**
     * Detect the most relevant domains, best match first, so a message with several concerns
     * (e.g. moving to Graz and an expired passport) gets sources for each of them.
     *
     * @return list<string>
     */
    public function detectDomains(string $query, int $limit = 2): array
    {
        $q = mb_strtolower(trim($query));

        $domainKeywords = [
            'tax' => [
                'steuer', 'lohnsteuer', 'steuerausgleich', 'arbeitnehmerveranlagung', 'finanzamt', 'finanzonline',
                'tax', 'taxes', 'pendlerpauschale', 'pendlereuro', 'familienbonus', 'klimabonus', 'absetzbetrag',
                'werbungskosten', 'sonderausgaben', 'mehrkindzuschlag', 'bmf', 'tax return', 'refund',
            ],
            'health' => [
                'krankenkasse', 'ögk', 'oegk', 'ecard', 'e-card', 'selbstversicherung', 'krankenversicherung',
                'rezeptgebühr', 'gesundheitskasse', 'svs', 'bvaeb', 'arzt', 'health insurance', 'krankmeldung',
                'chipkarte', 'gesundheit', 'patient', 'rezept', 'apotheke', 'student insurance',
                'studentische selbstversicherung', 'student health insurance', 'ecard photo', 'e-card photo',
            ],
            'driving_vehicle' => [
                'führerschein', 'driving license', 'driver license', 'auto', 'kfz', 'zulassung', 'pickerl',
                '57a', 'vignette', 'asfinag', 'kennzeichen', 'fahrzeug', 'car registration', 'moped', 'motorrad',
                'zulassungsstelle', 'abmeldung kfz', 'tüv', 'umschreibung führerschein',
            ],
            'work_permit' => [
                'rot-weiß-rot', 'red-white-red', 'blaue karte', 'blue card', 'work permit', 'arbeitserlaubnis',
                'arbeitsbewilligung', 'beschäftigungsbewilligung', 'rwr', 'mangelberuf', 'fachkraft',
            ],
            'permit' => [
                'student residence permit', 'student permit', 'student visa', 'studienvisum', 'studentenvisum',
                'aufenthalt', 'aufenthaltsbewilligung', 'residence permit', 'nag', 'visum', 'visa',
                'drittstaat', 'visum d', 'visum c', 'niederlassung', 'oead', 'ma 35',
            ],
            'passport' => [
                'pass', 'reisepass', 'passport', 'personalausweis', 'ausweis', 'passfoto', 'verlustanzeige',
                'diebstahlsanzeige', 'notpass', 'expresspass', 'pass verlängern', 'pass erneuern',
            ],
            'residence' => [
                'umzug', 'umgezogen', 'umzieh', 'wohn', 'meld', 'resid', 'mov', 'adr', 'meldezettel',
                'wohnsitz', 'hauptwohnsitz', 'nebenwohnsitz', 'unterkunftgeber', 'meldeamt', 'abmelden wohnsitz',
            ],
            'id_austria' => [
                'id austria', 'handy-signatur', 'handysignatur', 'digitales amt', 'vollfunktion', 'eidas',
                'digitale identität', 'basisfunktion', 'elektronische signatur',
            ],
            'family' => [
                'familienbeihilfe', 'kinderbetreuungsgeld', 'karenz', 'elternkarenz', 'geburt', 'geburtsurkunde',
                'kinder', 'baby', 'schwanger', 'maternity', 'childcare', 'heirat', 'ehe', 'hochzeit',
                'standesamt', 'ehefähigkeitszeugnis', 'namensänderung', 'familie', 'vaterschaft',
            ],
            'employment' => [
                'ams', 'arbeitslos', 'arbeitslosengeld', 'notstandshilfe', 'job', 'arbeitsamt', 'unemployment',
                'geringfügig', 'geringfügigkeitsgrenze', 'kündigung', 'abfertigung', 'arbeitsvertrag',
            ],
            'citizenship' => [
                'staatsbürgerschaft', 'citizenship', 'einbürgerung', 'staatsbürger', 'naturalization',
                'österreichischer pass beantragen', 'staatsbürgerschaftsnachweis',
            ],
        ];

        $scores = [];
        foreach ($domainKeywords as $domain => $keywords) {
            $score = 0;
            foreach ($keywords as $kw) {
                if (str_contains($q, $kw)) {
                    $score += mb_strlen($kw); // Longer matched keywords give higher weight
                }
            }
            if ($score > 0) {
                $scores[$domain] = $score;
            }
        }

        if (empty($scores)) {
            return [];
        }

        arsort($scores);

        return array_slice(array_keys($scores), 0, $limit);
    }

    /**
     * Provide a high-quality verified fallback response when the OpenRouter API is unavailable or offline.
     *
     * @return array{
     *     content: string,
     *     sources: array<int, array{title: string, url: string, description: string, tag: string}>,
     *     checklist: list<string>,
     *     topic: string
     * }
     */
    public function getFallbackResponse(string $query, string $locale = 'de'): array
    {
        $domain = $this->detectDomain($query);
        $topic = in_array($domain, self::FALLBACK_TOPICS, true) ? $domain : 'human';

        return [
            'content' => $this->content->markdown("fallbacks/{$topic}", Language::fromLocale($locale))->body,
            'sources' => $this->getOfficialSources($domain, $locale),
            'topic' => $topic,
            'checklist' => Translate::list("fallbacks.{$topic}", $locale),
        ];
    }
}
