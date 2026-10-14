<?php

namespace Database\Seeders;

use App\Models\KnowledgeNugget;
use Illuminate\Database\Seeder;

class KnowledgeNuggetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nuggets = [
            [
                'domain' => 'permit',
                'topic' => 'student_residence_financial_funds',
                'title' => 'Student Residence Permit: Financial Maintenance Funds (§ 64 NAG)',
                'keywords' => [
                    'student', 'finanzen', 'unterhalt', 'funds', 'konto', 'bank', 'monatlich',
                    'financial proof', 'stipendium', 'existenzmittel', 'sparkonto', 'kosten',
                ],
                'factual_context' => "Österreichisches Aufenthaltsrecht (§ 64 NAG):\n".
                    "- Antragsteller unter 24 Jahren: Mindestens ca. 672,64 € monatlich für 12 Monate im Voraus nachweisen (insgesamt ca. 8.070 €).\n".
                    "- Antragsteller ab 24 Jahren: Mindestens ca. 1.217,96 € monatlich für 12 Monate im Voraus nachweisen (insgesamt ca. 14.615 €).\n".
                    '- Nachweisformen: Eigenes Bankguthaben (SEPA- oder österreichisches Giro-/Sparkonto auf den Namen des Studierenden), rechtsverbindliche Verpflichtungserklärung (Haftungserklärung) oder Stipendienbestätigung.',
                'official_source_url' => 'https://oead.at/de/nach-oesterreich/einreise-und-aufenthalt',
                'is_verified' => true,
            ],
            [
                'domain' => 'permit',
                'topic' => 'student_residence_documents_fees',
                'title' => 'Student Residence Permit: Required Documents & Official Fees',
                'keywords' => [
                    'student', 'residence permit', 'aufenthaltstitel', 'visum', 'nag', 'unterlagen',
                    'dokumente', 'gebühren', 'ma 35', 'zulassung', 'zulassungsbescheid', 'apostille',
                ],
                'factual_context' => "Erforderliche Unterlagen für die Aufenthaltsbewilligung Student (§ 64 NAG):\n".
                    "- Gültiger Reisepass (Gültigkeit mindestens 3 Monate über die geplante Aufenthaltsdauer hinaus).\n".
                    "- Geburtsurkunde (mit diplomatischer Beglaubigung oder Apostille & beeidigter deutscher Übersetzung).\n".
                    "- Aktuelles biometrisches Passfoto (35 x 45 mm, EU-Kriterien, max. 6 Monate alt).\n".
                    "- Gültiger Zulassungsbescheid oder Aufnahmebestätigung einer österreichischen Universität oder Fachhochschule.\n".
                    "- Nachweis einer ortsüblichen Unterkunft (z. B. Mietvertrag oder Studierendenwohnheimvertrag).\n".
                    "- Alle Risken abdeckende Krankenversicherung (z. B. ÖGK studentische Selbstversicherung).\n".
                    "- Strafregisterbescheinigung aus dem Herkunftsland (max. 3 Monate alt, apostilliert).\n".
                    '- Gebühren: Gesamt 160,00 € (120 € Bundesgebühr bei Antragstellung, 20 € bei Erteilung, 20 € Personalisierungsgebühr).',
                'official_source_url' => 'https://www.oesterreich.gv.at/themen/leben_in_oesterreich/aufenthalt/Seite.120120.html',
                'is_verified' => true,
            ],
            [
                'domain' => 'passport',
                'topic' => 'passport_renewal_fees_deadlines',
                'title' => 'Austrian Passport: Issuance, Fees and Processing Times (PassG 1992)',
                'keywords' => [
                    'pass', 'reisepass', 'passport', 'verlängern', 'gebühr', 'kosten', 'dauer',
                    'express', 'passfoto', 'abgelaufen', 'notpass', 'magistrat', 'bezirksamt',
                ],
                'factual_context' => "Reisepass-Verfahren Republik Österreich (Passgesetz 1992):\n".
                    "- Reisepässe können NICHT verlängert werden – es wird stets ein neuer Reisepass ausgestellt.\n".
                    "- Der bisherige Reisepass darf bis zu 5 Jahre abgelaufen sein. Bei längerer Frist oder Verlust: Geburtsurkunde und Staatsbürgerschaftsnachweis mitbringen.\n".
                    "- Gebühren: Standard (ab 18 J.): 75,90 € (ca. 5 Werktage, Zustellung per behördlichem RSb-Brief). Expresspass: 100,00 € (2-3 Werktage). Ein-Tages-Expresspass: 220,00 € (nächster Werktag per Bote). Kinder 2-12 J.: 30,00 €. Kinder unter 2 J.: gebührenfrei bei Erstausstellung.\n".
                    '- Pflicht: Persönliches Erscheinen zur Abnahme der Fingerabdrücke und Unterschrift. 1 Passfoto (35 x 45 mm, max. 6 Monate alt, neutraler Hintergrund).',
                'official_source_url' => 'https://www.oesterreich.gv.at/themen/dokumente_und_recht/reisepass/Seite.020100.html',
                'is_verified' => true,
            ],
            [
                'domain' => 'residence',
                'topic' => 'residence_registration_deadline_landlord',
                'title' => 'Residence Registration: 3-Day Deadline & Landlord Signature (§ 1 MeldeG)',
                'keywords' => [
                    'wohnsitz', 'anmelden', 'ummeldung', 'umzug', 'meldezettel', 'frist', '3 tage',
                    'vermieter', 'unterkunftgeber', 'kosten', 'gebührenfrei', 'hauptwohnsitz',
                ],
                'factual_context' => "Wohnsitz-Meldepflicht nach dem Meldegesetz 1972:\n".
                    "- Gesetzliche Frist: Innerhalb von 3 Werktagen ab tatsächlichem Einzug in die Unterkunft (§ 1 Abs. 1 MeldeG).\n".
                    "- Kosten: Die Anmeldung und Ummeldung ist gesetzlich VOLLSTÄNDIG GEBÜHRENFREI (0,00 €).\n".
                    "- Meldezettel-Formular: Bei Miete ist zwingend die Unterschrift der Unterkunftgeberin / des Unterkunftgebers (Vermieter) erforderlich. Wohnungseigentümer unterschreiben selbst.\n".
                    "- Automatische Abmeldung: Bei Anmeldung der neuen Adresse wird der alte Wohnsitz automatisch abgemeldet.\n".
                    "- Vollständig digital möglich über ID Austria in der Smartphone-App 'Digitales Amt'.",
                'official_source_url' => 'https://www.oesterreich.gv.at/themen/dokumente_und_recht/an__abmeldung_des_wohnsitzes/Seite.1180200.html',
                'is_verified' => true,
            ],
            [
                'domain' => 'id_austria',
                'topic' => 'id_austria_tiers_digitales_amt',
                'title' => 'ID Austria: Basic vs. Full Functionality & Mobile Credentials',
                'keywords' => [
                    'id austria', 'handy-signatur', 'digitales amt', 'vollfunktion', 'basisfunktion',
                    'digitaler führerschein', 'aktivieren', 'polizei', 'passbehörde', 'eidas',
                ],
                'factual_context' => "ID Austria (Digitale Identität Österreichs):\n".
                    "- Nachfolger der Handy-Signatur mit voller Anerkennung nach EU-eIDAS-Verordnung.\n".
                    "- Basisfunktion: Ermöglicht Login bei Web-Portalen (FinanzOnline, MeineSV) und elektronisches Signieren von PDF-Dokumenten.\n".
                    "- Vollfunktion: Ermöglicht zusätzlich digitale Ausweise am Smartphone (digitaler Führerschein, digitaler Altersnachweis, digitaler Zulassungsschein) und Wohnsitzummeldung in der App 'Digitales Amt'.\n".
                    '- Aktivierung Vollfunktion: Erfordert behördliche Identitätsfeststellung bei einer Passbehörde (Bezirksamt/BH) oder Landespolizeidirektion (bei Reisepass-Antrag automatisch kostenlos inkludiert).',
                'official_source_url' => 'https://www.oesterreich.gv.at/id-austria.html',
                'is_verified' => true,
            ],
            [
                'domain' => 'tax',
                'topic' => 'tax_lohnsteuerausgleich_familienbonus',
                'title' => 'Employee Tax Assessment: 5-Year Retroactive Filing & Family Bonus Plus',
                'keywords' => [
                    'steuer', 'steuerausgleich', 'arbeitnehmerveranlagung', 'finanzonline', 'familienbonus',
                    'pendlerpauschale', 'lohnsteuer', '5 jahre', 'rückwirkend', 'absetzbetrag', 'refund',
                ],
                'factual_context' => "Arbeitnehmerveranlagung (Lohnsteuerausgleich) über FinanzOnline (EStG 1988):\n".
                    "- Frist: Die freiwillige Arbeitnehmerveranlagung kann bis zu 5 Jahre rückwirkend beim Finanzamt eingereicht werden.\n".
                    "- Familienbonus Plus: Direkter Steuerabsetzbetrag von bis zu 2.000 € pro Kind unter 18 Jahren (bzw. bis zu 700 € ab 18 Jahren in Ausbildung), der die Einkommensteuerschuld direkt mindert.\n".
                    "- Weitere Absetzbeträge: Pendlerpauschale & Pendlereuro, Alleinverdiener-/Alleinerzieherabsetzbetrag, Mehrkindzuschlag, Werbungskosten (Fortbildungen, Arbeitsmittel) und Sonderausgaben (Spenden, Kirchenbeitrag bis 600 €).\n".
                    '- Die Einreichung über FinanzOnline ist gebührenfrei.',
                'official_source_url' => 'https://www.bmf.gv.at/themen/steuern/arbeitnehmerveranlagung.html',
                'is_verified' => true,
            ],
            [
                'domain' => 'health',
                'topic' => 'health_ecard_photo_self_insurance',
                'title' => 'Healthcare & e-card: Mandatory Photo & Student Self-Insurance (ÖGK)',
                'keywords' => [
                    'ecard', 'e-card', 'foto', 'krankenkasse', 'ögk', 'selbstversicherung', 'student',
                    'rezeptgebühr', 'rezept', 'chipkarte', 'monatlich', 'versicherung',
                ],
                'factual_context' => "Österreichische Gesundheitskasse (ÖGK) & e-card (ASVG):\n".
                    "- e-card mit Foto Pflicht: Alle Versicherten ab 14 Jahren müssen ein Foto auf der e-card haben. Fotostatus abrufbar auf chipkarte.at. Falls kein Reisepass/Personalausweis vorliegt: Foto bei Passbehörde oder Polizei registrieren lassen.\n".
                    "- Studentische Selbstversicherung: Für ordentliche Universitäts- und FH-Studierende bei der ÖGK für ca. 69,13 € monatlich (Inskriptionsbestätigung und Leistungsnachweis erforderlich).\n".
                    "- Geringfügige Selbstversicherung: Bei Verdienst unter der Geringfügigkeitsgrenze (~518 €/Monat) für ca. 73 € monatlich (inkl. Pensionsversicherung).\n".
                    '- Rezeptgebühr: ca. 7,10 € pro Medikament. Obergrenze bei 2 % des jährlichen Nettoeinkommens (automatische Befreiung danach).',
                'official_source_url' => 'https://www.gesundheitskasse.at/selbstversicherung',
                'is_verified' => true,
            ],
            [
                'domain' => 'driving_vehicle',
                'topic' => 'foreign_driving_license_conversion',
                'title' => 'Driving License: Foreign License Conversion (6-Month Rule) & Vehicle Inspection',
                'keywords' => [
                    'führerschein', 'umschreiben', 'ausland', 'auto', 'kfz', 'zulassung', 'pickerl',
                    '57a', '6 monate', 'drittstaat', 'eu-führerschein', 'asfinag', 'vignette',
                ],
                'factual_context' => "Führerschein- und KFZ-Recht in Österreich (FSG & KFG):\n".
                    "- EU/EWR-Führerscheine: Bleiben in Österreich unbegrenzt gültig, keine Umschreibungspflicht bei Wohnsitzwechsel.\n".
                    "- Nicht-EU / Drittstaats-Führerscheine: Dürfen ab Begründung des Hauptwohnsitzes in Österreich maximal 6 Monate verwendet werden. Danach zwingende Umschreibung bei der Behörde (Polizeidirektion oder BH; je nach Herkunftsland praktische Fahrprüfung und Amtsarzt-Gutachten nötig).\n".
                    "- § 57a Begutachtung ('Pickerl'): Prüfintervall 3-2-1 bei Neufahrzeugen (nach 3 Jahren, nach 2 Jahren, danach jährlich).\n".
                    '- ASFINAG Digitale Vignette: Online-Kauf unterliegt 18-tägiger Konsumentenschutzfrist (oder sofortiger Beginn bei Auswahl als gewerblicher Nutzer).',
                'official_source_url' => 'https://www.oesterreich.gv.at/themen/dokumente_und_recht/fuehrerschein.html',
                'is_verified' => true,
            ],
            [
                'domain' => 'work_permit',
                'topic' => 'rot_weiss_rot_karte_requirements',
                'title' => 'Red-White-Red Card: Points Thresholds & Employer Declaration (AuslBG)',
                'keywords' => [
                    'rot-weiß-rot', 'red-white-red', 'rwr', 'arbeitgeber', 'mangelberuf', 'fachkraft',
                    'punkte', 'arbeitserlaubnis', 'ams', 'arbeitsplatz', 'qualifikation',
                ],
                'factual_context' => "Rot-Weiß-Rot – Karte (Kriterienbasierte Zuwanderung):\n".
                    "- Verbindet Aufenthaltsrecht und Arbeitserlaubnis bei einem konkreten österreichischen Arbeitgeber für 24 Monate.\n".
                    "- Mangelberufe: Mindestens 55 von 90 Punkten im Punktesystem (Qualifikation, Berufserfahrung, Sprachkenntnisse, Alter).\n".
                    "- Sonstige Schlüsselkräfte: Mindestens 55 von 90 Punkten + Einhaltung des kollektivvertraglichen Mindestlohns.\n".
                    "- Sehr Hochqualifizierte: Mindestens 70 von 100 Punkten (Berechtigung zum 6-monatigen Arbeitsplatzsuche-Visum).\n".
                    "- Wichtigstes Dokument: Formular 'Arbeitgebererklärung' des österreichischen Unternehmens.\n".
                    '- Umstieg auf Rot-Weiß-Rot – Karte Plus (freier Arbeitsmarktzugang) nach 21 Monaten Beschäftigung möglich.',
                'official_source_url' => 'https://www.migration.gv.at/de/formen-der-zuwanderung/dauerhafte-zuwanderung/',
                'is_verified' => true,
            ],
            [
                'domain' => 'family',
                'topic' => 'family_benefits_kbg_allowance',
                'title' => 'Family Allowance (Familienbeihilfe) & Childcare Benefit Models (KBG)',
                'keywords' => [
                    'familienbeihilfe', 'kinderbetreuungsgeld', 'geburt', 'karenz', 'eltern', 'kinder',
                    'baby', 'beihilfe', 'kbg-konto', 'einkommensabhängig', 'auszahlung',
                ],
                'factual_context' => "Staatliche Familienleistungen in Österreich (FLAG & KBGG):\n".
                    "- Familienbeihilfe: Monatliche Grundunterstützung für jedes Kind in Österreich (ca. 132 € bis 191 € je nach Alter). Zuerkennung nach Geburt im Inland erfolgt in der Regel automatisch (antragslose Familienbeihilfe).\n".
                    "- Kinderbetreuungsgeld (KBG): Wahlrecht zwischen:\n".
                    "  1. Pauschalem KBG-Konto: Tagesbetrag (ca. 16,87 € bis 39,30 €) für 12 bis 28 Monate (bei Teilung zwischen beiden Eltern).\n".
                    "  2. Einkommensabhängigem KBG: 80 % des Letzteinkommens (max. ca. 66 €/Tag) für maximal 12 bis 14 Monate (bei Teilung).\n".
                    '- Partnerschaftsbonus: 500 € Einmalzahlung pro Elternteil, wenn das KBG zu annähernd gleichen Teilen (mind. 40:60) geteilt wurde.',
                'official_source_url' => 'https://www.bundeskanzleramt.gv.at/agenda/familie/kinderbetreuungsgeld.html',
                'is_verified' => true,
            ],
        ];

        foreach ($nuggets as $item) {
            KnowledgeNugget::updateOrCreate(
                ['topic' => $item['topic']],
                $item
            );
        }
    }
}
