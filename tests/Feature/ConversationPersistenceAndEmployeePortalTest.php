<?php

use App\Livewire\AustrianGovAssistant;
use App\Livewire\EmployeePortal;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('conversations and messages are saved in database and reloaded on page refresh', function () {
    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Für einen Reisepass benötigen Sie ein biometrisches Passfoto.',
                    ],
                ],
            ],
        ], 200),
    ]);

    // Initial session with citizen message
    $test = Livewire::test(AustrianGovAssistant::class)
        ->set('inputMessage', 'Welche Unterlagen brauche ich für meinen Reisepass?')
        ->call('sendMessage')
        ->assertSee('Für einen Reisepass benötigen Sie ein biometrisches Passfoto.');

    $conversationId = $test->get('conversationId');
    expect($conversationId)->not->toBeNull();

    // Verify persisted in database
    $conv = Conversation::with('messages')->find($conversationId);
    expect($conv)->not->toBeNull();
    expect($conv->messages->count())->toBeGreaterThanOrEqual(3); // welcome, user, assistant

    // Simulate browser reload by mounting with same session
    session(['austrian_gov_conversation_id' => $conversationId]);

    Livewire::test(AustrianGovAssistant::class)
        ->assertSet('conversationId', $conversationId)
        ->assertSee('Welche Unterlagen brauche ich für meinen Reisepass?')
        ->assertSee('Für einen Reisepass benötigen Sie ein biometrisches Passfoto.');
});

test('escalating to human sets is_escalated and makes case visible in employee portal', function () {
    $assistantTest = Livewire::test(AustrianGovAssistant::class)
        ->set('inputMessage', 'Ich möchte bitte mit einem echten Beamten sprechen!')
        ->call('sendMessage')
        ->assertSee('Persönliche menschliche Unterstützung')
        ->assertSee('Beamtinnen- & Beamten-Portal');

    $conversationId = $assistantTest->get('conversationId');
    $conv = Conversation::find($conversationId);

    expect($conv->is_escalated)->toBeTrue();
    expect($conv->status)->toBe('pending_officer');
    expect($conv->case_dossier)->not->toBeEmpty();

    // Now visit employee portal
    $this->get(route('portal.cases'))
        ->assertOk()
        ->assertSee('Bundesservice Fall-Eskalation')
        ->assertSee("Fall #{$conv->id}");

    // Test EmployeePortal Livewire interactions
    Livewire::test(EmployeePortal::class, ['conversationId' => $conv->id])
        ->assertSee("Fall #{$conv->id}")
        ->assertSee('AI-Fallzusammenfassung')
        ->set('assignedOfficerInput', 'Mag. Weber')
        ->set('officerNoteInput', 'Bürger per E-Mail kontaktiert')
        ->call('saveOfficerNotes')
        ->assertSee('Sachbearbeiter-Notizen und Zuweisung erfolgreich gespeichert.')
        ->call('updateStatus', 'in_review')
        ->assertSee('In Bearbeitung')
        ->call('resolveCase')
        ->assertSee('ERLEDIGT');

    $conv->refresh();
    expect($conv->assigned_officer)->toBe('Mag. Weber');
    expect($conv->officer_notes)->toBe('Bürger per E-Mail kontaktiert');
    expect($conv->status)->toBe('resolved');
});

test('civil servant can send direct message to citizen from employee portal', function () {
    $conv = Conversation::create([
        'session_id' => 'test-session-123',
        'locale' => 'de',
        'title' => 'Anfrage zu Meldezettel',
        'is_escalated' => true,
        'escalated_at' => now(),
        'status' => 'pending_officer',
        'case_dossier' => 'Dossier Details',
    ]);

    ConversationMessage::create([
        'conversation_id' => $conv->id,
        'role' => 'user',
        'content' => 'Guten Tag, wo reiche ich den Meldezettel ein?',
    ]);

    Livewire::test(EmployeePortal::class, ['conversationId' => $conv->id])
        ->set('assignedOfficerInput', 'Amtsrat Dr. Hofer')
        ->set('officerResponseInput', 'Sie können den Meldezettel bei jedem Wiener Bezirksamt einreichen.')
        ->call('sendOfficerMessage')
        ->assertSee('Antwort wurde direkt in das Bürgergespräch übermittelt.');

    // Verify message is saved in conversation messages
    $lastMsg = ConversationMessage::where('conversation_id', $conv->id)->latest('id')->first();
    expect($lastMsg)->not->toBeNull();
    expect($lastMsg->role)->toBe('assistant');
    expect($lastMsg->content)->toContain('Offizielle Mitteilung der Sachbearbeitung (Amtsrat Dr. Hofer)');
    expect($lastMsg->content)->toContain('Sie können den Meldezettel bei jedem Wiener Bezirksamt einreichen.');
});

test('it repairs blank assistant messages on conversation reload', function () {
    $conv = Conversation::create([
        'session_id' => 'test-session-repair-'.uniqid(),
        'locale' => 'en',
        'status' => 'active',
    ]);

    ConversationMessage::create([
        'conversation_id' => $conv->id,
        'role' => 'user',
        'content' => 'i want to apply for student residence permit',
    ]);

    $blankMsg = ConversationMessage::create([
        'conversation_id' => $conv->id,
        'role' => 'assistant',
        'content' => '',
        'is_fallback' => false,
    ]);

    session(['austrian_gov_conversation_id' => $conv->id]);

    $component = Livewire::test(AustrianGovAssistant::class);

    // Verify blank message was repaired and is not empty in component state
    $loadedMessages = $component->get('messages');
    $assistantMsg = collect($loadedMessages)->firstWhere('role', 'assistant');
    expect($assistantMsg)->not->toBeNull();
    expect(trim($assistantMsg['content']))->not->toBeEmpty();
    expect($assistantMsg['content'])->toContain('Student Residence Permit');

    // Verify database row was also repaired
    $blankMsg->refresh();
    expect(trim($blankMsg->content))->not->toBeEmpty();
    expect($blankMsg->content)->toContain('Student Residence Permit');
});
