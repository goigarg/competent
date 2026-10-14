<?php

namespace App\Services\Citations;

/**
 * One numbered source offered to the model for a single answer.
 *
 * The number is what the answer text refers to with markers like [1]. Sources are stored in the
 * message's `sources` JSON column in this order and must never be re-indexed.
 *
 * `verified` stays false until a verification backend confirms the source (see CitationResolver::finalize).
 */
final readonly class CitedSource
{
    public function __construct(
        public int $number,
        public string $title,
        public string $label,
        public string $url,
        public string $authority,
        public string $description = '',
        public string $tag = '',
        public string $origin = 'topic',
        public bool $verified = false,
        public ?string $checkedAt = null,
        public bool $cited = false,
    ) {}

    /**
     * Build from a legacy source array ({title, url, description, tag}) or a stored CitedSource array.
     *
     * @param  array<string, mixed>  $source
     */
    public static function fromArray(array $source, int $fallbackNumber, string $origin = 'topic'): self
    {
        $url = (string) ($source['url'] ?? '');
        $title = (string) ($source['title'] ?? '');

        return new self(
            number: (int) ($source['number'] ?? $fallbackNumber),
            title: $title,
            label: (string) ($source['label'] ?? self::makeLabel($title, $url)),
            url: $url,
            authority: (string) ($source['authority'] ?? self::host($url)),
            description: (string) ($source['description'] ?? ''),
            tag: (string) ($source['tag'] ?? ''),
            origin: (string) ($source['origin'] ?? $origin),
            verified: (bool) ($source['verified'] ?? false),
            checkedAt: isset($source['checked_at']) ? (string) $source['checked_at'] : null,
            cited: (bool) ($source['cited'] ?? false),
        );
    }

    public function withCited(bool $cited): self
    {
        return new self(
            $this->number, $this->title, $this->label, $this->url, $this->authority,
            $this->description, $this->tag, $this->origin, $this->verified, $this->checkedAt, $cited,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'number' => $this->number,
            'title' => $this->title,
            'label' => $this->label,
            'url' => $this->url,
            'authority' => $this->authority,
            'description' => $this->description,
            'tag' => $this->tag,
            'origin' => $this->origin,
            'verified' => $this->verified,
            'checked_at' => $this->checkedAt,
            'cited' => $this->cited,
        ];
    }

    /**
     * CI source label: "<host>, <title>", e.g. "oesterreich.gv.at, Reisepass für Erwachsene".
     */
    public static function makeLabel(string $title, string $url): string
    {
        $host = self::host($url);
        $title = trim((string) preg_replace('/\s+[-–]\s+'.preg_quote($host, '/').'\s*$/iu', '', $title));

        if ($host === '') {
            return $title;
        }

        return $title === '' ? $host : $host.', '.$title;
    }

    public static function host(string $url): string
    {
        $host = (string) parse_url($url, PHP_URL_HOST);

        return (string) preg_replace('/^www\./i', '', $host);
    }
}
