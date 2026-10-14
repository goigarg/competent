<?php

/*
 * Kompetent – Messages the assistant writes itself (welcome, guided workflows, human support).
 * Factual content: only reviewed languages have this file; others fall back (see App\Enums\Language::contentChain).
 */

return [
    'dossier_general' => '### Bürger-Dossier (Menschliche Sachbearbeitung angefordert)

* **Datum/Uhrzeit:** :datetime Uhr
* **Status:** Offen für Sachbearbeiter-Prüfung
* **Sprache:** Deutsch

#### Bürger-Anfragen im Chat:
* :requests',
    'dossier_general_empty' => 'Allgemeine behördliche Auskunftsanfrage',
    'welcome' => 'Grüß Gott! Ich bin Ihre offizielle **KI-Bürgerassistenz der Republik Österreich**.

Ich übersetze Amtsdeutsch in einfache, klare Schritte und helfe Ihnen bei amtlichen Erledigungen – ohne langes Suchen. Wählen Sie ein Anliegen oder fragen Sie einfach in Ihren eigenen Worten:',
    'passport_intro' => '### Interaktiver Reisepass-Assistent

Ich begleite Sie Schritt für Schritt durch die Ausstellung Ihres neuen Reisepasses. Bitte wählen Sie Ihre aktuelle Ausgangssituation:',
    'residence_intro' => '### Interaktiver Wohnsitz-Assistent (Meldewesen)

In Österreich gilt eine gesetzliche Meldepflicht von 3 Tagen ab Einzug. Wann sind Sie umgezogen bzw. planen Sie den Umzug?',
    'passport_scenario' => [
        'expiring' => 'Reisepass läuft bald ab',
        'expired_recently' => 'Reisepass kürzlich abgelaufen (< 5 Jahre)',
        'expired_over_5_years' => 'Reisepass seit über 5 Jahren abgelaufen',
        'lost_stolen' => 'Reisepass verloren oder gestohlen',
        'child' => 'Reisepass für ein Kind',
    ],
    'passport_scenario_ack' => 'Verstanden: **:label**.

Wie schnell benötigen Sie das Dokument? Wählen Sie bitte die gewünschte Zustell-Geschwindigkeit:',
    'passport_overview' => '### Ihre persönliche Reisepass-Übersicht

* **Offizielle Gebühr:** **:fee**
* **Lieferzeit:** :duration
* **Gültigkeit:** :validity

Hier ist Ihre persönliche Dokumenten-Checkliste. Haken Sie die Punkte ab, sobald Sie diese vorbereitet haben:',
    'residence_planning' => 'Ich plane einen Umzug',
    'residence_moved' => 'Ich bin bereits umgezogen (:days Tage)',
    'residence_type_question' => 'Handelt es sich bei der neuen Unterkunft um Ihren **Hauptwohnsitz** (Mittelpunkt der Lebensbeziehungen) oder einen **Nebenwohnsitz**?',
    'residence_type_primary' => 'Hauptwohnsitz',
    'residence_type_secondary' => 'Nebenwohnsitz',
    'residence_housing_question' => 'Wohnen Sie in einer **Mietwohnung** oder sind Sie **Eigentümerin / Eigentümer** der Immobilie?',
    'residence_housing_rented' => 'Mietwohnung (Miete)',
    'residence_housing_owner' => 'Eigentum (Haus/Wohnung)',
    'residence_hint_rented' => '⚠️ **Wichtig:** Da Sie zur Miete wohnen, muss die Unterkunftgeberin / der Vermieter den Meldezettel im Original oder digital bestätigen.',
    'residence_hint_owner' => 'Als Eigentümerin/Eigentümer unterschreiben Sie den Meldezettel selbst im Abschnitt Unterkunftgeber.',
    'residence_channel_question' => 'Wie möchten Sie die Meldung durchführen? Wählen Sie den gewünschten Kanal:',
    'residence_channel_id_austria' => 'Digital über ID Austria (App Digitales Amt)',
    'residence_channel_in_person' => 'Persönlich am Meldeamt / Gemeindeamt',
    'residence_checklist_intro' => '### Checkliste für Ihre Wohnsitzanmeldung

* **Gebühr:** **0,00 €** (Vollständig gebührenfrei!)
* **Frist:** Binnen 3 Werktagen ab Einzug
* **Abmeldung alter Wohnsitz:** Erfolgt automatisch bei Neuanmeldung!

Hier ist Ihre Checkliste inklusive wichtiger Folge-Erledigungen (KFZ, ORF-Beitrag):',
    'escalation_reason_chat' => 'Bürgeranfrage im Chat nach persönlicher Beratung',
    'escalation_reason_default' => 'Bürgeranfrage: Persönliche Unterstützung / Beamtentelefon angefordert',
    'limited_mode_notice' => '> [!NOTE]
> **Eingeschränkter Modus:** Der KI-Dienst ist gerade nicht erreichbar. Sie erhalten hinterlegte Standardinformationen. Bitte prüfen Sie diese bei der Behörde oder sprechen Sie mit einer Person.',
    'human_support' => '### Persönliche menschliche Unterstützung (Fall an Beamten-Portal übermittelt)

Gerne vermittle ich Sie an die offiziellen menschlichen Ansprechpartner der österreichischen Verwaltung. Ihr Fall wurde erfasst und ist nun im **Beamtinnen- & Beamten-Portal** zur Bearbeitung vorgemerkt.

* **Bürgerservice des Bundeskanzleramts:**
  * 📞 **Telefon:** [+43 1 53115-0](tel:+431531150) (Montag bis Freitag, 08:00 – 16:00 Uhr)
  * ✉️ **E-Mail:** [buergerservice@bka.gv.at](mailto:buergerservice@bka.gv.at)
* **ID Austria Support-Hotline:** 📞 [+43 1 71100-805555](tel:+43171100805555)
* **Vor Ort:** Ihr zuständiges **Gemeindeamt** bzw. in Wien jedes **Magistratische Bezirksamt** (Servicetelefon 1360).

💡 **Tipp:** Klicken Sie unten auf **"Dossier für Beamtin/Beamten erstellen"**, um eine druckfertige Zusammenfassung Ihres Falls ohne persönliche Daten herunterzuladen.',
];
