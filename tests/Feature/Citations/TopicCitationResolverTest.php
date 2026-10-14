<?php

use App\Models\KnowledgeNugget;
use App\Services\Citations\CitedSource;
use App\Services\Citations\Contracts\CitationResolver;
use App\Services\Citations\TopicCitationResolver;

test('the citation resolver is bound to the topic resolver', function () {
    expect(app(CitationResolver::class))->toBeInstanceOf(TopicCitationResolver::class);
});

test('resolves numbered, deduplicated, unverified sources for several topics', function () {
    $sources = app(CitationResolver::class)->resolve('Umzug nach Graz und Pass abgelaufen', ['residence', 'passport'], 'de', collect());

    expect($sources)->not->toBeEmpty()
        ->and(count($sources))->toBeLessThanOrEqual(TopicCitationResolver::MAX_SOURCES)
        ->and(array_map(fn (CitedSource $s) => $s->number, $sources))->toBe(range(1, count($sources)))
        ->and(collect($sources)->every(fn (CitedSource $s) => $s->verified === false))->toBeTrue()
        ->and(collect($sources)->pluck('url')->unique()->count())->toBe(count($sources))
        ->and(collect($sources)->pluck('label')->filter(fn ($l) => str_starts_with($l, 'oesterreich.gv.at, '))->isNotEmpty())->toBeTrue();
});

test('nugget sources need a real authority page, never a bare portal root', function () {
    $nuggets = collect([
        new KnowledgeNugget(['title' => 'Distilled', 'official_source_url' => 'https://www.oesterreich.gv.at']),
        new KnowledgeNugget(['title' => 'Foreign', 'official_source_url' => 'https://example.com/page']),
        new KnowledgeNugget(['title' => 'Studienvisum OeAD', 'official_source_url' => 'https://oead.at/de/nach-oesterreich/einreise-und-aufenthalt']),
    ]);

    $sources = app(CitationResolver::class)->resolve('student visa', ['permit'], 'de', $nuggets);
    $urls = collect($sources)->pluck('url');

    expect($urls)->not->toContain('https://www.oesterreich.gv.at')
        ->and($urls)->not->toContain('https://example.com/page')
        ->and($urls)->toContain('https://oead.at/de/nach-oesterreich/einreise-und-aufenthalt');
});

test('finalize marks only the cited sources and keeps all numbers', function () {
    $resolver = app(CitationResolver::class);
    $sources = $resolver->resolve('Reisepass', ['passport'], 'de', collect());

    $final = $resolver->finalize($sources, "1. **Neuen Reisepass beantragen** [2]\n   Wo: Passbehörde");

    expect($final)->toHaveCount(count($sources))
        ->and(collect($final)->filter->cited->pluck('number')->all())->toBe([2])
        ->and(collect($final)->every(fn (CitedSource $s) => $s->verified === false))->toBeTrue();
});
