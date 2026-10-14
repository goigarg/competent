<?php

use App\Services\AustrianGov\KnowledgeBaseService;

test('knowledge base service builds grounded system prompt in german and english', function () {
    $service = new KnowledgeBaseService;

    // One base prompt for every language: persona and answer pattern, plus the German fact block with official terms.
    $dePrompt = $service->buildSystemPrompt('de');
    expect($dePrompt)->toContain('official AI Citizen Assistant of the Republic of Austria')
        ->and($dePrompt)->toContain('Passgesetz 1992')
        ->and($dePrompt)->toContain('75,90 €')
        ->and($dePrompt)->toContain('Habe ich Sie richtig verstanden? Sie brauchen:')
        ->and($dePrompt)->toContain('reply in German (Austrian standard German)');

    $enPrompt = $service->buildSystemPrompt('en');
    expect($enPrompt)->toContain('official AI Citizen Assistant of the Republic of Austria')
        ->and($enPrompt)->toContain('Meldegesetz')
        ->and($enPrompt)->toContain('Did I understand you correctly? You need:')
        ->and($enPrompt)->toContain('reply in English');
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

test('knowledge base correctly detects all 10 austrian administrative domains', function () {
    $service = new KnowledgeBaseService;

    expect($service->detectDomain('Wie mache ich den Steuerausgleich in FinanzOnline?'))->toBe('tax')
        ->and($service->detectDomain('Ich brauche ein Foto für meine e-card bei der ÖGK'))->toBe('health')
        ->and($service->detectDomain('Wie schreibe ich meinen Führerschein und mein Auto um?'))->toBe('driving_vehicle')
        ->and($service->detectDomain('Wie beantrage ich die Rot-Weiß-Rot – Karte als Fachkraft?'))->toBe('work_permit')
        ->and($service->detectDomain('I want to apply for a student residence permit'))->toBe('permit')
        ->and($service->detectDomain('Wann bekomme ich die Familienbeihilfe und Kinderbetreuungsgeld?'))->toBe('family')
        ->and($service->detectDomain('Wie melde ich mich beim AMS arbeitslos?'))->toBe('employment')
        ->and($service->detectDomain('Voraussetzungen für die österreichische Staatsbürgerschaft'))->toBe('citizenship');
});

test('knowledge base provides structured guidance with checklist and next steps for expanded domains', function () {
    $service = new KnowledgeBaseService;

    // Tax domain
    $taxFallback = $service->getFallbackResponse('Steuerausgleich und Familienbonus', 'de');
    expect($taxFallback['topic'])->toBe('tax')
        ->and($taxFallback['content'])->toContain('Arbeitnehmerveranlagung')
        ->and($taxFallback['content'])->toContain('Familienbonus Plus')
        ->and($taxFallback['content'])->toContain('5 Jahre rückwirkend')
        ->and($taxFallback['checklist'])->not->toBeEmpty();

    // Health domain
    $healthFallback = $service->getFallbackResponse('e-card photo requirement and student insurance', 'en');
    expect($healthFallback['topic'])->toBe('health')
        ->and($healthFallback['content'])->toContain('e-card')
        ->and($healthFallback['content'])->toContain('Student Self-Insurance')
        ->and($healthFallback['checklist'])->not->toBeEmpty();

    // Driving domain
    $drivingFallback = $service->getFallbackResponse('ausländischen Führerschein umschreiben und Pickerl', 'de');
    expect($drivingFallback['topic'])->toBe('driving_vehicle')
        ->and($drivingFallback['content'])->toContain('Führerschein')
        ->and($drivingFallback['content'])->toContain('6 Monate')
        ->and($drivingFallback['content'])->toContain('Pickerl')
        ->and($drivingFallback['checklist'])->not->toBeEmpty();
});
