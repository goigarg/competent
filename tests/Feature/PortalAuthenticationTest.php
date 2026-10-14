<?php

use App\Models\Conversation;
use App\Models\User;
use Database\Seeders\CivilServantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('unauthenticated users visiting portal are redirected to login', function () {
    $this->get('/portal')
        ->assertRedirect('/login');

    $this->get('/portal/cases')
        ->assertRedirect('/login');

    $this->get('/employee')
        ->assertRedirect('/login');
});

test('officer can authenticate with seeded temporary credentials and is redirected to portal', function () {
    $this->seed(CivilServantSeeder::class);

    // Attempt to visit portal as guest to set intended url
    $this->get('/portal')
        ->assertRedirect('/login');

    // Authenticate with the temporary civil servant credentials
    $response = $this->post('/login', [
        'email' => 'beamter@bka.gv.at',
        'password' => 'Bundeskanzleramt2030!',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/portal');
});

test('authenticated officer can view portal cases and logout', function () {
    $officer = User::factory()->create([
        'name' => 'Mag. Stefan Gruber',
        'email' => 'beamter@bka.gv.at',
    ]);

    $conv = Conversation::create([
        'session_id' => 'auth-test-session',
        'locale' => 'de',
        'title' => 'Eskalierter Passantrag',
        'is_escalated' => true,
        'escalated_at' => now(),
        'status' => 'pending_officer',
        'case_dossier' => 'Details zum Passantrag',
    ]);

    $this->actingAs($officer)
        ->get('/portal')
        ->assertOk()
        ->assertSee('Bundesservice Fall-Eskalation')
        ->assertSee('Mag. Stefan Gruber')
        ->assertSee("Fall #{$conv->id}");

    // Test logout
    $this->actingAs($officer)
        ->post('/logout')
        ->assertRedirect('/');

    $this->assertGuest();
});
