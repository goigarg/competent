<?php

use App\Services\AustrianGov\KnowledgeBaseService;

test('knowledge base service builds grounded system prompt in german and english', function () {
    $service = new KnowledgeBaseService;

    $dePrompt = $service->buildSystemPrompt('de');
    expect($dePrompt)->toContain('offizielle KI-Bürgerassistenz der Republik Österreich')
        ->and($dePrompt)->toContain('Passgesetz 1992')
        ->and($dePrompt)->toContain('75,90 €');

    $enPrompt = $service->buildSystemPrompt('en');
    expect($enPrompt)->toContain('official AI Citizen Assistant of the Republic of Austria')
        ->and($enPrompt)->toContain('€75.90')
        ->and($enPrompt)->toContain('Residence Registration Act');
});

test('knowledge base provides official verified sources for topics', function () {
    $service = new KnowledgeBaseService;

    $passportSources = $service->getOfficialSources('passport', 'de');
    expect($passportSources)->not->toBeEmpty()
        ->and($passportSources[0]['url'])->toContain('oesterreich.gv.at');

    $residenceSources = $service->getOfficialSources('residence', 'de');
    expect($residenceSources)->not->toBeEmpty()
        ->and($residenceSources[0]['tag'])->toContain('MeldeG');

    $humanSources = $service->getOfficialSources('human', 'de');
    expect($humanSources)->not->toBeEmpty()
        ->and($humanSources[0]['description'])->toContain('+43 1 53115-0');
});

test('knowledge base provides accurate fallback response with legal fees', function () {
    $service = new KnowledgeBaseService;

    $passportFallback = $service->getFallbackResponse('Mein Reisepass läuft ab', 'de');
    expect($passportFallback['topic'])->toBe('passport')
        ->and($passportFallback['content'])->toContain('75,90 €')
        ->and($passportFallback['content'])->toContain('100,00 €')
        ->and($passportFallback['content'])->toContain('35 x 45 mm')
        ->and($passportFallback['checklist'])->not->toBeEmpty();

    $residenceFallback = $service->getFallbackResponse('Ich bin umgezogen', 'de');
    expect($residenceFallback['topic'])->toBe('residence')
        ->and($residenceFallback['content'])->toContain('3 Werktagen')
        ->and($residenceFallback['content'])->toContain('gebührenfrei')
        ->and($residenceFallback['content'])->toContain('Unterkunftgeber');
});
