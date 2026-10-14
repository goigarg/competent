<?php

namespace App\Models;

use Database\Factories\AdministrativeFormFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $domain
 * @property string $form_code
 * @property string $title
 * @property string|null $title_en
 * @property string|null $description
 * @property string $authority
 * @property string|null $download_url
 * @property string|null $online_url
 * @property list<string> $keywords
 * @property bool $is_verified
 * @property int $hit_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class AdministrativeForm extends Model
{
    /** @use HasFactory<AdministrativeFormFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'domain',
        'form_code',
        'title',
        'title_en',
        'description',
        'authority',
        'download_url',
        'online_url',
        'keywords',
        'is_verified',
        'hit_count',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'keywords' => 'array',
            'is_verified' => 'boolean',
            'hit_count' => 'integer',
        ];
    }

    /**
     * Scope query to verified forms.
     *
     * @param  Builder<AdministrativeForm>  $query
     * @return Builder<AdministrativeForm>
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    /**
     * Return the best URL for the form (direct download preferred, else online portal).
     */
    public function primaryUrl(): string
    {
        return (string) ($this->download_url ?: $this->online_url);
    }

    /**
     * Return title localized if English requested.
     */
    public function titleForLocale(string $locale): string
    {
        if ($locale === 'en' && ! empty($this->title_en)) {
            return $this->title_en;
        }

        return $this->title;
    }

    /**
     * Find the most relevant administrative forms for a query and detected topics.
     *
     * @param  list<string>  $topics
     * @return Collection<int, AdministrativeForm>
     */
    public static function findRelevantForms(string $query, array $topics = [], string $locale = 'de', int $limit = 2): Collection
    {
        $normalized = mb_strtolower(trim($query));
        if ($normalized === '') {
            return new Collection;
        }

        try {
            $allForms = static::verified()->get();
        } catch (\Throwable) {
            return new Collection;
        }

        if ($allForms->isEmpty()) {
            return new Collection;
        }

        $isFormQuery = static::containsFormIntent($normalized);

        $scores = $allForms->mapWithKeys(function (AdministrativeForm $form) use ($normalized, $topics, $isFormQuery) {
            $score = $form->relevanceScore($normalized, $topics);
            if ($isFormQuery && $score > 0) {
                // Boost score significantly when the user explicitly asked for a form or application
                $score += 15;
            }

            return [$form->id => $score];
        });

        // Minimum threshold: 10 points
        $matched = $allForms
            ->filter(fn (AdministrativeForm $form) => $scores[$form->id] >= 10)
            ->sortByDesc(fn (AdministrativeForm $form) => $scores[$form->id])
            ->take($limit)
            ->values();

        if ($matched->isNotEmpty()) {
            static::whereIn('id', $matched->modelKeys())->increment('hit_count');
        }

        return $matched;
    }

    /**
     * Detect whether the query is seeking a form, application, or download.
     */
    public static function containsFormIntent(string $normalizedQuery): bool
    {
        $triggers = [
            'formular', 'form', 'formulare', 'antrag', 'antragsformular',
            'application', 'download', 'herunterladen', 'pdf', 'ausfüllen',
            'dokument herunterladen', 'wo finde ich den antrag',
        ];

        foreach ($triggers as $trigger) {
            if (str_contains($normalizedQuery, $trigger)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate relevance score of this form against the user's prompt and domains.
     *
     * @param  list<string>  $topics
     */
    public function relevanceScore(string $normalizedQuery, array $topics = []): int
    {
        $score = 0;

        // Domain match
        if (in_array($this->domain, $topics, true) || str_contains($normalizedQuery, mb_strtolower($this->domain))) {
            $score += 8;
        }

        // Form code match (e.g. "L1", "Meldezettel", "Beih100")
        $code = mb_strtolower(trim($this->form_code));
        if ($code !== '' && (str_contains($normalizedQuery, $code) || str_contains($code, $normalizedQuery))) {
            $score += 25;
        }

        // Title matches
        $lowerTitle = mb_strtolower($this->title);
        $lowerTitleEn = mb_strtolower((string) $this->title_en);

        if (str_contains($normalizedQuery, 'rot-weiß-rot') && (str_contains($lowerTitle, 'rot-weiß-rot') || str_contains($lowerTitle, 'aufenthaltstitel'))) {
            $score += 20;
        }

        if (str_contains($normalizedQuery, 'red-white-red') && (str_contains($lowerTitleEn, 'red-white-red') || str_contains($lowerTitleEn, 'residence title'))) {
            $score += 20;
        }

        // Keyword matches
        foreach ($this->keywords as $keyword) {
            $kw = mb_strtolower(trim($keyword));
            if ($kw !== '' && str_contains($normalizedQuery, $kw)) {
                $score += max(mb_strlen($kw) * 2, 6);
            }
        }

        return $score;
    }
}
