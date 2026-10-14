<?php

namespace App\Services\AustrianGov\Workflows;

class ResidenceWorkflow
{
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
        public array $state = [
            'step' => 1,
            'move_status' => null,
            'days_since_move' => 0,
            'residence_type' => 'hauptwohnsitz',
            'housing_type' => 'rented',
            'channel' => 'id_austria',
            'checked_items' => [],
        ]
    ) {}

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
        $isGerman = str_starts_with(strtolower($locale), 'de');
        $status = $this->state['move_status'] ?? null;
        $days = (int) ($this->state['days_since_move'] ?? 0);

        if ($status === 'planning_to_move') {
            return [
                'is_compliant' => true,
                'warning' => $isGerman
                    ? 'Perfekt vorbereitet! Bitte beachten Sie: Die 3-Tage-Frist beginnt erst mit dem tatsächlichen Einzug.'
                    : 'Great preparation! The statutory 3-day deadline starts only once you physically move into the residence.',
                'severity' => 'ok',
            ];
        }

        if ($days <= 3) {
            return [
                'is_compliant' => true,
                'warning' => $isGerman
                    ? 'Sie befinden sich innerhalb der gesetzlichen 3-Tage-Frist (§ 1 Abs. 1 MeldeG). Keine Eile, aber bitte zeitnah erledigen!'
                    : 'You are within the statutory 3-day window under Austrian residence law. Please finalize registration soon.',
                'severity' => 'ok',
            ];
        }

        return [
            'is_compliant' => false,
            'warning' => $isGerman
                ? "Achtung: Die gesetzliche 3-Tage-Frist ist um {$days} Tage überschritten. Bitte melden Sie sich umgehend an, um Beanstandungen oder Verwaltungsstrafen zu vermeiden."
                : "Attention: The 3-day statutory deadline is exceeded by {$days} days. Please complete registration promptly to avoid administrative warnings.",
            'severity' => 'urgent',
        ];
    }

    /**
     * Generate dynamic checklist based on user choices.
     *
     * @return array<int, array{id: string, text: string, detail: string, required: bool, checked: bool}>
     */
    public function getChecklist(string $locale = 'de'): array
    {
        $isGerman = str_starts_with(strtolower($locale), 'de');
        $housing = $this->state['housing_type'] ?? 'rented';
        $channel = $this->state['channel'] ?? 'id_austria';
        $checked = $this->state['checked_items'] ?? [];

        $items = [];

        if ($channel === 'id_austria') {
            $items[] = [
                'id' => 'id_austria_ready',
                'text' => $isGerman ? 'ID Austria (Vollfunktion) auf dem Smartphone aktiv' : 'Active ID Austria on Smartphone',
                'detail' => $isGerman ? 'Ermöglicht die 100 % digitale Meldung ohne Amtsbesuch in der App "Digitales Amt".' : 'Enables 100% digital registration without visiting city hall.',
                'required' => true,
                'checked' => $checked['id_austria_ready'] ?? false,
            ];
        }

        $items[] = [
            'id' => 'meldezettel',
            'text' => $isGerman ? 'Meldezettel-Formular vollständig ausgefüllt' : 'Meldezettel Application Form Filled Out',
            'detail' => $isGerman ? 'Offizielles Bundesformular mit Angaben zu Person und Wohnsitzart.' : 'Official registration form with personal and address details.',
            'required' => true,
            'checked' => $checked['meldezettel'] ?? false,
        ];

        if ($housing === 'rented') {
            $items[] = [
                'id' => 'landlord_signature',
                'text' => $isGerman ? 'Unterschrift der Unterkunftgeberin / des Vermieters' : 'Landlord / Property Owner Signature',
                'detail' => $isGerman ? 'Zwingend erforderlich bei Mietwohnungen im Abschnitt "Bestätigung des Unterkunftgebers".' : 'Legally mandatory on the form for tenants/renters.',
                'required' => true,
                'checked' => $checked['landlord_signature'] ?? false,
            ];
        } else {
            $items[] = [
                'id' => 'owner_proof',
                'text' => $isGerman ? 'Eigentumsnachweis (z. B. Grundbuchauszug oder Kaufvertrag)' : 'Proof of Ownership (Title deed / Land registry)',
                'detail' => $isGerman ? 'Als Eigentümer unterschreiben Sie selbst auf dem Meldezettel.' : 'As homeowner, you sign the landlord section yourself.',
                'required' => false,
                'checked' => $checked['owner_proof'] ?? false,
            ];
        }

        $items[] = [
            'id' => 'photo_id',
            'text' => $isGerman ? 'Amtlicher Lichtbildausweis (Reisepass oder Personalausweis)' : 'Official Photo ID (Passport or EU National ID)',
            'detail' => $isGerman ? 'Für alle mitziehenden Personen erforderlich.' : 'Required for all moving household members.',
            'required' => true,
            'checked' => $checked['photo_id'] ?? false,
        ];

        $items[] = [
            'id' => 'car_registry',
            'text' => $isGerman ? 'Folgepflicht: KFZ-Ummeldung binnen 1 Woche (falls anderer Bezirk)' : 'Follow-up: Vehicle registration update within 1 week',
            'detail' => $isGerman ? 'Bei Umzug in einen anderen Zulassungsbezirk bei einer Zulassungsstelle.' : 'If moving into a different motor vehicle registration district.',
            'required' => false,
            'checked' => $checked['car_registry'] ?? false,
        ];

        $items[] = [
            'id' => 'orf_contribution',
            'text' => $isGerman ? 'Folgepflicht: ORF-Beitrag (ehemals GIS) Adressänderung online' : 'Follow-up: Public broadcast fee (ORF-Beitrag) address update',
            'detail' => $isGerman ? 'Gilt pro Hauptwohnsitz-Haushalt, einfach online bei der OBS melden.' : 'Per household fee; update address online on the OBS portal.',
            'required' => false,
            'checked' => $checked['orf_contribution'] ?? false,
        ];

        return $items;
    }

    /**
     * Generate summary text for official dossier.
     */
    public function generateDossierSummary(string $locale = 'de'): string
    {
        $isGerman = str_starts_with(strtolower($locale), 'de');
        $checklist = $this->getChecklist($locale);
        $deadline = $this->getDeadlineStatus($locale);

        $completedCount = count(array_filter($checklist, fn ($i) => $i['checked']));
        $totalCount = count($checklist);

        if ($isGerman) {
            $out = "=== BÜRGER-DOSSIER: WOHNSITZMELDUNG 2030 ===\n";
            $out .= "Anliegen: Wohnsitz An- / Ummeldung\n";
            $out .= 'Art des Wohnsitzes: '.($this->state['residence_type'] === 'hauptwohnsitz' ? 'Hauptwohnsitz (Mittelpunkt der Lebensinteressen)' : 'Nebenwohnsitz')."\n";
            $out .= 'Wohnverhältnis: '.($this->state['housing_type'] === 'rented' ? 'Miete (Vermieter-Unterschrift erforderlich)' : 'Eigentum')."\n";
            $out .= 'Bevorzugter Weg: '.($this->state['channel'] === 'id_austria' ? 'Digital via ID Austria' : 'Persönlich am Meldeamt')."\n";
            $out .= "Fristenstatus: {$deadline['warning']}\n";
            $out .= "Gebühr: 0,00 € (Gesetzlich gebührenfrei)\n";
            $out .= "Dokumenten-Vorbereitung: {$completedCount} von {$totalCount} Punkten erledigt\n\n";
            $out .= "Dokumenten-Checkliste:\n";
            foreach ($checklist as $item) {
                $mark = $item['checked'] ? '[X]' : '[ ]';
                $out .= "{$mark} {$item['text']}\n";
            }
            $out .= "\nZuständige Behörde: Meldeamt der Gemeinde bzw. Magistratisches Bezirksamt in Wien\n";

            return $out;
        }

        $out = "=== CITIZEN DOSSIER: RESIDENCE REGISTRATION 2030 ===\n";
        $out .= "Inquiry: Change of Residence / Registration\n";
        $out .= 'Residence Category: '.($this->state['residence_type'] === 'hauptwohnsitz' ? 'Primary Residence (Hauptwohnsitz)' : 'Secondary Residence (Nebenwohnsitz)')."\n";
        $out .= 'Housing Status: '.($this->state['housing_type'] === 'rented' ? 'Rental (Landlord signature required)' : 'Owner-occupied')."\n";
        $out .= 'Registration Channel: '.($this->state['channel'] === 'id_austria' ? 'Digital via ID Austria' : 'In person at registration office')."\n";
        $out .= "Deadline Compliance: {$deadline['warning']}\n";
        $out .= "Cost: €0.00 (Free of charge by law)\n";
        $out .= "Document Status: {$completedCount} of {$totalCount} items prepared\n\n";
        $out .= "Checklist Details:\n";
        foreach ($checklist as $item) {
            $mark = $item['checked'] ? '[X]' : '[ ]';
            $out .= "{$mark} {$item['text']}\n";
        }
        $out .= "\nCompetent Office: Municipal registration office (Gemeindeamt / Magistrat in Vienna)\n";

        return $out;
    }
}
