<?php

namespace App\Services\Citations\Contracts;

use App\Models\KnowledgeNugget;
use App\Services\Citations\CitedSource;
use Illuminate\Support\Collection;

/**
 * Decides which numbered sources an answer may cite and, after the answer, which ones it did cite.
 *
 * The current implementation offers topic-based authority sources. A verification backend can
 * replace it (bound in AppServiceProvider) and set `verified` / `checkedAt` in finalize().
 */
interface CitationResolver
{
    /**
     * Build the numbered source list (1..n) offered to the model for this answer.
     *
     * @param  list<string>  $topics
     * @param  Collection<int, KnowledgeNugget>  $nuggets
     * @return list<CitedSource>
     */
    public function resolve(string $query, array $topics, string $locale, Collection $nuggets): array;

    /**
     * Mark the sources the answer cites. Returns the full list so numbers stay stable.
     *
     * @param  list<CitedSource>  $sources
     * @return list<CitedSource>
     */
    public function finalize(array $sources, string $answer): array;
}
