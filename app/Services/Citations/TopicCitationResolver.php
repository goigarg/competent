<?php

namespace App\Services\Citations;

use App\Models\AdministrativeForm;
use App\Services\AustrianGov\KnowledgeBaseService;
use App\Services\Citations\Contracts\CitationResolver;
use Illuminate\Support\Collection;

/**
 * Offers official verified sources for topics, dynamic administrative forms, and matching knowledge nuggets.
 *
 * Nothing is marked as verified here: these sources are matched by topic and query, not checked against the answer.
 */
class TopicCitationResolver implements CitationResolver
{
    public const MAX_SOURCES = 6;

    /**
     * Hosts accepted as authority sources for nugget & form URLs (suffix match).
     */
    public const AUTHORITY_HOSTS = ['gv.at', 'gesundheitskasse.at', 'oead.at', 'ams.at', 'arbeiterkammer.at'];

    public function __construct(protected KnowledgeBaseService $knowledgeBase) {}

    public function resolve(string $query, array $topics, string $locale, Collection $nuggets): array
    {
        $normalizedQuery = mb_strtolower(trim($query));
        $isFormQuery = AdministrativeForm::containsFormIntent($normalizedQuery);

        $formCandidates = [];
        try {
            $forms = AdministrativeForm::findRelevantForms($query, $topics, $locale, 2);
            foreach ($forms as $form) {
                $url = $form->primaryUrl();
                if (! $this->isCitableNuggetUrl($url)) {
                    continue;
                }

                $prefix = $locale === 'en' ? 'Official Form' : 'Offizielles Formular';
                $title = $form->titleForLocale($locale);

                $formCandidates[] = [
                    'title' => "{$prefix}: {$title}",
                    'url' => $url,
                    'tag' => $form->authority,
                    'description' => (string) ($form->description ?? ''),
                    'origin' => 'form',
                ];
            }
        } catch (\Throwable) {
            // Memory or testing fallback
        }

        $nuggetCandidates = [];
        foreach ($nuggets as $nugget) {
            $url = (string) $nugget->official_source_url;

            if (! $this->isCitableNuggetUrl($url)) {
                continue;
            }

            $nuggetCandidates[] = ['title' => (string) $nugget->title, 'url' => $url, 'origin' => 'nugget'];
        }

        $topicCandidates = [];
        foreach ($topics === [] ? ['general'] : $topics as $topic) {
            foreach ($this->knowledgeBase->getOfficialSources($topic, $locale) as $source) {
                $topicCandidates[] = $source + ['origin' => 'topic'];
            }
        }

        // If the query asks for forms/applications, place form candidates at the top.
        // Otherwise, place nuggets & curated topics first, with relevant forms following.
        $candidates = $isFormQuery
            ? [...$formCandidates, ...$nuggetCandidates, ...$topicCandidates]
            : [...$formCandidates, ...$nuggetCandidates, ...$topicCandidates];

        $byUrl = [];
        foreach ($candidates as $candidate) {
            $key = $this->normalizeUrl($candidate['url']);

            if ($key === '') {
                continue;
            }

            // Prefer form entry over topic/nugget, and prefer curated topic over nugget
            if (! isset($byUrl[$key])) {
                $byUrl[$key] = $candidate;
            } elseif ($candidate['origin'] === 'form') {
                $byUrl[$key] = $candidate;
            } elseif ($candidate['origin'] === 'topic' && $byUrl[$key]['origin'] === 'nugget') {
                $byUrl[$key] = $candidate;
            }
        }

        $sources = [];
        foreach (array_slice(array_values($byUrl), 0, self::MAX_SOURCES) as $index => $source) {
            $sources[] = CitedSource::fromArray($source, $index + 1, (string) $source['origin']);
        }

        return $sources;
    }

    public function finalize(array $sources, string $answer): array
    {
        $cited = CitationMarkers::extract($answer);

        return array_map(fn (CitedSource $source) => $source->withCited(in_array($source->number, $cited, true)), $sources);
    }

    /**
     * Only real authority pages count, never a bare portal root (LLM-distilled nuggets use one as a placeholder).
     */
    protected function isCitableNuggetUrl(string $url): bool
    {
        $host = CitedSource::host($url);
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        if ($host === '' || $path === '') {
            return false;
        }

        foreach (self::AUTHORITY_HOSTS as $suffix) {
            if ($host === $suffix || str_ends_with($host, '.'.$suffix)) {
                return true;
            }
        }

        return false;
    }

    protected function normalizeUrl(string $url): string
    {
        $host = CitedSource::host($url);

        return $host === '' ? '' : $host.rtrim((string) parse_url($url, PHP_URL_PATH), '/');
    }
}
