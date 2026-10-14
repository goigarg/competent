<?php

namespace App\Services\AustrianGov\Workflows;

use App\Support\Translate;

class PassportWorkflow
{
    public const DEFAULT_STATE = [
        'step' => 1,
        'scenario' => null,
        'urgency' => 'standard',
        'age_group' => 'adult',
        'checked_items' => [],
        'authority_location' => null,
    ];

    /**
     * @param  array{
     *     step: int,
     *     scenario: ?string,
     *     urgency: ?string,
     *     age_group: ?string,
     *     checked_items: array<string, bool>,
     *     authority_location: ?string
     * }  $state
     */
    public function __construct(
        public array $state = self::DEFAULT_STATE
    ) {}

    /**
     * Build a complete, well-typed state from stored data (e.g. conversations.workflow_data),
     * falling back to the defaults for missing or invalid keys.
     *
     * @return array{step: int, scenario: ?string, urgency: ?string, age_group: ?string, checked_items: array<string, bool>, authority_location: ?string}
     */
    public static function normalizeState(mixed $data): array
    {
        $data = is_array($data) ? $data : [];
        $nullableString = fn (string $key, ?string $default) => array_key_exists($key, $data) && (is_string($data[$key]) || $data[$key] === null) ? $data[$key] : $default;

        return [
            'step' => is_int($data['step'] ?? null) ? $data['step'] : self::DEFAULT_STATE['step'],
            'scenario' => $nullableString('scenario', self::DEFAULT_STATE['scenario']),
            'urgency' => $nullableString('urgency', self::DEFAULT_STATE['urgency']),
            'age_group' => $nullableString('age_group', self::DEFAULT_STATE['age_group']),
            'checked_items' => self::normalizeChecked($data['checked_items'] ?? []),
            'authority_location' => $nullableString('authority_location', self::DEFAULT_STATE['authority_location']),
        ];
    }

    /**
     * @return array<string, bool>
     */
    public static function normalizeChecked(mixed $items): array
    {
        $checked = [];

        foreach (is_array($items) ? $items : [] as $key => $value) {
            $checked[(string) $key] = (bool) $value;
        }

        return $checked;
    }

    /**
     * Set the passport situation.
     */
    public function setScenario(string $scenario): void
    {
        $this->state['scenario'] = $scenario;
        if ($this->state['step'] === 1) {
            $this->state['step'] = 2;
        }
    }

    /**
     * Set the urgency level.
     */
    public function setUrgency(string $urgency): void
    {
        $this->state['urgency'] = $urgency;
        if ($this->state['step'] === 2) {
            $this->state['step'] = 3;
        }
    }

    /**
     * Set age group.
     */
    public function setAgeGroup(string $ageGroup): void
    {
        $this->state['age_group'] = $ageGroup;
    }

    /**
     * Toggle a checklist item.
     */
    public function toggleItem(string $key): void
    {
        $current = $this->state['checked_items'][$key] ?? false;
        $this->state['checked_items'][$key] = ! $current;
    }

    /**
     * Calculate official fee based on situation and age.
     *
     * @return array{amount: float, formatted: string, duration: string, validity: string}
     */
    public function getFeeInfo(string $locale = 'de'): array
    {
        $age = $this->state['age_group'] ?? 'adult';
        $urgency = $this->state['urgency'] ?? 'standard';
        $years = match ($age) {
            'child_small' => 2,
            'child' => 5,
            default => 10,
        };
        $validity = $this->text('fee.validity', $locale, ['years' => $years]);

        [$amount, $duration] = match (true) {
            $urgency === 'one_day' => [220.00, 'fee.duration_one_day'],
            $urgency === 'express' => [100.00, 'fee.duration_express'],
            $age === 'child_small' => [0.00, 'fee.duration_standard'],
            $age === 'child' => [30.00, 'fee.duration_standard'],
            default => [75.90, 'fee.duration_standard'],
        };

        return [
            'amount' => $amount,
            'formatted' => $urgency === 'standard' && $age === 'child_small'
                ? $this->text('fee.free_first_issue', $locale)
                : $this->formatEuro($amount, $locale),
            'duration' => $this->text($duration, $locale),
            'validity' => $validity,
        ];
    }

    /**
     * Generate dynamic checklist based on user state.
     *
     * @return array<int, array{id: string, text: string, detail: string, required: bool, checked: bool}>
     */
    public function getChecklist(string $locale = 'de'): array
    {
        $scenario = $this->state['scenario'] ?? 'expiring';
        $fee = $this->getFeeInfo($locale);

        $ids = match ($scenario) {
            'lost_stolen' => ['loss_report', 'other_photo_id'],
            'expired_over_5_years' => ['certificates'],
            default => ['old_passport'],
        };

        $items = [];
        foreach ([...$ids, 'photo', 'fee', 'appointment'] as $id) {
            $items[] = [
                'id' => $id,
                'text' => $this->text("items.{$id}.text", $locale, ['fee' => $fee['formatted']]),
                'detail' => $this->text("items.{$id}.detail", $locale, ['duration' => $fee['duration']]),
                'required' => $id !== 'appointment',
                'checked' => $this->state['checked_items'][$id] ?? false,
            ];
        }

        return $items;
    }

    /**
     * Return summary text for civil servant dossier.
     */
    public function generateDossierSummary(string $locale = 'de'): string
    {
        $fee = $this->getFeeInfo($locale);
        $checklist = $this->getChecklist($locale);
        $default = $this->text('dossier.default_value', $locale);

        $out = $this->text('dossier.title', $locale)."\n";
        $out .= $this->text('dossier.inquiry', $locale)."\n";
        $out .= $this->text('dossier.scenario', $locale, ['value' => $this->state['scenario'] ?? $default])."\n";
        $out .= $this->text('dossier.urgency', $locale, ['value' => $this->state['urgency'] ?? $default, 'duration' => $fee['duration']])."\n";
        $out .= $this->text('dossier.fee', $locale, ['fee' => $fee['formatted']])."\n";
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

    protected function formatEuro(float $amount, string $locale): string
    {
        $number = number_format($amount, 2, $this->text('fee.decimal_separator', $locale), $this->text('fee.thousands_separator', $locale));

        return $this->text('fee.format', $locale, ['amount' => $number]);
    }

    /**
     * @param  array<string, string|int|float>  $replace
     */
    protected function text(string $key, string $locale, array $replace = []): string
    {
        return Translate::text("workflows.passport.{$key}", $replace, $locale);
    }
}
