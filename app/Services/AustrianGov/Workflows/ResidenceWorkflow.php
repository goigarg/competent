<?php

namespace App\Services\AustrianGov\Workflows;

use App\Support\Translate;

class ResidenceWorkflow
{
    public const DEFAULT_STATE = [
        'step' => 1,
        'move_status' => null,
        'days_since_move' => 0,
        'residence_type' => 'hauptwohnsitz',
        'housing_type' => 'rented',
        'channel' => 'id_austria',
        'checked_items' => [],
    ];

    /**
     * @param  array{
     *     step: int,
     *     move_status: ?string,
     *     days_since_move: int,
     *     residence_type: string,
     *     housing_type: string,
     *     channel: string,
     *     checked_items: array<string, bool>
     * }  $state
     */
    public function __construct(
        public array $state = self::DEFAULT_STATE
    ) {}

    /**
     * Build a complete, well-typed state from stored data (e.g. conversations.workflow_data),
     * falling back to the defaults for missing or invalid keys.
     *
     * @return array{step: int, move_status: ?string, days_since_move: int, residence_type: string, housing_type: string, channel: string, checked_items: array<string, bool>}
     */
    public static function normalizeState(mixed $data): array
    {
        $data = is_array($data) ? $data : [];
        $string = fn (string $key, string $default) => is_string($data[$key] ?? null) ? $data[$key] : $default;

        return [
            'step' => is_int($data['step'] ?? null) ? $data['step'] : self::DEFAULT_STATE['step'],
            'move_status' => is_string($data['move_status'] ?? null) ? $data['move_status'] : self::DEFAULT_STATE['move_status'],
            'days_since_move' => is_int($data['days_since_move'] ?? null) ? $data['days_since_move'] : self::DEFAULT_STATE['days_since_move'],
            'residence_type' => $string('residence_type', self::DEFAULT_STATE['residence_type']),
            'housing_type' => $string('housing_type', self::DEFAULT_STATE['housing_type']),
            'channel' => $string('channel', self::DEFAULT_STATE['channel']),
            'checked_items' => PassportWorkflow::normalizeChecked($data['checked_items'] ?? []),
        ];
    }

    public function setMoveStatus(string $status, int $daysSinceMove = 0): void
    {
        $this->state['move_status'] = $status;
        $this->state['days_since_move'] = $daysSinceMove;
        if ($this->state['step'] === 1) {
            $this->state['step'] = 2;
        }
    }

    public function setResidenceType(string $type): void
    {
        $this->state['residence_type'] = $type;
        if ($this->state['step'] === 2) {
            $this->state['step'] = 3;
        }
    }

    public function setHousingType(string $type): void
    {
        $this->state['housing_type'] = $type;
        if ($this->state['step'] === 3) {
            $this->state['step'] = 4;
        }
    }

    public function setChannel(string $channel): void
    {
        $this->state['channel'] = $channel;
        $this->state['step'] = 5;
    }

    public function toggleItem(string $key): void
    {
        $current = $this->state['checked_items'][$key] ?? false;
        $this->state['checked_items'][$key] = ! $current;
    }

    /**
     * Determine legal compliance status with the Austrian 3-day deadline.
     *
     * @return array{is_compliant: bool, warning: ?string, severity: 'ok'|'warning'|'urgent'}
     */
    public function getDeadlineStatus(string $locale = 'de'): array
    {
        $status = $this->state['move_status'];
        $days = $this->state['days_since_move'];

        if ($status === 'planning_to_move') {
            return ['is_compliant' => true, 'warning' => $this->text('deadline.planning', $locale), 'severity' => 'ok'];
        }

        if ($days <= 3) {
            return ['is_compliant' => true, 'warning' => $this->text('deadline.within', $locale), 'severity' => 'ok'];
        }

        return ['is_compliant' => false, 'warning' => $this->text('deadline.overdue', $locale, ['days' => $days]), 'severity' => 'urgent'];
    }

    /**
     * Generate dynamic checklist based on user choices.
     *
     * @return array<int, array{id: string, text: string, detail: string, required: bool, checked: bool}>
     */
    public function getChecklist(string $locale = 'de'): array
    {
        $ids = [
            ...($this->state['channel'] === 'id_austria' ? ['id_austria_ready'] : []),
            'meldezettel',
            $this->state['housing_type'] === 'rented' ? 'landlord_signature' : 'owner_proof',
            'photo_id',
            'car_registry',
            'orf_contribution',
        ];
        $optional = ['owner_proof', 'car_registry', 'orf_contribution'];

        $items = [];
        foreach ($ids as $id) {
            $items[] = [
                'id' => $id,
                'text' => $this->text("items.{$id}.text", $locale),
                'detail' => $this->text("items.{$id}.detail", $locale),
                'required' => ! in_array($id, $optional, true),
                'checked' => $this->state['checked_items'][$id] ?? false,
            ];
        }

        return $items;
    }

    /**
     * Generate summary text for official dossier.
     */
    public function generateDossierSummary(string $locale = 'de'): string
    {
        $checklist = $this->getChecklist($locale);
        $deadline = $this->getDeadlineStatus($locale);

        $out = $this->text('dossier.title', $locale)."\n";
        $out .= $this->text('dossier.inquiry', $locale)."\n";
        $out .= $this->text('dossier.type', $locale, ['value' => $this->text($this->state['residence_type'] === 'hauptwohnsitz' ? 'dossier.type_primary' : 'dossier.type_secondary', $locale)])."\n";
        $out .= $this->text('dossier.housing', $locale, ['value' => $this->text($this->state['housing_type'] === 'rented' ? 'dossier.housing_rented' : 'dossier.housing_owner', $locale)])."\n";
        $out .= $this->text('dossier.channel', $locale, ['value' => $this->text($this->state['channel'] === 'id_austria' ? 'dossier.channel_id_austria' : 'dossier.channel_in_person', $locale)])."\n";
        $out .= $this->text('dossier.deadline', $locale, ['value' => (string) $deadline['warning']])."\n";
        $out .= $this->text('dossier.fee', $locale)."\n";
        $out .= $this->text('dossier.progress', $locale, [
            'done' => count(array_filter($checklist, fn ($i) => $i['checked'])),
            'total' => count($checklist),
        ])."\n\n";
        $out .= $this->text('dossier.checklist', $locale)."\n";
        foreach ($checklist as $item) {
            $out .= ($item['checked'] ? '[X]' : '[ ]')." {$item['text']}\n";
        }
        $out .= "\n".$this->text('dossier.authority', $locale)."\n";

        return $out;
    }

    /**
     * @param  array<string, string|int|float>  $replace
     */
    protected function text(string $key, string $locale, array $replace = []): string
    {
        return Translate::text("workflows.residence.{$key}", $replace, $locale);
    }
}
