<?php

use App\Enums\Language;

test('every language has its registry metadata', function (Language $language) {
    expect($language->nativeName())->not->toBe('')
        ->and($language->htmlLang())->not->toBe('')
        ->and($language->speechLang())->not->toBe('')
        ->and($language->promptName())->not->toBe('')
        ->and($language->promptStyle())->not->toBe('')
        ->and($language->contentChain()[0])->toBe($language)
        ->and(array_slice($language->contentChain(), -1))->toBe([Language::German]);
})->with(Language::cases());

test('only arabic is written right to left', function () {
    $rtl = array_values(array_filter(Language::cases(), fn (Language $l) => $l->isRtl()));

    expect($rtl)->toBe([Language::Arabic])
        ->and(Language::Arabic->dir())->toBe('rtl')
        ->and(Language::German->dir())->toBe('ltr');
});

test('factual content falls back to english then german, leichte sprache to german', function () {
    expect(Language::Turkish->contentChain())->toBe([Language::Turkish, Language::English, Language::German])
        ->and(Language::English->contentChain())->toBe([Language::English, Language::German])
        ->and(Language::GermanSimple->contentChain())->toBe([Language::GermanSimple, Language::German])
        ->and(Language::German->contentChain())->toBe([Language::German]);
});

test('unknown locales resolve to german', function () {
    expect(Language::fromLocale('xx'))->toBe(Language::German)
        ->and(Language::fromLocale(null))->toBe(Language::German)
        ->and(Language::fromLocale('uk'))->toBe(Language::Ukrainian);
});
