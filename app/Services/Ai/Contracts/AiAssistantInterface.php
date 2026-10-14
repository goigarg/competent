<?php

namespace App\Services\Ai\Contracts;

interface AiAssistantInterface
{
    /**
     * Send a chat completion request to the AI model.
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
    public function chat(array $messages, array $options = []): array;

    /**
     * Stream a chat completion request token by token.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  callable(string): void  $callback
     * @param  array<string, mixed>  $options
     */
    public function streamChat(array $messages, callable $callback, array $options = []): void;

    /**
     * Get the active model identifier.
     */
    public function getModel(): string;
}
