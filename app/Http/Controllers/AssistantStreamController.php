<?php

namespace App\Http\Controllers;

use App\Services\Ai\Contracts\AiAssistantInterface;
use App\Services\AustrianGov\KnowledgeBaseService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssistantStreamController extends Controller
{
    public function __construct(
        protected AiAssistantInterface $aiClient,
        protected KnowledgeBaseService $kbService,
    ) {}

    /**
     * Stream chat completion tokens via Server-Sent Events (SSE).
     */
    public function stream(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'messages' => ['required', 'array', 'min:1'],
            'messages.*.role' => ['required', 'string', 'in:user,assistant,system'],
            'messages.*.content' => ['required', 'string', 'max:4000'],
            'locale' => ['nullable', 'string', 'in:de,en'],
        ]);

        $locale = $validated['locale'] ?? 'de';
        $systemPrompt = $this->kbService->buildSystemPrompt($locale);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        foreach ($validated['messages'] as $msg) {
            // Scrub sensitive personal identifiers like Austrian SVNr
            $scrubbedContent = preg_replace('/\b(\d{4})\s?(\d{6})\b/', '[SVNr geschwärzt]', $msg['content']);
            $messages[] = [
                'role' => $msg['role'],
                'content' => $scrubbedContent,
            ];
        }

        return new StreamedResponse(function () use ($messages) {
            // Disable output buffering
            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            $this->aiClient->streamChat($messages, function (string $token) {
                echo 'data: '.json_encode(['token' => $token])."\n\n";
                flush();
            });

            echo "data: [DONE]\n\n";
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
