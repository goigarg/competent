<?php

use App\Enums\Language;
use App\Livewire\AustrianGovAssistant;
use App\Livewire\EmployeePortal;
use App\Models\Conversation;
use App\Models\User;
use App\Support\Translate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('switching the language updates session, conversation, html attributes and texts', function (Language $language) {
    $component = Livewire::test(AustrianGovAssistant::class)
        ->call('setLocale', $language->value)
        ->assertSet('locale', $language->value)
        ->assertSee('lang="'.$language->htmlLang().'"', false)
        ->assertSee('dir="'.$language->dir().'"', false)
        ->assertSee(Translate::text('chat.language_switched', [], $language->value))
        ->assertSee(Translate::text('answer.talk_to_person', [], $language->value));

    expect(session('locale'))->toBe($language->value)
        ->and(Conversation::find($component->get('conversationId'))?->locale)->toBe($language->value);

    // A follow-up request keeps the language (hydrate path).
    $component->call('setTextSize', 'large')
        ->assertSee(Translate::text('chat.send', [], $language->value));
})->with(Language::cases());

test('unknown language codes are ignored', function () {
    Livewire::test(AustrianGovAssistant::class)
        ->call('setLocale', 'xx')
        ->assertSet('locale', 'de');
});

test('the switcher lists every language by its own name', function () {
    $component = Livewire::test(AustrianGovAssistant::class);

    foreach (Language::cases() as $language) {
        $component->assertSee($language->nativeName());
    }
});

test('languages without reviewed content get the english content with a notice', function () {
    session(['locale' => 'tr']);

    Livewire::test(AustrianGovAssistant::class)
        ->assertSee('Welcome!')
        ->assertSee('Bu içerik henüz Türkçe mevcut değil');
});

test('the welcome page renders arabic right to left', function () {
    $this->withSession(['locale' => 'ar'])
        ->get('/')
        ->assertOk()
        ->assertSee('<html lang="ar" dir="rtl"', false);
});

test('the staff portal stays german whatever the citizen language is', function () {
    $officer = User::factory()->create();
    Conversation::create(['session_id' => 's', 'locale' => 'ar', 'status' => 'pending_officer', 'is_escalated' => true]);

    $this->actingAs($officer)->withSession(['locale' => 'ar']);

    Livewire::test(EmployeePortal::class)
        ->assertSee('العربية');

    expect(app()->getLocale())->toBe('de');
});

test('the stream endpoint accepts registered languages only', function () {
    $this->postJson(route('assistant.stream'), ['messages' => [['role' => 'user', 'content' => 'Hallo']], 'locale' => 'xx'])
        ->assertStatus(422);
});
