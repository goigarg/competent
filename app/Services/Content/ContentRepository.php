<?php

namespace App\Services\Content;

use App\Enums\Language;
use RuntimeException;

/**
 * Loads long content files from resources/content/{locale}/… along the language's fallback chain.
 *
 * Factual texts are only stored for reviewed languages; other languages are served the first
 * available version (e.g. Türkçe → English → Deutsch) and the caller can tell users which one it is.
 */
class ContentRepository
{
    /**
     * @var array<string, string|null>
     */
    protected array $cache = [];

    public function __construct(protected ?string $basePath = null) {}

    public function markdown(string $path, Language $language): ResolvedContent
    {
        foreach ($language->contentChain() as $candidate) {
            $body = $this->read("{$candidate->value}/{$path}.md");

            if ($body !== null) {
                return new ResolvedContent($body, $candidate);
            }
        }

        throw new RuntimeException("Content [{$path}] is missing for every language in the chain of [{$language->value}].");
    }

    /**
     * Language-neutral text file, e.g. resources/content/prompt/system.md.
     */
    public function neutral(string $path): string
    {
        $body = $this->read($path);

        if ($body === null) {
            throw new RuntimeException("Content [{$path}] does not exist.");
        }

        return $body;
    }

    /**
     * Language-neutral PHP data file, e.g. resources/content/sources.php.
     *
     * @return array<mixed>
     */
    public function data(string $file): array
    {
        $path = $this->path("{$file}.php");

        if (! is_file($path)) {
            throw new RuntimeException("Content data file [{$file}] does not exist.");
        }

        $data = require $path;

        return is_array($data) ? $data : [];
    }

    protected function read(string $relative): ?string
    {
        if (array_key_exists($relative, $this->cache)) {
            return $this->cache[$relative];
        }

        $path = $this->path($relative);
        $body = is_file($path) ? (string) file_get_contents($path) : null;

        // Files end with one newline that is not part of the text.
        return $this->cache[$relative] = $body === null ? null : (string) preg_replace('/\n\z/', '', $body);
    }

    protected function path(string $relative): string
    {
        return rtrim($this->basePath ?? resource_path('content'), '/').'/'.$relative;
    }
}
