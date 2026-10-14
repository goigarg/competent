<?php

use App\Enums\Language;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

const UI_GROUPS = ['chat', 'answer'];

/**
 * @return array<string, mixed>
 */
function translationFile(string $locale, string $group): array
{
    $path = lang_path("{$locale}/{$group}.php");

    return is_file($path) ? Arr::dot(require $path) : [];
}

/**
 * @return list<string>
 */
function placeholders(string $text): array
{
    preg_match_all('/:([a-z_]+)/', $text, $m);
    $names = array_values(array_unique($m[1]));
    sort($names);

    return $names;
}

test('every language has complete interface translations', function (Language $language, string $group) {
    $german = translationFile('de', $group);
    $translation = translationFile($language->value, $group);

    if ($language->isSimple()) {
        // Leichte Sprache only overrides some German texts and falls back to German for the rest.
        expect(array_diff(array_keys($translation), array_keys($german)))->toBe([]);

        return;
    }

    expect(array_keys($translation))->toEqualCanonicalizing(array_keys($german));

    foreach ($german as $key => $text) {
        expect(trim((string) $translation[$key]))->not->toBe('', "{$language->value}.{$group}.{$key} is empty");

        if (! str_contains($key, 'label_steps')) {
            expect(placeholders((string) $translation[$key]))->toBe(placeholders((string) $text), "placeholders differ in {$language->value}.{$group}.{$key}");
        }
    }
})->with(Language::cases())->with(UI_GROUPS);

test('every translation key used in the code exists in german', function () {
    $code = collect([...File::allFiles(app_path()), ...File::allFiles(resource_path('views'))])
        ->map(fn ($file) => $file->getContents())
        ->implode("\n");

    preg_match_all("/(?:__|trans_choice|Translate::text|Translate::list|Translate::map|->t)\\('((?:chat|answer|assistant|workflows|sources|fallbacks)\\.[a-z0-9_.]+)'/", $code, $matches);

    foreach (array_unique($matches[1]) as $key) {
        expect(trans()->hasForLocale($key, 'de'))->toBeTrue("missing translation key {$key}");
    }
});

test('step labels use the plural forms of each language', function (Language $language) {
    foreach ([1, 2, 3, 5, 11, 21, 100] as $count) {
        $label = trans_choice('answer.label_steps', $count, ['count' => $count], $language->value);

        expect($label)->not->toContain('|')->and($label)->not->toContain('{');
        if ($count > 2) {
            expect($label)->toContain((string) $count);
        }
    }
})->with(Language::cases());
