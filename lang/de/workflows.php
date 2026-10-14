<?php

/*
 * Kompetent – texts of the guided passport and residence workflows (checklists, deadlines, dossiers).
 *
 * Factual content: only reviewed languages have this file; others fall back (App\Enums\Language::contentChain).
 */

return [
    'passport' => [
        'fee' => [
            'format' => ':amount €',
            'decimal_separator' => ',',
            'thousands_separator' => '.',
            'free_first_issue' => 'Gebührenfrei (Erstausstellung) bzw. 30,00 €',
            'duration_one_day' => 'Zustellung am nächsten Werktag (Ein-Tages-Express)',
            'duration_express' => 'Zustellung in 2–3 Werktagen (Expresspass)',
            'duration_standard' => 'ca. 5 Werktage (RSb-Brief)',
            'validity' => ':years Jahre',
        ],
        'items' => [
            'loss_report' => [
                'text' => 'Amtliche Verlust- oder Diebstahlsanzeige',
                'detail' => 'Bei Diebstahl von der Polizei, bei Verlust vom Fundamt bzw. Passbehörde.',
            ],
            'other_photo_id' => [
                'text' => 'Anderer Lichtbildausweis (z. B. Führerschein, Personalausweis)',
                'detail' => 'Zur eindeutigen Feststellung Ihrer Identität am Schalter.',
            ],
            'certificates' => [
                'text' => 'Geburtsurkunde & Staatsbürgerschaftsnachweis',
                'detail' => 'Erforderlich, wenn der Reisepass länger als 5 Jahre abgelaufen ist.',
            ],
            'old_passport' => [
                'text' => 'Bisheriger Reisepass (abgelaufen oder noch gültig)',
                'detail' => 'Wird bei Neuausstellung entwertet und kann auf Wunsch behalten werden.',
            ],
            'photo' => [
                'text' => '1 aktuelles biometrisches Passfoto (35 x 45 mm)',
                'detail' => 'Nicht älter als 6 Monate, neutraler Blick, gleichmäßige Ausleuchtung.',
            ],
            'fee' => [
                'text' => 'Gebühr :fee (Bankomat oder bar)',
                'detail' => 'Beinhaltet Produktion und RSb-Postzustellung (:duration).',
            ],
            'appointment' => [
                'text' => 'Online-Termin bei der Passbehörde reserviert',
                'detail' => 'Sparen Sie Wartezeit – Magistratisches Bezirksamt oder Bezirkshauptmannschaft.',
            ],
        ],
        'dossier' => [
            'title' => '=== BÜRGER-DOSSIER: REISEPASS-NEUAUSSTELLUNG 2030 ===',
            'inquiry' => 'Anliegen: Neuausstellung / Verlängerung Reisepass',
            'scenario' => 'Status / Fall: :value',
            'urgency' => 'Dringlichkeit: :value (:duration)',
            'fee' => 'Berechnete Gebühr: :fee',
            'progress' => 'Dokumenten-Vorbereitung: :done von :total Punkten erledigt',
            'checklist' => 'Erledigte Dokumente:',
            'authority' => 'Offizielle Behörde: Jede Passbehörde in Österreich (Magistrat / Bezirkshauptmannschaft)',
            'default_value' => 'Standard',
        ],
    ],
    'residence' => [
        'deadline' => [
            'planning' => 'Perfekt vorbereitet! Bitte beachten Sie: Die 3-Tage-Frist beginnt erst mit dem tatsächlichen Einzug.',
            'within' => 'Sie befinden sich innerhalb der gesetzlichen 3-Tage-Frist (§ 1 Abs. 1 MeldeG). Keine Eile, aber bitte zeitnah erledigen!',
            'overdue' => 'Achtung: Die gesetzliche 3-Tage-Frist ist um :days Tage überschritten. Bitte melden Sie sich umgehend an, um Beanstandungen oder Verwaltungsstrafen zu vermeiden.',
        ],
        'items' => [
            'id_austria_ready' => [
                'text' => 'ID Austria (Vollfunktion) auf dem Smartphone aktiv',
                'detail' => 'Ermöglicht die 100 % digitale Meldung ohne Amtsbesuch in der App "Digitales Amt".',
            ],
            'meldezettel' => [
                'text' => 'Meldezettel-Formular vollständig ausgefüllt',
                'detail' => 'Offizielles Bundesformular mit Angaben zu Person und Wohnsitzart.',
            ],
            'landlord_signature' => [
                'text' => 'Unterschrift der Unterkunftgeberin / des Vermieters',
                'detail' => 'Zwingend erforderlich bei Mietwohnungen im Abschnitt "Bestätigung des Unterkunftgebers".',
            ],
            'owner_proof' => [
                'text' => 'Eigentumsnachweis (z. B. Grundbuchauszug oder Kaufvertrag)',
                'detail' => 'Als Eigentümer unterschreiben Sie selbst auf dem Meldezettel.',
            ],
            'photo_id' => [
                'text' => 'Amtlicher Lichtbildausweis (Reisepass oder Personalausweis)',
                'detail' => 'Für alle mitziehenden Personen erforderlich.',
            ],
            'car_registry' => [
                'text' => 'Folgepflicht: KFZ-Ummeldung binnen 1 Woche (falls anderer Bezirk)',
                'detail' => 'Bei Umzug in einen anderen Zulassungsbezirk bei einer Zulassungsstelle.',
            ],
            'orf_contribution' => [
                'text' => 'Folgepflicht: ORF-Beitrag (ehemals GIS) Adressänderung online',
                'detail' => 'Gilt pro Hauptwohnsitz-Haushalt, einfach online bei der OBS melden.',
            ],
        ],
        'dossier' => [
            'title' => '=== BÜRGER-DOSSIER: WOHNSITZMELDUNG 2030 ===',
            'inquiry' => 'Anliegen: Wohnsitz An- / Ummeldung',
            'type' => 'Art des Wohnsitzes: :value',
            'type_primary' => 'Hauptwohnsitz (Mittelpunkt der Lebensinteressen)',
            'type_secondary' => 'Nebenwohnsitz',
            'housing' => 'Wohnverhältnis: :value',
            'housing_rented' => 'Miete (Vermieter-Unterschrift erforderlich)',
            'housing_owner' => 'Eigentum',
            'channel' => 'Bevorzugter Weg: :value',
            'channel_id_austria' => 'Digital via ID Austria',
            'channel_in_person' => 'Persönlich am Meldeamt',
            'deadline' => 'Fristenstatus: :value',
            'fee' => 'Gebühr: 0,00 € (Gesetzlich gebührenfrei)',
            'progress' => 'Dokumenten-Vorbereitung: :done von :total Punkten erledigt',
            'checklist' => 'Dokumenten-Checkliste:',
            'authority' => 'Zuständige Behörde: Meldeamt der Gemeinde bzw. Magistratisches Bezirksamt in Wien',
        ],
    ],
];
