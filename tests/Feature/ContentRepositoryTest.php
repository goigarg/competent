<?php

use App\Enums\Language;
use App\Services\AustrianGov\IntentMatcher;
use App\Services\AustrianGov\MessageParser;
use App\Services\AustrianGov\OfficerMessages;
use App\Services\Content\ContentRepository;

function contentFixture(): string
{
    $base = storage_path('framework/testing/content-'.uniqid());
    @mkdir("{$base}/de/fallbacks", 0777, true);
    @mkdir("{$base}/en/fallbacks", 0777, true);
    file_put_contents("{$base}/de/fallbacks/passport.md", "Deutsch\n");
    file_put_contents("{$base}/en/fallbacks/passport.md", "English\n");
    file_put_contents("{$base}/de/fallbacks/only_german.md", "Nur Deutsch\n");

    return $base;
}

test('content follows the fallback chain of the language', function () {
    $repo = new ContentRepository(contentFixture());

    expect($repo->markdown('fallbacks/passport', Language::Turkish))
        ->body->toBe('English')
        ->servedIn->toBe(Language::English)
        ->and($repo->markdown('fallbacks/passport', Language::GermanSimple)->servedIn)->toBe(Language::German)
        ->and($repo->markdown('fallbacks/only_german', Language::Arabic)->body)->toBe('Nur Deutsch');
});

test('missing content fails loudly', function () {
    (new ContentRepository(contentFixture()))->markdown('fallbacks/unknown', Language::German);
})->throws(RuntimeException::class);

test('intents are recognised in every language', function () {
    $intents = app(IntentMatcher::class);

    expect($intents->wantsHuman('Mit einer Person sprechen'))->toBeTrue()
        ->and($intents->wantsHuman('Bir kişiyle konuşun'))->toBeTrue()
        ->and($intents->wantsHuman('Поговорити з людиною'))->toBeTrue()
        ->and($intents->wantsHuman('Brauche ich einen Personalausweis?'))->toBeFalse()
        ->and($intents->workflowFor('Mein Reisepass ist abgelaufen'))->toBe('passport')
        ->and($intents->workflowFor('Мій паспорт прострочений'))->toBe('passport')
        ->and($intents->wantsDossier('Dossier für die Behörde generieren'))->toBeTrue();
});

test('the parser reads canonical, legacy and translated field labels', function () {
    $markdown = "1. **Anmelden** [1]\n   Where: Magistrat Graz\n   Frist: 3 Tage\n   Документи: Meldezettel; Pass\n\n2. **Abmelden**\n   Documents: none";
    $steps = MessageParser::parse($markdown)[0]['items'];

    expect($steps[0]['where'])->toBe('Magistrat Graz')
        ->and($steps[0]['deadline'])->toBe('3 Tage')
        ->and($steps[0]['documents'])->toBe(['Meldezettel', 'Pass'])
        ->and($steps[1]['documents'])->toBe([]);
});

test('officer messages are recognised by their workflow flag', function () {
    expect(OfficerMessages::kind(['workflow' => 'officer_joined', 'content' => 'Ahmed ist da']))->toBe('joined')
        ->and(OfficerMessages::kind(['workflow' => 'officer_message', 'content' => 'Termin bestätigt']))->toBe('message')
        ->and(OfficerMessages::kind(['workflow' => null, 'content' => 'Normale Antwort']))->toBeNull();
});
