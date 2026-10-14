<?php

use App\Livewire\AustrianGovAssistant;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\KnowledgeNugget;
use App\Services\Ai\Contracts\AiAssistantInterface;
use App\Services\AustrianGov\KnowledgeDistiller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'KnowledgeNuggetSeeder']);
});

test('knowledge nuggets find relevant factual context from database', function () {
    $nuggets = KnowledgeNugget::findRelevantNuggets('I am 22 and want to know how much money I need for student visa', 2);

    expect($nuggets)->not->toBeEmpty();
    $first = $nuggets->first();
    expect($first->domain)->toBe('permit')
        ->and($first->factual_context)->toContain('672,64 €')
        ->and($first->factual_context)->toContain('1.217,96 €');
});

test('querying knowledge nuggets increments their hit counter', function () {
    $nugget = KnowledgeNugget::where('topic', 'passport_renewal_fees_deadlines')->first();
    expect($nugget)->not->toBeNull();
    $initialHits = $nugget->hit_count;

    KnowledgeNugget::findRelevantNuggets('Reisepass verlängern Gebühren', 1);

    $nugget->refresh();
    expect($nugget->hit_count)->toBeGreaterThan($initialHits);
});

test('assistant retrieves database facts and personalizes response using conversation history', function () {
    $conv = Conversation::create([
        'session_id' => 'test-pers-'.uniqid(),
        'locale' => 'en',
        'status' => 'active',
    ]);

    // Pre-existing conversation history establishing age and nationality
    ConversationMessage::create([
        'conversation_id' => $conv->id,
        'role' => 'user',
        'content' => 'I am 22 years old and from Brazil',
    ]);
    ConversationMessage::create([
        'conversation_id' => $conv->id,
        'role' => 'assistant',
        'content' => 'Hello! How can I assist you with Austrian public services?',
    ]);

    session(['austrian_gov_conversation_id' => $conv->id]);

    // Mock the AI Client to inspect received prompt messages
    $mockAi = Mockery::mock(AiAssistantInterface::class);
    $mockAi->shouldReceive('chat')
        ->once()
        ->withArgs(function ($messages, $options) {
            $systemMsg = collect($messages)->firstWhere('role', 'system')['content'] ?? '';
            $userMsgs = collect($messages)->where('role', 'user')->pluck('content')->all();

            // Verify statutory database facts were injected
            expect($systemMsg)->toContain('RELEVANT STATUTORY FACTS')
                ->and($systemMsg)->toContain('PERSONALIZATION');

            // Verify citizen conversation history is preserved for personalization
            expect($userMsgs)->toContain('I am 22 years old and from Brazil')
                ->and($userMsgs)->toContain('How much maintenance funds do I need for student visa?');

            return true;
        })
        ->andReturn([
            'success' => true,
            'content' => "### Financial Proof for Student Permit (§ 64 NAG)\n\nSince you are 22 years old, you need to prove **€672.64 per month**.\n\n1. **Open Bank Account**: Provide a SEPA account in your name.\n\n- [ ] **Bank Statement**: Total approx. €8,070 for 12 months ahead.",
            'model' => 'test-model',
            'error' => null,
            'fallback' => false,
        ]);

    app()->instance(AiAssistantInterface::class, $mockAi);

    Livewire::test(AustrianGovAssistant::class)
        ->set('inputMessage', 'How much maintenance funds do I need for student visa?')
        ->call('sendMessage')
        ->assertSee('Since you are 22 years old')
        ->assertSee('€672.64');
});

test('knowledge distiller extracts and saves novel administrative insight', function () {
    $mockAi = Mockery::mock(AiAssistantInterface::class);
    $mockAi->shouldReceive('chat')
        ->once()
        ->andReturn([
            'success' => true,
            'content' => json_encode([
                'domain' => 'general',
                'topic' => 'pet_import_non_eu_health_certificate',
                'title' => 'Pet Import from Non-EU Countries: Health Certificate & Rabies Test',
                'keywords' => ['pet', 'dog', 'cat', 'haustier', 'impfung', 'tierarzt'],
                'factual_context' => 'Einreise von Hunden und Katzen aus Drittstaaten erfordert ISO-Mikrochip, gültige Tollwutimpfung und amtstierärztliches EU-Gesundheitszeugnis.',
            ]),
            'model' => 'test-model',
            'error' => null,
            'fallback' => false,
        ]);

    $distiller = new KnowledgeDistiller($mockAi);

    $userPrompt = 'What are the rules for importing a pet dog or cat into Austria from non-EU country?';
    $aiResponse = 'To import a pet dog or cat into Austria from a third country, the animal must have an ISO microchip, a valid rabies vaccination, an EU pet health certificate, and an official rabies antibody titration test.';

    $nugget = $distiller->learnFromInteraction($userPrompt, $aiResponse, 'en');

    expect($nugget)->not->toBeNull();
    expect($nugget->topic)->toBe('pet_import_non_eu_health_certificate');
    expect($nugget->factual_context)->toContain('Tollwutimpfung');
});
