<?php

use App\Services\Ai\OpenRouterClient;
use Illuminate\Support\Facades\Http;

test('openrouter client initializes with configured model', function () {
    $client = new OpenRouterClient(apiKey: 'test-key', model: 'deepseek/deepseek-v4-flash');

    expect($client->getModel())->toBe('deepseek/deepseek-v4-flash');
});

test('openrouter client returns successful response when api responds with choice', function () {
    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'id' => 'gen-12345',
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Guten Tag, für den Reisepass benötigen Sie ein Passfoto.',
                    ],
                ],
            ],
            'usage' => [
                'prompt_tokens' => 20,
                'completion_tokens' => 15,
                'total_tokens' => 35,
            ],
        ], 200),
    ]);

    $client = new OpenRouterClient(apiKey: 'valid-key', model: 'deepseek/deepseek-v4-flash');
    $result = $client->chat([
        ['role' => 'user', 'content' => 'Was brauche ich für den Reisepass?'],
    ]);

    expect($result['success'])->toBeTrue()
        ->and($result['fallback'])->toBeFalse()
        ->and($result['content'])->toContain('Guten Tag, für den Reisepass benötigen Sie ein Passfoto.')
        ->and($result['model'])->toBe('deepseek/deepseek-v4-flash')
        ->and($result['usage']['total_tokens'])->toBe(35);
});

test('openrouter client gracefully flags fallback when api key is missing', function () {
    $client = new OpenRouterClient(apiKey: '', model: 'deepseek/deepseek-v4-flash');
    $result = $client->chat([
        ['role' => 'user', 'content' => 'Hello'],
    ]);

    expect($result['success'])->toBeFalse()
        ->and($result['fallback'])->toBeTrue()
        ->and($result['error'])->toContain('API-Schlüssel');
});

test('openrouter client gracefully flags fallback when api returns rate limit error', function () {
    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'error' => ['message' => 'Rate limit exceeded'],
        ], 429),
    ]);

    $client = new OpenRouterClient(apiKey: 'key', model: 'deepseek/deepseek-v4-flash');
    $result = $client->chat([
        ['role' => 'user', 'content' => 'Hello'],
    ]);

    expect($result['success'])->toBeFalse()
        ->and($result['fallback'])->toBeTrue()
        ->and($result['error'])->toContain('Abfragelimit');
});
