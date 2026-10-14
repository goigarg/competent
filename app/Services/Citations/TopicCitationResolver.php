<?php

namespace App\Services\Citations;

use App\Services\AustrianGov\KnowledgeBaseService;
use App\Services\Citations\Contracts\CitationResolver;
use Illuminate\Support\Collection;

/**
 * Offers the official sources for the detected topics plus the source pages of matching knowledge nuggets.
 *
 * Nothing is marked as verified here: these sources are matched by topic, not checked against the answer.
 */
class TopicCitationResolver implements CitationResolver
{
    public const MAX_SOURCES = 6;

    /**
     * Hosts accepted as authority sources for nugget URLs (suffix match).
     */
    public const AUTHORITY_HOSTS = ['gv.at', 'gesundheitskasse.at', 'oead.at'];

    public function __construct(protected KnowledgeBaseService $knowledgeBase) {}

    public function resolve(string $query, array $topics, string $locale, Collection $nuggets): array
    {
        $candidates = [];

        foreach ($nuggets as $nugget) {
            $url = (string) $nugget->official_source_url;

            if (! $this->isCitableNuggetUrl($url)) {
                continue;
            }

            $candidates[] = ['title' => (string) $nugget->title, 'url' => $url, 'origin' => 'nugget'];
        }

        foreach ($topics === [] ? ['general'] : $topics as $topic) {
            foreach ($this->knowledgeBase->getOfficialSources($topic, $locale) as $source) {
                $candidates[] = $source + ['origin' => 'topic'];
            }
        }

        $byUrl = [];
        foreach ($candidates as $candidate) {
            $key = $this->normalizeUrl($candidate['url']);

            if ($key === '') {
                continue;
            }

            // Prefer the curated topic entry over a nugget entry for the same page (keeps the first position).
            if (! isset($byUrl[$key]) || ($byUrl[$key]['origin'] === 'nugget' && $candidate['origin'] === 'topic')) {
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
