<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $session_id
 * @property string|null $title
 * @property string $locale
 * @property string|null $active_workflow
 * @property bool $is_escalated
 * @property Carbon|null $escalated_at
 * @property string|null $escalation_reason
 * @property string $status
 * @property string|null $case_dossier
 * @property string|null $officer_notes
 * @property string|null $assigned_officer
 * @property array<string, mixed>|null $workflow_data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, ConversationMessage> $messages
 */
class Conversation extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'session_id',
        'title',
        'locale',
        'active_workflow',
        'is_escalated',
        'escalated_at',
        'escalation_reason',
        'status',
        'case_dossier',
        'officer_notes',
        'assigned_officer',
        'workflow_data',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_escalated' => 'boolean',
            'escalated_at' => 'datetime',
            'workflow_data' => 'array',
        ];
    }

    /**
     * @return HasMany<ConversationMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class)->orderBy('id');
    }

    /**
     * Scope a query to only include escalated cases.
     *
     * @param  Builder<Conversation>  $query
     * @return Builder<Conversation>
     */
    public function scopeEscalated($query)
    {
        return $query->where('is_escalated', true);
    }
}
