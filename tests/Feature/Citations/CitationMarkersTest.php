<?php

use App\Services\Citations\CitationMarkers;

test('extracts source numbers in order and ignores checkboxes labels and links', function () {
    $text = "Melden Sie sich an [2]. Frist [1] und nochmal [2].\n- [ ] Pass\n- [x] Foto\n[NÄCHSTE SCHRITTE]\n[SVNr geschwärzt] [3](https://example.com)";

    expect(CitationMarkers::extract($text))->toBe([2, 1]);
});

test('strips markers without touching other brackets', function () {
    expect(CitationMarkers::strip('Frist: 3 Tage [1]. Siehe [Formular](https://x.at) und - [ ] Pass'))
        ->toBe('Frist: 3 Tage. Siehe [Formular](https://x.at) und - [ ] Pass');
});

test('linkifies valid markers and drops unknown numbers', function () {
    $html = CitationMarkers::linkifyHtml('<p>Gebührenfrei [1] und schnell [7].</p>', [1, 2], 'src-abc', 'de');

    expect($html)->toContain('<sup')
        ->and($html)->toContain('href="#src-abc-1"')
        ->and($html)->toContain('aria-label="Quelle 1"')
        ->and($html)->not->toContain('[7]')
        ->and($html)->not->toContain('src-abc-7');
});
