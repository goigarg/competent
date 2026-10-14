<?php

use App\Livewire\AustrianGovAssistant;
use App\Services\AustrianGov\AnswerPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

uses(RefreshDatabase::class);

const STRUCTURED_ANSWER = <<<'MD'
Für Ihren Umzug und den neuen Pass sind drei Schritte nötig.

1. **Hauptwohnsitz anmelden** [1]
   Wo: Magistrat Graz, Meldeservice oder online mit ID Austria
   Frist: 3 Tage nach Einzug [1]
   Dokumente: Meldezettel, unterschrieben von Unterkunftgeber:in; Lichtbildausweis

2. **Wohnsitz in Linz abmelden** [1]
   Wo: gleich bei der Anmeldung in Graz möglich
   Dokumente: keine

3. **Neuen Reisepass beantragen** [2]
   Wo: Passbehörde, z. B. Magistrat Graz
   Dokumente: Passfoto; Alter Reisepass

[NÄCHSTE SCHRITTE]
- Online anmelden
- Passtermin buchen
- Checkliste speichern
- Mit einer Person sprechen
MD;

function modelReply(string $content): array
{
    return ['choices' => [['message' => ['role' => 'assistant', 'content' => $content]]]];
}

function presentedSources(): array
{
    return [
        ['number' => 1, 'title' => 'Wohnsitz anmelden', 'label' => 'oesterreich.gv.at, Wohnsitz anmelden', 'url' => 'https://www.oesterreich.gv.at/wohnsitz', 'verified' => false],
        ['number' => 2, 'title' => 'Reisepass', 'label' => 'oesterreich.gv.at, Reisepass', 'url' => 'https://www.oesterreich.gv.at/reisepass', 'verified' => false],
        ['number' => 3, 'title' => 'Unused', 'label' => 'bmi.gv.at, Unused', 'url' => 'https://www.bmi.gv.at/unused', 'verified' => false],
    ];
}

test('presenter builds the A to D answer with cited sources only and at most three next steps', function () {
    $view = AnswerPresenter::present(['id' => 'm1', 'content' => STRUCTURED_ANSWER, 'sources' => presentedSources()], 'de', true);

    expect($view['kind'])->toBe('answer')
        ->and($view['label'])->toBe('KI-Antwort · 3 Schritte')
        ->and(collect($view['cited_sources'])->pluck('number')->all())->toBe([1, 2])
        ->and($view['further_sources'])->toBe([])
        ->and(collect($view['next_steps'])->pluck('label')->all())->toBe(['Online anmelden', 'Passtermin buchen', 'Checkliste speichern'])
        ->and($view['next_steps'][0]['primary'])->toBeTrue()
        ->and($view['next_steps'][1]['primary'])->toBeFalse()
        ->and($view['plain_text'])->not->toContain('[1]')
        ->and($view['plain_text'])->not->toContain('NÄCHSTE SCHRITTE');
});

test('presenter falls back to further sources when an answer cites nothing', function () {
    $view = AnswerPresenter::present(['id' => 'm2', 'content' => 'Grüß Gott!', 'sources' => presentedSources()], 'de', true);

    expect($view['cited_sources'])->toBe([])
        ->and($view['further_sources'])->toHaveCount(3)
        ->and($view['label'])->toBe('KI-Antwort');
});

test('presenter shows a clarification that awaits confirmation only as the latest message', function () {
    $msg = ['id' => 'm3', 'content' => "[RÜCKFRAGE]\nHabe ich Sie richtig verstanden? Sie brauchen:\n1. einen neuen Reisepass"];

    expect(AnswerPresenter::present($msg, 'de', true))
        ->kind->toBe('clarify')
        ->label->toBe('KI-Antwort · Rückfrage')
        ->awaiting_confirmation->toBeTrue()
        ->and(AnswerPresenter::present($msg, 'de', false)['awaiting_confirmation'])->toBeFalse();
});

