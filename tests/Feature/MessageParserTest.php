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
