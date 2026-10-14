<?php

namespace App\Services\Ai;

use App\Services\Ai\Contracts\AiAssistantInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OpenRouterClient implements AiAssistantInterface
{
    protected ?string $apiKey;

    protected string $model;

    protected string $baseUrl;

    protected int $timeout;

    protected string $appUrl;

    protected string $appName;

    public function __construct(
        ?string $apiKey = null,
        ?string $model = null,
        ?string $baseUrl = null,
        ?int $timeout = null,
        ?string $appUrl = null,
        ?string $appName = null
    ) {
        $this->apiKey = $apiKey ?? config('openrouter.api_key');
        $this->model = $model ?? config('openrouter.model', 'poolside/laguna-s-2.1:free');
        $this->baseUrl = rtrim($baseUrl ?? config('openrouter.base_url', 'https://openrouter.ai/api/v1'), '/');
        $this->timeout = $timeout ?? (int) config('openrouter.timeout', 30);
        $this->appUrl = $appUrl ?? config('openrouter.app_url', 'http://localhost:8000');
        $this->appName = $appName ?? config('openrouter.app_name', 'Digitales Österreich 2030');
    }

    /**
     * Send a chat completion request to OpenRouter.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $options
     * @return array{
     *     success: bool,
     *     content: string,
     *     model: string,
     *     error: ?string,
     *     fallback: bool,
     *     usage?: array<string, int>
     * }
     */
    public function chat(array $messages, array $options = []): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'content' => '',
                'model' => $this->model,
                'error' => 'API-Schlüssel für OpenRouter nicht konfiguriert (OPENROUTER_API_KEY).',
                'fallback' => true,
            ];
        }

        $model = $options['model'] ?? $this->model;
        $temperature = $options['temperature'] ?? 0.3;
        $maxTokens = $options['max_tokens'] ?? 1500;

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout($this->timeout)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => $temperature,
                    'max_tokens' => $maxTokens,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (! empty($data['error'])) {
                    $errMsg = is_array($data['error']) ? ($data['error']['message'] ?? json_encode($data['error'])) : $data['error'];
                    Log::warning('OpenRouter returned error in 200 payload', ['error' => $errMsg]);

                    return [
                        'success' => false,
                        'content' => '',
                        'model' => $model,
                        'error' => "API-Meldung: {$errMsg}",
                        'fallback' => true,
                    ];
                }

                $message = $data['choices'][0]['message'] ?? [];
                $content = $message['content'] ?? '';

                // If content is empty, check reasoning fields which DeepSeek models often output
                if (trim((string) $content) === '' && ! empty($message['reasoning_content'])) {
                    $content = $message['reasoning_content'];
                }
                if (trim((string) $content) === '' && ! empty($message['reasoning'])) {
                    $content = $message['reasoning'];
                }

                $content = trim((string) $content);

                if ($content === '') {
                    Log::warning('OpenRouter returned 200 but message content was empty', ['payload' => $data]);

                    return [
                        'success' => false,
                        'content' => '',
                        'model' => $model,
                        'error' => 'Antwort des KI-Modells war leer.',
                        'fallback' => true,
                    ];
                }

                $usage = $data['usage'] ?? [];

                return [
                    'success' => true,
                    'content' => $content,
                    'model' => $model,
                    'error' => null,
                    'fallback' => false,
                    'usage' => [
                        'prompt_tokens' => $usage['prompt_tokens'] ?? 0,
                        'completion_tokens' => $usage['completion_tokens'] ?? 0,
                        'total_tokens' => $usage['total_tokens'] ?? 0,
                    ],
                ];
            }

            $statusCode = $response->status();
            $errorBody = $response->json('error.message') ?? $response->body();

            Log::warning('OpenRouter API request failed', [
                'status' => $statusCode,
                'error' => $errorBody,
                'model' => $model,
            ]);

            $friendlyError = match ($statusCode) {
                401 => 'Ungültiger oder abgelaufener OpenRouter API-Schlüssel.',
                429 => 'Das Abfragelimit für den KI-Dienst wurde vorübergehend erreicht (Rate Limit).',
                503, 502 => 'Der OpenRouter-Dienst oder das KI-Modell ist zurzeit nicht erreichbar.',
                default => "API-Fehler ({$statusCode}): {$errorBody}",
            };

            return [
                'success' => false,
                'content' => '',
                'model' => $model,
                'error' => $friendlyError,
                'fallback' => true,
            ];
        } catch (Throwable $e) {
            Log::error('OpenRouter Exception: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'content' => '',
                'model' => $model,
                'error' => 'Verbindungsfehler oder Zeitüberschreitung: '.$e->getMessage(),
                'fallback' => true,
            ];
        }
    }

    /**
     * Stream response chunks via cURL directly to callback.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  callable(string): void  $callback
     * @param  array<string, mixed>  $options
     */
    public function streamChat(array $messages, callable $callback, array $options = []): void
    {
        if (empty($this->apiKey)) {
            $callback('Fehler: Kein OpenRouter API-Schlüssel konfiguriert.');

            return;
        }

        $model = $options['model'] ?? $this->model;
        $url = "{$this->baseUrl}/chat/completions";

        $payload = json_encode([
            'model' => $model,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.3,
            'max_tokens' => $options['max_tokens'] ?? 1500,
            'stream' => true,
        ]);

        $headers = [];
        foreach ($this->getHeaders() as $key => $value) {
            $headers[] = "{$key}: {$value}";
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);

        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) use ($callback) {
            $lines = explode("\n", $data);
            foreach ($lines as $line) {
                $line = trim($line);
                if (str_starts_with($line, 'data: ')) {
                    $jsonStr = substr($line, 6);
                    if ($jsonStr === '[DONE]') {
                        continue;
                    }
                    $json = json_decode($jsonStr, true);
                    $delta = $json['choices'][0]['delta']['content'] ?? '';
                    if ($delta !== '') {
                        $callback($delta);
                    }
                }
            }

            return strlen($data);
        });

        curl_exec($ch);
        curl_close($ch);
    }

    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * @return array<string, string>
     */
    protected function getHeaders(): array
    {
        return [
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
            'HTTP-Referer' => $this->appUrl,
            'X-Title' => $this->appName,
        ];
    }
}