test('clarification, confirmation and the full answer follow the CI answer pattern', function () {
    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::sequence()
            ->push(modelReply("[RÜCKFRAGE]\nHabe ich Sie richtig verstanden? Sie brauchen:\n1. die Anmeldung Ihres neuen Hauptwohnsitzes in Graz\n2. einen neuen Reisepass"))
            ->push(modelReply(STRUCTURED_ANSWER))
            // KnowledgeDistiller asks the model once more after a full answer; '{}' is rejected as a nugget.
            ->push(modelReply('{}')),
    ]);

    $component = Livewire::test(AustrianGovAssistant::class)
        ->set('inputMessage', 'Ich zieh nach Graz und mein Pass ist auch abgelaufen.')
        ->call('sendMessage')
        ->assertSee('KI-Antwort · Rückfrage')
        ->assertSee('Habe ich Sie richtig verstanden?')
        ->assertSee('Ja, genau')
        ->assertSee('Nicht ganz');

    $clarification = collect($component->get('messages'))->last();

    $component->call('confirmUnderstanding', $clarification['id'])
        ->assertSee('KI-Antwort · 3 Schritte')
        ->assertSee('1 · Hauptwohnsitz anmelden')
        ->assertSee('Frist: 3 Tage nach Einzug')
        ->assertSee('Lichtbildausweis')
        ->assertSee('Keine weiteren Dokumente')
        ->assertSee('src-'.collect($component->get('messages'))->last()['id'].'-1', false)
        ->assertSee('Mit einer Person sprechen')
        ->assertDontSee('wire:click="confirmUnderstanding', false);

    $answer = collect($component->get('messages'))->last();
    expect(collect($answer['sources'])->where('cited', true)->pluck('number')->all())->not->toBeEmpty()
        ->and(collect($answer['sources'])->every(fn ($s) => $s['verified'] === false))->toBeTrue();

    Http::assertSentCount(3);
    Http::assertSent(function (Request $request) {
        $system = $request['messages'][0]['content'];

        return str_contains($system, 'SOURCES FOR THIS ANSWER')
            && str_contains($system, 'confirmed your summary with "Ja, genau"')
            && collect($request['messages'])->last()['content'] === 'Ja, genau';
    });
});

test('not quite asks a targeted follow-up instead of starting over', function () {
    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::sequence()
            ->push(modelReply("[RÜCKFRAGE]\nHabe ich Sie richtig verstanden? Sie brauchen:\n1. einen neuen Reisepass"))
            ->push(modelReply("[RÜCKFRAGE]\nWas fehlt in meiner Zusammenfassung?"))
            ->whenEmpty(Http::response(modelReply('{}'))),
    ]);

    $component = Livewire::test(AustrianGovAssistant::class)
        ->set('inputMessage', 'Mein Pass ist abgelaufen.')
        ->call('sendMessage');

    $component->call('rejectUnderstanding', collect($component->get('messages'))->last()['id'])
        ->assertSee('Nicht ganz')
        ->assertSee('Was fehlt in meiner Zusammenfassung?');

    Http::assertSent(fn (Request $request) => str_contains($request['messages'][0]['content'], 'chose "Nicht ganz"'));
});

test('talk to a person is the last option under every assistant answer and reaches human support', function () {
    $component = Livewire::test(AustrianGovAssistant::class);
    $html = $component->html();

    // Welcome message: next steps end with the person button.
    expect(substr_count($html, 'wire:click="respondWithHumanSupport"'))->toBeGreaterThanOrEqual(2);

    $component->call('selectNextStep', 'Mit einer Person sprechen')
        ->assertSee('+43 1 53115-0');
});

test('earlier source markers are not sent back to the model', function () {
    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::sequence()
            ->push(modelReply(STRUCTURED_ANSWER))
            ->push(modelReply('Gerne.'))
            ->whenEmpty(Http::response(modelReply('{}'))),
    ]);

    Livewire::test(AustrianGovAssistant::class)
        ->set('inputMessage', 'Frage 1')
        ->call('sendMessage')
        ->call('selectNextStep', 'Passtermin buchen');

    Http::assertSent(function (Request $request) {
        $assistantHistory = collect($request['messages'])->where('role', 'assistant')->pluck('content')->implode("\n");

        return str_contains($assistantHistory, 'Hauptwohnsitz anmelden') && ! str_contains($assistantHistory, '[1]');
    });
});
