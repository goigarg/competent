<?php

namespace App\Services\AustrianGov;

use App\Enums\Language;
use App\Services\Content\ContentRepository;
use App\Support\Translate;

/**
 * Detects intents in free text or button labels in every supported language
 * (keywords in resources/content/intents.php).
 */
class IntentMatcher
{
    /**
     * @var array{human: list<string>, dossier: list<string>, workflows: array<string, list<string>>}|null
     */
    protected ?array $intents = null;

    public function __construct(protected ContentRepository $content) {}

    public function wantsHuman(string $text): bool
    {
        return $this->contains($text, $this->intents()['human']);
    }

    public function wantsDossier(string $text): bool
    {
        return $this->contains($text, $this->intents()['dossier']);
    }

    /**
     * Guided workflow ('passport' or 'residence') a question starts, if any.
     */
    public function workflowFor(string $text): ?string
    {
        foreach ($this->intents()['workflows'] as $workflow => $keywords) {
            if ($this->contains($text, $keywords)) {
                return $workflow;
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $keywords
     */
    protected function contains(string $text, array $keywords): bool
    {
        $text = mb_strtolower($text);

        foreach ($keywords as $keyword) {
            if ($keyword !== '' && str_contains($text, mb_strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{human: list<string>, dossier: list<string>, workflows: array<string, list<string>>}
     */
    protected function intents(): array
    {
        if ($this->intents !== null) {
            return $this->intents;
        }

        $data = $this->content->data('intents');
        $strings = fn (mixed $list): array => is_array($list) ? array_values(array_filter($list, 'is_string')) : [];

        $workflows = [];
        foreach (is_array($data['workflows'] ?? null) ? $data['workflows'] : [] as $name => $keywords) {
            $workflows[(string) $name] = $strings($keywords);
        }

        $human = $strings($data['human'] ?? []);
        foreach (Language::cases() as $language) {
            $human[] = Translate::text('answer.talk_to_person', [], $language->value);
        }

        return $this->intents = [
            'human' => $human,
            'dossier' => $strings($data['dossier'] ?? []),
            'workflows' => $workflows,
        ];
    }
}
