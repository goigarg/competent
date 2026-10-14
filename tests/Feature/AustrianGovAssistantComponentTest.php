<?php

use App\Livewire\AustrianGovAssistant;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

test('home page renders the kompetent assistant with ci branding and ai disclosure', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(config('app.brand'))
        ->assertSee(config('app.brand_domain'))
        ->assertSee('KI-Assistent')
        ->assertSee('KI-Antwort')
        ->assertSee('Mit einer Person sprechen');
});

test('assistant component initializes with welcome message in german', function () {
    Livewire::test(AustrianGovAssistant::class)
        ->assertSee('Grüß Gott')
        ->assertSee('Reisepass erneuern')
        ->assertSee('Wohnsitz ummelden');
});

test('assistant component toggles locale to english', function () {
    Livewire::test(AustrianGovAssistant::class)
        ->call('setLocale', 'en')
        ->assertSet('locale', 'en')
        ->assertSee('Language switched to English');
});

test('assistant component starts passport workflow', function () {
    Livewire::test(AustrianGovAssistant::class)
        ->call('startWorkflow', 'passport')
        ->assertSet('activeWorkflow', 'passport')
        ->assertSee('Interaktiver Reisepass-Assistent')
        ->call('setPassportScenario', 'expiring')
        ->call('setPassportUrgency', 'standard')
        ->assertSee('75,90 €');
});

test('assistant component starts residence workflow', function () {
    Livewire::test(AustrianGovAssistant::class)
        ->call('startWorkflow', 'residence')
        ->assertSet('activeWorkflow', 'residence')
        ->call('setResidenceMoveStatus', 'already_moved', 2)
        ->assertSee('Frist')
        ->call('setResidenceType', 'hauptwohnsitz')
        ->call('setResidenceHousing', 'rented')
        ->assertSee('Vermieter');
});

test('assistant component redacts austrian social security numbers for privacy', function () {
    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'choices' => [
                ['message' => ['role' => 'assistant', 'content' => 'Informationen erhalten.']],
            ],
        ], 200),
    ]);

    Livewire::test(AustrianGovAssistant::class)
        ->set('inputMessage', 'Meine Sozialversicherungsnummer ist 1234 010180.')
        ->call('sendMessage')
        ->assertSee('[SVNr geschwärzt]')
        ->assertSee('Datenschutz-Hinweis');
});

test('assistant component responds with human support channels', function () {
    Livewire::test(AustrianGovAssistant::class)
        ->call('respondWithHumanSupport')
        ->assertSee('+43 1 53115-0')
        ->assertSee('buergerservice@bka.gv.at');
});

test('assistant component generates civil servant case dossier', function () {
    Livewire::test(AustrianGovAssistant::class)
        ->call('startWorkflow', 'passport')
        ->call('setPassportScenario', 'expiring')
        ->call('setPassportUrgency', 'standard')
        ->call('generateDossier')
        ->assertSet('showDossierModal', true)
        ->assertSee('BÜRGER-DOSSIER');
});

test('assistant renders next step action buttons at end of assistant message', function () {
    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => "Hier sind die benötigten Unterlagen für Ihren Antrag:\n\n- [ ] **Gültiger Reisepass**: Mindestens 3 Monate gültig\n- [ ] **Krankenversicherungsnachweis**: ÖGK Bestätigung\n\n[NÄCHSTE SCHRITTE]\n- Haben Sie bereits einen gültigen Reisepass?\n- Benötigen Sie Informationen zur Krankenversicherung (ÖGK)?\n- Meldezettel-Formular herunterladen",
                    ],
                ],
            ],
        ], 200),
    ]);

    Livewire::test(AustrianGovAssistant::class)
        ->set('inputMessage', 'Welche Dokumente brauche ich für meinen Aufenthaltstitel?')
        ->call('sendMessage')
        ->assertSee('Empfohlene nächste Schritte')
        ->assertSee('Haben Sie bereits einen gültigen Reisepass?')
        ->assertSee('Benötigen Sie Informationen zur Krankenversicherung (ÖGK)?')
        ->assertSee('Meldezettel-Formular herunterladen');
});

test('clicking a next step button sends follow up question to assistant', function () {
    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::sequence()
            ->push([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => "Antwort 1.\n\n[NÄCHSTE SCHRITTE]\n- Haben Sie bereits einen Reisepass?",
                        ],
                    ],
                ],
            ], 200)
            ->push([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'Reisepass-Prüfung abgeschlossen. Ihr Reisepass ist gültig.',
                        ],
                    ],
                ],
            ], 200)
            ->whenEmpty(Http::response(['choices' => [['message' => ['role' => 'assistant', 'content' => '{}']]]], 200)),
    ]);

    Livewire::test(AustrianGovAssistant::class)
        ->set('inputMessage', 'Frage 1')
        ->call('sendMessage')
        ->assertSee('Haben Sie bereits einen Reisepass?')
        ->call('selectNextStep', 'Haben Sie bereits einen Reisepass?')
        ->assertSee('Reisepass-Prüfung abgeschlossen');
});

test('assistant generates residence permit next step buttons in english', function () {
    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => "Here are the required documents for your residence permit application:\n\n- [ ] **Valid Passport**: Minimum 3 months validity\n- [ ] **Proof of Health Insurance**: Coverage confirmation\n\n[NEXT STEPS]\n- Do you already have a valid passport?\n- How do I apply for health insurance (ÖGK)?\n- Do I need a landlord confirmation signature?",
                    ],
                ],
            ],
        ], 200),
    ]);

    Livewire::test(AustrianGovAssistant::class)
        ->call('setLocale', 'en')
        ->set('inputMessage', 'what documents i need for my residence permit application')
        ->call('sendMessage')
        ->assertSee('Recommended next steps')
        ->assertSee('Do you already have a valid passport?')
        ->assertSee('How do I apply for health insurance (ÖGK)?');
});
