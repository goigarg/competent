<?php

namespace App\Services\AustrianGov\Workflows;

class PassportWorkflow
{
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
        public array $state = [
            'step' => 1,
            'scenario' => null,
            'urgency' => 'standard',
            'age_group' => 'adult',
            'checked_items' => [],
            'authority_location' => null,
        ]
    ) {}

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
    public function getFeeInfo(): array
    {
        $age = $this->state['age_group'] ?? 'adult';
        $urgency = $this->state['urgency'] ?? 'standard';

        if ($urgency === 'one_day') {
            return [
                'amount' => 220.00,
                'formatted' => '220,00 €',
                'duration' => 'Zustellung am nächsten Werktag (Ein-Tages-Express)',
                'validity' => $age === 'child_small' ? '2 Jahre' : ($age === 'child' ? '5 Jahre' : '10 Jahre'),
            ];
        }

        if ($urgency === 'express') {
            return [
                'amount' => 100.00,
                'formatted' => '100,00 €',
                'duration' => 'Zustellung in 2–3 Werktagen (Expresspass)',
                'validity' => $age === 'child_small' ? '2 Jahre' : ($age === 'child' ? '5 Jahre' : '10 Jahre'),
            ];
        }

        // Standard
        if ($age === 'child_small') {
            return [
                'amount' => 0.00,
                'formatted' => 'Gebührenfrei (Erstausstellung) bzw. 30,00 €',
                'duration' => 'ca. 5 Werktage (RSb-Brief)',
                'validity' => '2 Jahre',
            ];
        }

        if ($age === 'child') {
            return [
                'amount' => 30.00,
                'formatted' => '30,00 €',
                'duration' => 'ca. 5 Werktage (RSb-Brief)',
                'validity' => '5 Jahre',
            ];
        }

        return [
            'amount' => 75.90,
            'formatted' => '75,90 €',
            'duration' => 'ca. 5 Werktage (RSb-Brief)',
            'validity' => '10 Jahre',
        ];
    }

    /**
     * Generate dynamic checklist based on user state.
     *
     * @return array<int, array{id: string, text: string, detail: string, required: bool, checked: bool}>
     */
    public function getChecklist(string $locale = 'de'): array
    {
        $isGerman = str_starts_with(strtolower($locale), 'de');
        $scenario = $this->state['scenario'] ?? 'expiring';
        $checked = $this->state['checked_items'] ?? [];

        $items = [];

        if ($scenario === 'lost_stolen') {
            $items[] = [
                'id' => 'loss_report',
                'text' => $isGerman ? 'Amtliche Verlust- oder Diebstahlsanzeige' : 'Official Loss or Police Theft Report',
                'detail' => $isGerman ? 'Bei Diebstahl von der Polizei, bei Verlust vom Fundamt bzw. Passbehörde.' : 'From police in case of theft, or municipal lost & found for loss.',
                'required' => true,
                'checked' => $checked['loss_report'] ?? false,
            ];
            $items[] = [
                'id' => 'other_photo_id',
                'text' => $isGerman ? 'Anderer Lichtbildausweis (z. B. Führerschein, Personalausweis)' : 'Alternative Photo ID (e.g., Driving Licence or EU ID Card)',
                'detail' => $isGerman ? 'Zur eindeutigen Feststellung Ihrer Identität am Schalter.' : 'To verify your identity at the service desk.',
                'required' => true,
                'checked' => $checked['other_photo_id'] ?? false,
            ];
        } elseif ($scenario === 'expired_over_5_years') {
            $items[] = [
                'id' => 'certificates',
                'text' => $isGerman ? 'Geburtsurkunde & Staatsbürgerschaftsnachweis' : 'Birth Certificate & Citizenship Proof',
                'detail' => $isGerman ? 'Erforderlich, wenn der Reisepass länger als 5 Jahre abgelaufen ist.' : 'Mandatory when passport has been expired for more than 5 years.',
                'required' => true,
                'checked' => $checked['certificates'] ?? false,
            ];
        } else {
            $items[] = [
                'id' => 'old_passport',
                'text' => $isGerman ? 'Bisheriger Reisepass (abgelaufen oder noch gültig)' : 'Current or Expired Passport',
                'detail' => $isGerman ? 'Wird bei Neuausstellung entwertet und kann auf Wunsch behalten werden.' : 'Will be cancelled and handed back to you as a keepsake if desired.',
                'required' => true,
                'checked' => $checked['old_passport'] ?? false,
            ];
        }

        $items[] = [
            'id' => 'photo',
            'text' => $isGerman ? '1 aktuelles biometrisches Passfoto (35 x 45 mm)' : '1 Recent Biometric Passport Photograph (35 x 45 mm)',
            'detail' => $isGerman ? 'Nicht älter als 6 Monate, neutraler Blick, gleichmäßige Ausleuchtung.' : 'Max 6 months old, neutral expression, high resolution.',
            'required' => true,
            'checked' => $checked['photo'] ?? false,
        ];

        $fee = $this->getFeeInfo();
        $items[] = [
            'id' => 'fee',
            'text' => $isGerman ? "Gebühr {$fee['formatted']} (Bankomat oder bar)" : "Fee {$fee['formatted']} (Debit card or cash)",
            'detail' => $isGerman ? "Beinhaltet Produktion und RSb-Postzustellung ({$fee['duration']})." : "Includes manufacturing and registered postal delivery ({$fee['duration']}).",
            'required' => true,
            'checked' => $checked['fee'] ?? false,
        ];

        $items[] = [
            'id' => 'appointment',
            'text' => $isGerman ? 'Online-Termin bei der Passbehörde reserviert' : 'Booked Online Appointment at Passport Office',
            'detail' => $isGerman ? 'Sparen Sie Wartezeit – Magistratisches Bezirksamt oder Bezirkshauptmannschaft.' : 'Avoid waiting times at your local district municipal office.',
            'required' => false,
            'checked' => $checked['appointment'] ?? false,
        ];

        return $items;
    }

    /**
     * Return summary text for civil servant dossier.
     */
    public function generateDossierSummary(string $locale = 'de'): string
    {
        $isGerman = str_starts_with(strtolower($locale), 'de');
        $fee = $this->getFeeInfo();
        $checklist = $this->getChecklist($locale);

        $completedCount = count(array_filter($checklist, fn ($i) => $i['checked']));
        $totalCount = count($checklist);

        if ($isGerman) {
            $out = "=== BÜRGER-DOSSIER: REISEPASS-NEUAUSSTELLUNG 2030 ===\n";
            $out .= "Anliegen: Neuausstellung / Verlängerung Reisepass\n";
            $out .= 'Status / Fall: '.($this->state['scenario'] ?? 'Standard')."\n";
            $out .= 'Dringlichkeit: '.($this->state['urgency'] ?? 'Standard')." ({$fee['duration']})\n";
            $out .= "Berechnete Gebühr: {$fee['formatted']}\n";
            $out .= "Dokumenten-Vorbereitung: {$completedCount} von {$totalCount} Punkten erledigt\n\n";
            $out .= "Erledigte Dokumente:\n";
            foreach ($checklist as $item) {
                $mark = $item['checked'] ? '[X]' : '[ ]';
                $out .= "{$mark} {$item['text']}\n";
            }
            $out .= "\nOffizielle Behörde: Jede Passbehörde in Österreich (Magistrat / Bezirkshauptmannschaft)\n";

            return $out;
        }

        $out = "=== CITIZEN DOSSIER: AUSTRIAN PASSPORT APPLICATION 2030 ===\n";
        $out .= "Inquiry: Passport Issuance / Renewal\n";
        $out .= 'Case Scenario: '.($this->state['scenario'] ?? 'Standard')."\n";
        $out .= 'Urgency: '.($this->state['urgency'] ?? 'Standard')." ({$fee['duration']})\n";
        $out .= "Calculated Fee: {$fee['formatted']}\n";
        $out .= "Document Preparation: {$completedCount} of {$totalCount} items prepared\n\n";
        $out .= "Checklist Status:\n";
        foreach ($checklist as $item) {
            $mark = $item['checked'] ? '[X]' : '[ ]';
            $out .= "{$mark} {$item['text']}\n";
        }
        $out .= "\nOfficial Authority: Any passport office in Austria (Magistrate / District Commission)\n";

        return $out;
    }
}
