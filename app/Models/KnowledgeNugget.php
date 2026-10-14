<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeNugget extends Model
{
    use HasFactory;

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

        $scored = $allNuggets->map(function (KnowledgeNugget $nugget) use ($normalized) {
            $score = 0;
            $keywords = is_array($nugget->keywords) ? $nugget->keywords : [];

            foreach ($keywords as $kw) {
                $kwLower = mb_strtolower(trim((string) $kw));
                if ($kwLower !== '' && str_contains($normalized, $kwLower)) {
                    $score += max(mb_strlen($kwLower) * 2, 8);
                }
            }

            // Also check domain match
            if (str_contains($normalized, mb_strtolower($nugget->domain))) {
                $score += 5;
            }

            return [
                'nugget' => $nugget,
                'score' => $score,
            ];
        })->filter(fn ($item) => $item['score'] >= 8)
            ->sortByDesc('score')
            ->take($limit)
            ->pluck('nugget');

        // Increment hit counter for matched knowledge nuggets
        if ($scored->isNotEmpty()) {
            static::whereIn('id', $scored->pluck('id'))->increment('hit_count');
        }

        return new Collection($scored->all());
    }
}
