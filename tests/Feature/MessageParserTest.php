<?php

use App\Services\AustrianGov\MessageParser;

test('message parser splits prose and numbered step cards', function () {
    $markdown = <<<'MD'
Ein Reisepass wird in Österreich stets neu ausgestellt. Hier ist der Prozess:

#### Schritte zur Beantragung
1. **Dokumente sammeln**: Bisherigen Reisepass und Passfoto mitbringen.
2. **Termin reservieren**: Online-Termin beim Bezirksamt buchen.
3. **Persönlich vorsprechen**: Fingerabdrücke erfassen lassen.
4. **Zustellung abwarten**: Erhalt in ca. 5 Werktagen per RSb-Brief.

Bei Fragen hilft das Bürgerservice.
MD;

    $blocks = MessageParser::parse($markdown);

    expect($blocks)->toHaveCount(3)
        ->and($blocks[0]['type'])->toBe('prose')
        ->and($blocks[0]['html'])->toContain('Ein Reisepass wird in Österreich stets neu ausgestellt.')
        ->and($blocks[1]['type'])->toBe('steps')
        ->and($blocks[1]['heading'])->toBe('Schritte zur Beantragung')
        ->and($blocks[1]['items'])->toHaveCount(4)
        ->and($blocks[1]['items'][0]['title'])->toBe('Dokumente sammeln')
        ->and($blocks[1]['items'][0]['number'])->toBe(1)
        ->and($blocks[2]['type'])->toBe('prose');
});

test('message parser identifies checklist cards with task checkboxes', function () {
    $markdown = <<<'MD'
Hier sind die Unterlagen:

#### Benötigte Dokumente
- [ ] **Bisheriger Reisepass**: Darf bis zu 5 Jahre abgelaufen sein.
- [ ] **1 Aktuelles Passfoto**: 35 x 45 mm, Hochformat.
- [ ] **Gebühr**: 75,90 € per Bankomat oder bar.
MD;

    $blocks = MessageParser::parse($markdown);

    expect($blocks)->toHaveCount(2)
        ->and($blocks[0]['type'])->toBe('prose')
        ->and($blocks[1]['type'])->toBe('checklist')
        ->and($blocks[1]['heading'])->toBe('Benötigte Dokumente')
        ->and($blocks[1]['items'])->toHaveCount(3)
        ->and($blocks[1]['items'][0]['title'])->toBe('Bisheriger Reisepass')
        ->and($blocks[1]['items'][1]['title'])->toBe('1 Aktuelles Passfoto');
});

test('message parser detects a clarification in german and english', function () {
    $de = MessageParser::parseWithNextSteps("[RÜCKFRAGE]\nHabe ich Sie richtig verstanden? Sie brauchen:\n1. die Anmeldung Ihres neuen Hauptwohnsitzes in Graz\n2. einen neuen Reisepass");
    $en = MessageParser::parseWithNextSteps("[CLARIFY]\nDid I understand you correctly? You need:\n1. a new passport", 'en');

    expect($de['kind'])->toBe('clarify')
        ->and($de['clarify']['intro'])->toBe('Habe ich Sie richtig verstanden? Sie brauchen:')
        ->and($de['clarify']['items'])->toBe(['die Anmeldung Ihres neuen Hauptwohnsitzes in Graz', 'einen neuen Reisepass'])
        ->and($de['blocks'])->toBe([])
        ->and($de['next_steps'])->toBe([])
        ->and($en['kind'])->toBe('clarify')
        ->and($en['clarify']['items'])->toBe(['a new passport']);
});

test('message parser reads structured steps with where, deadline, documents and sources', function () {
    $markdown = <<<'MD'
Für Ihren Umzug sind zwei Schritte nötig.

1. **Hauptwohnsitz anmelden** [1]
   Wo: Magistrat Graz, Meldeservice
   Frist: 3 Tage nach Einzug [1]
   Dokumente: Meldezettel; Lichtbildausweis

2. **Wohnsitz in Linz abmelden** [2]
   Wo: gleich bei der Anmeldung in Graz möglich
   Dokumente: keine

[1]: https://example.com/should-be-removed
MD;

    $parsed = MessageParser::parseWithNextSteps($markdown);
    $steps = collect($parsed['blocks'])->firstWhere('type', 'steps');

    expect($parsed['kind'])->toBe('answer')
        ->and(collect($parsed['blocks'])->where('type', 'steps'))->toHaveCount(1)
        ->and($steps['structured'])->toBeTrue()
        ->and($steps['items'])->toHaveCount(2)
        ->and($steps['items'][0]['title'])->toBe('Hauptwohnsitz anmelden')
        ->and($steps['items'][0]['where'])->toBe('Magistrat Graz, Meldeservice')
        ->and($steps['items'][0]['deadline'])->toBe('3 Tage nach Einzug')
        ->and($steps['items'][0]['documents'])->toBe(['Meldezettel', 'Lichtbildausweis'])
        ->and($steps['items'][0]['sources'])->toBe([1])
        ->and($steps['items'][1]['documents'])->toBe([])
        ->and($steps['items'][1]['sources'])->toBe([2])
        ->and(json_encode($parsed['blocks']))->not->toContain('should-be-removed');
});

test('message parser keeps legacy steps unstructured', function () {
    $steps = MessageParser::parse("1. **Termin reservieren**: Online buchen.\n2. **Vorsprechen**: Persönlich.")[0];

    expect($steps['structured'])->toBeFalse()
        ->and($steps['items'][0]['documents'])->toBeNull()
        ->and($steps['items'][0]['detail'])->toBe('Online buchen.')
        ->and($steps['items'][0]['sources'])->toBe([]);
});

test('message parser strips raw html from model output', function () {
    $html = MessageParser::parse('Hallo <script>alert(1)</script> Welt')[0]['html'];

    expect($html)->not->toContain('<script>');
});
