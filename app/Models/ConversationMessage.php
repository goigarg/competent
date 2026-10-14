<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $conversation_id
 * @property string|null $uuid
 * @property string $role
 * @property string $content
 * @property array<int, array<string, mixed>>|null $sources
 * @property bool $is_fallback
 * @property string|null $workflow
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ConversationMessage extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'conversation_id',
        'uuid',
        'role',
        'content',
        'sources',
        'is_fallback',
        'workflow',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sources' => 'array',
            'is_fallback' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Conversation, $this>
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
