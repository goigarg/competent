<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $domain
 * @property string $topic
 * @property string $title
 * @property list<string> $keywords
 * @property string $factual_context
 * @property string|null $official_source_url
 * @property bool $is_verified
 * @property int $hit_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class KnowledgeNugget extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'domain',
        'topic',
        'title',
        'keywords',
        'factual_context',
        'official_source_url',
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
     * Scope a query to only include verified nuggets.
     *
     * @param  Builder<KnowledgeNugget>  $query
     * @return Builder<KnowledgeNugget>
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    /**
     * Find the most relevant factual nuggets for a user's prompt.
     *
     * @return Collection<int, KnowledgeNugget>
     */
    public static function findRelevantNuggets(string $query, int $limit = 2): Collection
    {
        $normalized = mb_strtolower(trim($query));
        if ($normalized === '') {
            return new Collection;
        }

        try {
            $allNuggets = static::verified()->get();
        } catch (\Throwable) {
            return new Collection;
        }

        if ($allNuggets->isEmpty()) {
            return new Collection;
        }

        $scores = $allNuggets->mapWithKeys(fn (KnowledgeNugget $nugget) => [$nugget->id => $nugget->relevanceScore($normalized)]);

        $matched = $allNuggets
            ->filter(fn (KnowledgeNugget $nugget) => $scores[$nugget->id] >= 8)
            ->sortByDesc(fn (KnowledgeNugget $nugget) => $scores[$nugget->id])
            ->take($limit)
            ->values();

        // Increment hit counter for matched knowledge nuggets
        if ($matched->isNotEmpty()) {
            static::whereIn('id', $matched->modelKeys())->increment('hit_count');
        }

        return $matched;
    }

    /**
     * Keyword score of this nugget for an already lower-cased query (8 or more counts as relevant).
     */
    public function relevanceScore(string $normalizedQuery): int
    {
        $score = 0;

        foreach ($this->keywords as $keyword) {
            $keyword = mb_strtolower(trim($keyword));
            if ($keyword !== '' && str_contains($normalizedQuery, $keyword)) {
                $score += max(mb_strlen($keyword) * 2, 8);
            }
        }

        // Also check domain match
        if (str_contains($normalizedQuery, mb_strtolower($this->domain))) {
            $score += 5;
        }

        return $score;
    }
}
