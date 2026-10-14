<?php

namespace App\Services\AustrianGov;

class KnowledgeBaseService
{
    /**
     * Build the grounded system prompt for the AI assistant.
     */
    public function buildSystemPrompt(string $locale = 'de'): string
    {
        $isGerman = str_starts_with(strtolower($locale), 'de');

        if ($isGerman) {
            return <<<'PROMPT'
Du bist die offizielle KI-Bürgerassistenz der Republik Österreich ("Digitales Österreich 2030 / Bundesservice").
Deine Aufgabe ist es, Bürgerinnen und Bürgern bei allen behördlichen Anliegen verständlich, empathisch und präzise zur Seite zu stehen.

ÖSTERREICHISCHE VERWALTUNGSBEREICHE & RECHTSGRUNDLAGEN:
1. REISEPASS & AUSWEISE (Passgesetz 1992):
   - Gebühren Standard-Reisepass (ab 18 J.): 75,90 € (ca. 5 Werktage per RSb-Brief).
   - Expresspass: 100,00 € (2-3 Werktage), Ein-Tages-Expresspass: 220,00 €.
   - Kinder bis 2 Jahre: Gebührenfrei bei Erstausstellung (bzw. 30,00 €); Kinder 2-12 Jahre: 30,00 €.
   - Reisepass kann nicht verlängert werden – es wird stets ein neuer ausgestellt. Bisheriger Pass darf bis zu 5 Jahre abgelaufen sein. Passfoto (35x45mm) max. 6 Monate alt nach EU-Biometrie.
2. WOHNSITZ & MELDEWESEN (Meldegesetz 1972):
   - Gesetzliche Meldepflicht: Innerhalb von 3 Tagen ab tatsächlichem Einzug (§ 1 Abs. 1 MeldeG).
   - Vollständig gebührenfrei! Meldezettel-Formular mit Bestätigung der Unterkunftgeberin / des Unterkunftgebers (Vermieter-Unterschrift) bei Miete.
   - Digitaler Behördenweg über ID Austria in der App "Digitales Amt" oder persönlich am Gemeindeamt / Wiener Bezirksamt.
3. AUFENTHALT & EINWANDERUNG (NAG & AuslBG):
   - Student Residence Permit (§ 64 NAG): Zulassungsbescheid der Hochschule, Finanzierungsnachweis (~672 €/Monat unter 24 J., ~1.217 € ab 24 J.), ÖGK-Krankenversicherung, Unterkunft, Strafregisterauszug. Gesamtgebühr: 160,00 €.
   - Rot-Weiß-Rot – Karte: Kriterienbasiertes Punktesystem für Fachkräfte, Mangelberufe und Studienabsolventen. Antragstellung bei Vertretungsbehörde im Ausland oder Inlandsbehörde (z. B. MA 35 Wien).
4. STEUERN & FINANZEN (FinanzOnline & EStG 1988):
   - Arbeitnehmerveranlagung (Lohnsteuerausgleich): Bis zu 5 Jahre rückwirkend über FinanzOnline einreichbar.
   - Familienbonus Plus: Bis zu 2.000 € pro Kind unter 18 Jahren / bis zu 700 € ab 18 Jahren direkt von der Steuer absetzbar.
   - Pendlerpauschale & Pendlereuro, Alleinverdiener-/Alleinerzieherabsetzbetrag, Mehrkindzuschlag.
5. GESUNDHEIT & SOZIALVERSICHERUNG (ÖGK & ASVG):
   - e-card mit Foto: Pflicht für Versicherte ab 14 Jahren (Fotoerfassung bei Passbehörden oder Polizeidienststellen).
   - Studentische Selbstversicherung bei der ÖGK (ca. 69 €/Monat für ordentliche Studierende).
   - Rezeptgebührenobergrenze (2 % des Nettoeinkommens) und Rezeptgebührenbefreiung bei geringem Einkommen.
6. FÜHRERSCHEIN & MOBILITÄT (FSG & KFG):
   - Führerschein-Neuausstellung (Scheckkarte, ca. 49,50 € bzw. 60,50 €).
   - Umschreibung ausländischer Führerscheine: EWR-Führerscheine unbegrenzt anerkannt; Drittstaats-Führerscheine binnen 6 Monaten nach Wohnsitzbegründung umzuschreiben (§ 23 FSG).
   - § 57a KFG Begutachtung ("Pickerl"): Prüfintervall 3-2-1 bei Neufahrzeugen.
   - ASFINAG Digitale Vignette: Im Online-Shop 18 Tage Konsumentenschutzfrist beachten oder als Unternehmer/sofort gültig kaufen.
7. FAMILIE & LEBENSALTER (FLAG & KBGG):
   - Familienbeihilfe: Monatliche staatliche Beihilfe für Kinder, automatische Auszahlung nach Geburt.
   - Kinderbetreuungsgeld (KBG): Wahl zwischen pauschalem KBG-Konto (Tagesbetrag) und einkommensabhängigem KBG (80 % des Letzteinkommens, max. 66 €/Tag).
   - Standesamt: Eheschließung (Aufgebot, Lichtbildausweis, Geburtsurkunde, Staatsbürgerschaftsnachweis).
8. ARBEIT & AMS (AlVG):
   - Arbeitslosengeld: Anwartschaft (mind. 52 Wochen arbeitslosenversicherungspflichtige Beschäftigung binnen 24 Monaten). Online-Meldung über eJob-Room des AMS.
   - Geringfügigkeitsgrenze (ca. 518 €/Monat).

GRUNDREGELN FÜR DIE ANTWORTEN:
1. EINFACHE SPRACHE: Übersetze Amtsdeutsch in klare, leicht verständliche Alltagssprache ohne Amtskauderwelsch.
2. KARTEN-STRUKTUR FÜR PROZESSE & CHECKLISTEN:
   - Beginne immer mit einem einleitenden Absatz (1-2 kurze Sätze Erklärung).
   - Formatiere Prozessschritte immer als nummerierte Schritte:
     1. **[Schritt-Titel]**: [Verständliche Erklärung des Schritts]
   - Formatiere Unterlagen oder Dokumente immer als Checkliste:
     - [ ] **[Dokument-Name]**: [Vorgaben und Details]
   - Fasse am Ende Gebühren, Fristen und die zuständige Stelle prägnant zusammen.
3. NÄCHSTE SCHRITTE & FOLGEFRAGEN (BUTTONS):
   Schließe deine Antwort am Ende immer mit 2 bis 4 konkreten Folgefragen oder Handlungsschritten für die Bürgerin / den Bürger ab:
   [NÄCHSTE SCHRITTE]
   - [Konkrete Folgefrage oder Handlung]
   - [Konkrete Folgefrage oder Handlung]
PROMPT;
        }

        return <<<'PROMPT'
You are the official AI Citizen Assistant of the Republic of Austria ("Digital Austria 2030 / Bundesservice").
Your role is to guide citizens, residents, and international visitors through Austrian public administration naturally, securely, and in plain, accessible language.

AUSTRIAN ADMINISTRATIVE FRAMEWORKS & LEGAL STANDARDS:
1. PASSPORTS & IDENTIFICATION (Passport Act 1992 - PassG):
   - Adult standard passport (18+): €75.90 (delivered in approx. 5 working days via official RSb registered mail).
   - Express passport: €100.00 (2-3 working days), One-day express: €220.00.
   - Children under 2: Free of charge for first issue (or €30.00); children 2-12: €30.00.
   - Passports cannot be extended; a new one is always issued. Expired passport can be up to 5 years past validity. 1 recent biometric photo (35x45mm) max 6 months old.
2. RESIDENCE REGISTRATION (Residence Registration Act 1972 - MeldeG):
   - Statutory registration deadline: Within 3 business days of moving into accommodation (§ 1 MeldeG).
   - Registration is 100% free of charge! Official Meldezettel form must be signed by the landlord ("Unterkunftgeber") if renting.
   - 100% digital service via ID Austria in the "Digitales Amt" app, or in-person at municipal district offices (Bezirksamt in Vienna, Gemeindeamt).
3. RESIDENCE & WORK PERMITS (NAG & AuslBG):
   - Student Residence Permit (§ 64 NAG): University admission letter (Zulassungsbescheid), proof of maintenance funds (~€672/mo under 24, ~€1,217/mo 24+), ÖGK health insurance, accommodation contract, criminal record clearance. Total statutory fee: €160.00.
   - Red-White-Red Card (Rot-Weiß-Rot – Karte): Points-based residence and work permit for skilled workers, graduates, and shortage occupations. Administered by Austrian embassies abroad or local immigration authorities (e.g. MA 35 in Vienna).
4. TAXES & FINANZONLINE (Income Tax Act - EStG 1988):
   - Employee Tax Assessment (Arbeitnehmerveranlagung / Lohnsteuerausgleich): Claimable up to 5 years retroactively via FinanzOnline.
   - Family Bonus Plus (Familienbonus Plus): Up to €2,000/year deduction per child under 18 (€700/year for 18+).
   - Commuter allowance (Pendlerpauschale), Single-earner tax credit, Climate bonus (Klimabonus).
5. HEALTHCARE & SOCIAL INSURANCE (ÖGK & ASVG):
   - e-card with photo: Mandatory for insured individuals aged 14+ (photo registration at passport or police stations).
   - Student self-insurance with ÖGK (~€69/month for enrolled university students).
   - Prescription fee cap (2% of net income) and fee exemption for low-income residents.
6. DRIVING & VEHICLE REGISTRATION (FSG & KFG):
   - Driving license renewal (€49.50 standard).
   - Conversion of foreign driving licenses: EU/EEA licenses recognized indefinitely; non-EU licenses must be converted within 6 months of residency (§ 23 FSG).
   - § 57a vehicle inspection ("Pickerl"): 3-2-1 year inspection cycle for private cars.
   - ASFINAG digital motorway vignette: Available online (note 18-day consumer protection delay or immediate purchase).
7. FAMILY & PARENTAL BENEFITS (FLAG & KBGG):
   - Family allowance (Familienbeihilfe): Universal child benefit paid monthly by the tax office.
   - Childcare allowance (Kinderbetreuungsgeld): Flat-rate account or income-dependent variant (80% of earnings, max €66/day).
8. EMPLOYMENT & AMS (Unemployment Insurance Act - AlVG):
   - Unemployment benefits (Arbeitslosengeld): Requires 52 weeks of insured employment in the last 24 months. Register via AMS eJob-Room.
   - Marginal employment limit (Geringfügigkeitsgrenze ~€518/month).

RESPONSE FORMAT REQUIREMENTS:
1. PLAIN LANGUAGE: Translate complex Austrian administrative terminology into plain, accessible English without bureaucratic jargon.
2. CARD-FRIENDLY STRUCTURE FOR PROCESSES & CHECKLISTS:
   - Begin with a concise introductory paragraph (1-2 sentences).
   - Format action steps as a numbered sequence:
     1. **[Step Title]**: [Clear explanation of this step]
   - Format document requirements as a checklist:
     - [ ] **[Document Name]**: [Specifications and details]
   - Conclude with concise summary of fees, deadlines, and authority location.
3. RECOMMENDED NEXT STEPS (ACTION BUTTONS):
   Conclude every response with 2 to 4 actionable follow-up questions or next steps for the citizen:
   [NEXT STEPS]
   - [Actionable follow-up question or step]
   - [Actionable follow-up question or step]
PROMPT;
    }

    /**
     * Retrieve official verified sources for a given topic.
     *
     * @return array<int, array{title: string, url: string, description: string, tag: string}>
     */
    public function getOfficialSources(string $topic = 'general', string $locale = 'de'): array
    {
        $isGerman = str_starts_with(strtolower($locale), 'de');

        $sources = [
            'passport' => [
                [
                    'title' => $isGerman ? 'Reisepass für Erwachsene - oesterreich.gv.at' : 'Austrian Passport Renewal - oesterreich.gv.at',
                    'url' => 'https://www.oesterreich.gv.at/themen/dokumente_und_recht/reisepass/Seite.020100.html',
                    'description' => $isGerman ? 'Offizielles Verfahren, Kosten (€ 75,90) und Fristen der Republik Österreich.' : 'Official procedures, fees (€75.90) and deadlines of the Republic of Austria.',
                    'tag' => 'Passgesetz 1992',
                ],
                [
                    'title' => $isGerman ? 'Passbild-Kriterien (Kabinettfoto / Biometrie)' : 'Official Biometric Passport Photo Criteria',
                    'url' => 'https://www.oesterreich.gv.at/themen/dokumente_und_recht/reisepass/kriterien_fuer_die_erstellung_von_passbildern.html',
                    'description' => $isGerman ? '35 x 45 mm, Hochformat, max. 6 Monate alt, neutraler Hintergrund.' : '35 x 45 mm, portrait, max 6 months old, neutral background.',
                    'tag' => 'BMI Standard',
                ],
                [
                    'title' => $isGerman ? 'Reisepass verloren oder gestohlen (Verlustanzeige)' : 'Lost or Stolen Passport (Loss Report)',
                    'url' => 'https://www.oesterreich.gv.at/themen/dokumente_und_recht/reisepass/Seite.020500.html',
                    'description' => $isGerman ? 'Verlustanzeige bei der Passbehörde oder Fundamt, Diebstahlsanzeige bei der Polizei.' : 'Notice of loss at passport authority or theft report at police.',
                    'tag' => 'Verlustanzeige',
                ],
            ],
            'residence' => [
                [
                    'title' => $isGerman ? 'Wohnsitz An- und Abmeldung - oesterreich.gv.at' : 'Residence Registration & Deregistration - oesterreich.gv.at',
                    'url' => 'https://www.oesterreich.gv.at/themen/dokumente_und_recht/an__abmeldung_des_wohnsitzes/Seite.1180200.html',
                    'description' => $isGerman ? 'Gesetzliche 3-Tage-Frist, Meldezettel, gebührenfreie Abwicklung.' : 'Statutory 3-day deadline, Meldezettel form, free of charge.',
                    'tag' => '§ 1 MeldeG',
                ],
                [
                    'title' => $isGerman ? 'Meldezettel Online mit ID Austria' : 'Online Residence Registration via ID Austria',
                    'url' => 'https://www.oesterreich.gv.at/id-austria.html',
                    'description' => $isGerman ? 'Digitaler Behördenweg ohne Wartezeit in der App Digitales Amt.' : '100% digital public service via Digitales Amt mobile app.',
                    'tag' => 'ID Austria Vollfunktion',
                ],
                [
                    'title' => $isGerman ? 'Muster-Meldezettel zum Download' : 'Official Meldezettel PDF Form Download',
                    'url' => 'https://www.bmi.gv.at/202/files/Meldezettel_neu.pdf',
                    'description' => $isGerman ? 'Amtliches Formular mit Pflichtangaben für Haupt- und Nebenwohnsitz.' : 'Official registration form including landlord confirmation section.',
                    'tag' => 'Formular BMI',
                ],
            ],
            'id_austria' => [
                [
                    'title' => $isGerman ? 'ID Austria - Die digitale Identität Österreichs' : 'ID Austria - Official Digital Identity',
                    'url' => 'https://www.oesterreich.gv.at/id-austria.html',
                    'description' => $isGerman ? 'Nachfolger der Handy-Signatur. Basis- und Vollfunktion für alle Online-Behörden.' : 'Successor to Handy-Signatur with full eIDAS recognition.',
                    'tag' => 'Bundeskanzleramt',
                ],
                [
                    'title' => $isGerman ? 'Digitales Amt App (Google Play & Apple App Store)' : 'Digitales Amt Official Mobile App',
                    'url' => 'https://www.oesterreich.gv.at/digitales-amt.html',
                    'description' => $isGerman ? 'Behördenwege direkt am Smartphone erledigen mit ID Austria.' : 'Official federal app for mobile identity and public services.',
                    'tag' => 'Digitales Amt',
                ],
            ],
            'permit' => [
                [
                    'title' => $isGerman ? 'Aufenthaltstitel "Student" (§ 64 NAG) - oesterreich.gv.at' : 'Student Residence Permit (§ 64 NAG) - oesterreich.gv.at',
                    'url' => 'https://www.oesterreich.gv.at/themen/leben_in_oesterreich/aufenthalt/Seite.120120.html',
                    'description' => $isGerman ? 'Voraussetzungen, Unterlagen und Verfahren für Drittstaatsangehörige zum Studium.' : 'Requirements, documents, and procedure for third-country students in Austria.',
                    'tag' => '§ 64 NAG',
                ],
                [
                    'title' => $isGerman ? 'OeAD: Einreise und Aufenthalt für Studierende' : 'OeAD: Entry & Residence for Students in Austria',
                    'url' => 'https://oead.at/de/nach-oesterreich/einreise-und-aufenthalt',
                    'description' => $isGerman ? 'Offizieller Leitfaden des Bundes für internationale Hochschulstudierende.' : 'Official national agency guide for international higher education students.',
                    'tag' => 'OeAD Richtlinie',
                ],
                [
                    'title' => $isGerman ? 'Einwanderungs- und Aufenthaltsbehörde (MA 35 Wien / BH)' : 'Immigration Authority (MA 35 Vienna / District Commission)',
                    'url' => 'https://www.wien.gv.at/kontakte/ma35/',
                    'description' => $isGerman ? 'Zuständige Niederlassungsbehörde für die persönliche Antragsabgabe.' : 'Competent Austrian authority for permit applications and biometric collection.',
                    'tag' => 'Aufenthaltsbehörde',
                ],
            ],
            'work_permit' => [
                [
                    'title' => $isGerman ? 'Rot-Weiß-Rot – Karte (migration.gv.at)' : 'Red-White-Red Card - Official Migration Portal',
                    'url' => 'https://www.migration.gv.at/de/formen-der-zuwanderung/dauerhafte-zuwanderung/',
                    'description' => $isGerman ? 'Kriterienbasiertes Punktesystem für Fachkräfte und Schlüsselarbeitskräfte in Österreich.' : 'Points-based permanent immigration system for skilled workers in Austria.',
                    'tag' => 'AuslBG & NAG',
                ],
                [
                    'title' => $isGerman ? 'AMS: Mangelberufe & Arbeitsmarktprüfung' : 'AMS: Shortage Occupations & Labor Market Verification',
                    'url' => 'https://www.ams.at/organisation/arbeitsmarktdaten/mangelberufe',
                    'description' => $isGerman ? 'Aktuelle Bundeseinheitliche und regionale Mangelberufsliste.' : 'Official annual list of certified shortage occupations in Austria.',
                    'tag' => 'AMS Kriterien',
                ],
            ],
            'tax' => [
                [
                    'title' => $isGerman ? 'Arbeitnehmerveranlagung (Lohnsteuerausgleich) - BMF' : 'Employee Tax Assessment (Lohnsteuerausgleich) - BMF',
                    'url' => 'https://www.bmf.gv.at/themen/steuern/arbeitnehmerveranlagung.html',
                    'description' => $isGerman ? 'Offizielle Informationen des Bundesministeriums für Finanzen (bis zu 5 Jahre rückwirkend).' : 'Official Ministry of Finance guide for claiming income tax refunds up to 5 years retroactively.',
                    'tag' => 'EStG 1988',
                ],
                [
                    'title' => $isGerman ? 'FinanzOnline Portal der Republik Österreich' : 'FinanzOnline - Official Austrian Tax Portal',
                    'url' => 'https://finanzonline.bmf.gv.at/',
                    'description' => $isGerman ? 'E-Government-Zugang für Steuererklärungen, Familienbonus und Absetzbeträge.' : 'Official electronic tax portal accessible with ID Austria.',
                    'tag' => 'FinanzOnline',
                ],
                [
                    'title' => $isGerman ? 'Familienbonus Plus Rechner & Antrag' : 'Family Bonus Plus (Familienbonus Plus) Guide',
                    'url' => 'https://www.bmf.gv.at/themen/steuern/arbeitnehmerveranlagung/familienbonus-plus.html',
                    'description' => $isGerman ? 'Bis zu 2.000 € Steuerabsetzbetrag pro Kind unter 18 Jahren.' : 'Direct tax credit up to €2,000 per minor child.',
                    'tag' => '§ 33 EStG',
                ],
            ],
            'health' => [
                [
                    'title' => $isGerman ? 'e-card der Republik Österreich (chipkarte.at)' : 'Official Austrian e-card Portal (chipkarte.at)',
                    'url' => 'https://www.chipkarte.at/',
                    'description' => $isGerman ? 'e-card mit Foto Pflicht, Registrierungsstellen und Europäische Krankenversicherungskarte.' : 'Mandatory photo registration, European Health Insurance Card (EHIC).',
                    'tag' => 'ASVG § 31a',
                ],
                [
                    'title' => $isGerman ? 'Österreichische Gesundheitskasse (ÖGK) - Selbstversicherung' : 'Austrian Health Insurance Fund (ÖGK) - Self-Insurance',
                    'url' => 'https://www.gesundheitskasse.at/selbstversicherung',
                    'description' => $isGerman ? 'Günstige Krankenversicherung für Studierende (~69 €/Monat) und geringfügig Beschäftigte.' : 'Low-cost health insurance for university students and part-time workers.',
                    'tag' => 'ÖGK Tarif',
                ],
                [
                    'title' => $isGerman ? 'Rezeptgebühr & Befreiungsrechner' : 'Prescription Fee Exemption (Rezeptgebühr)',
                    'url' => 'https://www.gesundheitskasse.at/rezeptgebuehr',
                    'description' => $isGerman ? 'Gesetzliche Rezeptgebühr, 2 % Obergrenze und automatische Befreiung.' : 'Statutory prescription fee rules, 2% annual income ceiling, and exemptions.',
                    'tag' => 'MeineSV',
                ],
            ],
            'driving_vehicle' => [
                [
                    'title' => $isGerman ? 'Führerschein-Verfahren & Erneuerung - oesterreich.gv.at' : 'Austrian Driving License Services - oesterreich.gv.at',
                    'url' => 'https://www.oesterreich.gv.at/themen/dokumente_und_recht/fuehrerschein.html',
                    'description' => $isGerman ? 'Erstantrag, Verlängerung, Umschreibung ausländischer Führerscheine (§ 23 FSG).' : 'Applications, renewals, and foreign license conversions.',
                    'tag' => 'FSG 1997',
                ],
                [
                    'title' => $isGerman ? 'KFZ-Zulassung & Kennzeichen' : 'Motor Vehicle Registration (KFZ-Zulassung)',
                    'url' => 'https://www.oesterreich.gv.at/themen/mobilitaet/kfz_an__abmeldung.html',
                    'description' => $isGerman ? 'Anmeldung, Ummeldung, Abmeldung bei den KFZ-Zulassungsstellen der Versicherer.' : 'Official registration procedures via Austrian insurance licensing offices.',
                    'tag' => 'KFG 1967',
                ],
                [
                    'title' => $isGerman ? 'ASFINAG Maut & Digitale Vignette' : 'ASFINAG Official Motorway Toll & Digital Vignette',
                    'url' => 'https://shop.asfinag.at/',
                    'description' => $isGerman ? 'Digitale Jahres-, 1-Tages-, 10-Tages- und 2-Monats-Vignette für Autobahnen.' : 'Official motorway vignettes for Austrian autobahns and expressways.',
                    'tag' => 'BStMG',
                ],
            ],
            'family' => [
                [
                    'title' => $isGerman ? 'Familienbeihilfe - Finanzamt Österreich' : 'Family Allowance (Familienbeihilfe) - Tax Office',
                    'url' => 'https://www.bmf.gv.at/themen/familien/familienbeihilfe.html',
                    'description' => $isGerman ? 'Staatliche Unterstützung für alle Familien, automatische Beantragung bei Geburt.' : 'Universal monthly financial support for parents in Austria.',
                    'tag' => 'FLAG 1967',
                ],
                [
                    'title' => $isGerman ? 'Kinderbetreuungsgeld (KBG-Konto & Einkommensabhängig)' : 'Austrian Childcare Allowance (Kinderbetreuungsgeld)',
                    'url' => 'https://www.bundeskanzleramt.gv.at/agenda/familie/kinderbetreuungsgeld.html',
                    'description' => $isGerman ? 'Pauschales KBG-Konto vs. einkommensabhängiges Kinderbetreuungsgeld der Krankenkasse.' : 'Official parental leave allowance system managed by health insurance funds.',
                    'tag' => 'KBGG',
                ],
                [
                    'title' => $isGerman ? 'Standesamt & Eheschließung - oesterreich.gv.at' : 'Civil Registry & Marriage - oesterreich.gv.at',
                    'url' => 'https://www.oesterreich.gv.at/themen/familie_und_partnerschaft/heirat.html',
                    'description' => $isGerman ? 'Aufgebot, Unterlagen, Namensrecht und Trauung am Standesamt.' : 'Legal marriage formalities, civil registrar appointments, and required records.',
                    'tag' => 'PStG 2013',
                ],
            ],
            'employment' => [
                [
                    'title' => $isGerman ? 'Arbeitsmarktservice Österreich (AMS eJob-Room)' : 'Public Employment Service Austria (AMS)',
                    'url' => 'https://www.ams.at/arbeitsuchende/arbeitslos-was-tun',
                    'description' => $isGerman ? 'Arbeitslosmeldung, Arbeitslosengeld-Antrag, Notstandshilfe und Weiterbildung.' : 'Unemployment claims, benefits calculator, and employment services.',
                    'tag' => 'AlVG 1977',
                ],
                [
                    'title' => $isGerman ? 'Arbeiterkammer (AK) - Arbeitsrecht & Konsumentenschutz' : 'Chamber of Labor (Arbeiterkammer) Employee Rights',
                    'url' => 'https://www.arbeiterkammer.at/',
                    'description' => $isGerman ? 'Gesetzliche Beratung zu Kündigung, Gehalt, Überstunden und Kollektivverträgen.' : 'Statutory legal assistance for employees regarding labor rights and dismissal.',
                    'tag' => 'Arbeitsrecht',
                ],
            ],
            'citizenship' => [
                [
                    'title' => $isGerman ? 'Staatsbürgerschaft & Einbürgerung - oesterreich.gv.at' : 'Austrian Citizenship & Naturalization - oesterreich.gv.at',
                    'url' => 'https://www.oesterreich.gv.at/themen/leben_in_oesterreich/staatsbuergerschaft.html',
                    'description' => $isGerman ? 'Gesetzliche Fristen (6 bis 10 Jahre), Deutschkenntnisse (B1/B2), Staatsbürgerschaftsprüfung.' : 'Statutory residency criteria, language tests, exam requirements, and dual citizenship rules.',
                    'tag' => 'StbG 1985',
                ],
            ],
            'human' => [
                [
                    'title' => $isGerman ? 'Bürgerservice des Bundeskanzleramts' : 'Federal Chancellery Citizen Service Helpdesk',
                    'url' => 'https://www.bundeskanzleramt.gv.at/service/buergerservice.html',
                    'description' => $isGerman ? 'Telefon: +43 1 53115-0 (Mo–Fr 08:00–16:00), E-Mail: buergerservice@bka.gv.at' : 'Phone: +43 1 53115-0 (Mon–Fri 08:00–16:00), Email: buergerservice@bka.gv.at',
                    'tag' => 'Bürgerservice BKA',
                ],
                [
                    'title' => $isGerman ? 'Servicenummer der Stadt Wien (1360)' : 'City of Vienna Citizen Information Line (1360)',
                    'url' => 'https://www.wien.gv.at/verwaltung/stadtservice/',
                    'description' => $isGerman ? 'Direkte Unterstützung bei Bezirksamt-Terminen und Meldeangelegenheiten.' : 'Direct assistance for district authority appointments and registrations.',
                    'tag' => 'Stadt Wien',
                ],
            ],
        ];

        return $sources[$topic] ?? array_merge($sources['passport'], $sources['residence'], $sources['human']);
    }

    /**
     * Detect the most relevant Austrian administrative domain using intelligent keyword & synonym scoring.
     */
    public function detectDomain(string $query): string
    {
        $q = mb_strtolower(trim($query));

        $domainKeywords = [
            'tax' => [
                'steuer', 'lohnsteuer', 'steuerausgleich', 'arbeitnehmerveranlagung', 'finanzamt', 'finanzonline',
                'tax', 'taxes', 'pendlerpauschale', 'pendlereuro', 'familienbonus', 'klimabonus', 'absetzbetrag',
                'werbungskosten', 'sonderausgaben', 'mehrkindzuschlag', 'bmf', 'tax return', 'refund',
            ],
            'health' => [
                'krankenkasse', 'ögk', 'oegk', 'ecard', 'e-card', 'selbstversicherung', 'krankenversicherung',
                'rezeptgebühr', 'gesundheitskasse', 'svs', 'bvaeb', 'arzt', 'health insurance', 'krankmeldung',
                'chipkarte', 'gesundheit', 'patient', 'rezept', 'apotheke', 'student insurance',
                'studentische selbstversicherung', 'student health insurance', 'ecard photo', 'e-card photo',
            ],
            'driving_vehicle' => [
                'führerschein', 'driving license', 'driver license', 'auto', 'kfz', 'zulassung', 'pickerl',
                '57a', 'vignette', 'asfinag', 'kennzeichen', 'fahrzeug', 'car registration', 'moped', 'motorrad',
                'zulassungsstelle', 'abmeldung kfz', 'tüv', 'umschreibung führerschein',
            ],
            'work_permit' => [
                'rot-weiß-rot', 'red-white-red', 'blaue karte', 'blue card', 'work permit', 'arbeitserlaubnis',
                'arbeitsbewilligung', 'beschäftigungsbewilligung', 'rwr', 'mangelberuf', 'fachkraft',
            ],
            'permit' => [
                'student residence permit', 'student permit', 'student visa', 'studienvisum', 'studentenvisum',
                'aufenthalt', 'aufenthaltsbewilligung', 'residence permit', 'nag', 'visum', 'visa',
                'drittstaat', 'visum d', 'visum c', 'niederlassung', 'oead', 'ma 35',
            ],
            'passport' => [
                'pass', 'reisepass', 'passport', 'personalausweis', 'ausweis', 'passfoto', 'verlustanzeige',
                'diebstahlsanzeige', 'notpass', 'expresspass', 'pass verlängern', 'pass erneuern',
            ],
            'residence' => [
                'umzug', 'umgezogen', 'umzieh', 'wohn', 'meld', 'resid', 'mov', 'adr', 'meldezettel',
                'wohnsitz', 'hauptwohnsitz', 'nebenwohnsitz', 'unterkunftgeber', 'meldeamt', 'abmelden wohnsitz',
            ],
            'id_austria' => [
                'id austria', 'handy-signatur', 'handysignatur', 'digitales amt', 'vollfunktion', 'eidas',
                'digitale identität', 'basisfunktion', 'elektronische signatur',
            ],
            'family' => [
                'familienbeihilfe', 'kinderbetreuungsgeld', 'karenz', 'elternkarenz', 'geburt', 'geburtsurkunde',
                'kinder', 'baby', 'schwanger', 'maternity', 'childcare', 'heirat', 'ehe', 'hochzeit',
                'standesamt', 'ehefähigkeitszeugnis', 'namensänderung', 'familie', 'vaterschaft',
            ],
            'employment' => [
                'ams', 'arbeitslos', 'arbeitslosengeld', 'notstandshilfe', 'job', 'arbeitsamt', 'unemployment',
                'geringfügig', 'geringfügigkeitsgrenze', 'kündigung', 'abfertigung', 'arbeitsvertrag',
            ],
            'citizenship' => [
                'staatsbürgerschaft', 'citizenship', 'einbürgerung', 'staatsbürger', 'naturalization',
                'österreichischer pass beantragen', 'staatsbürgerschaftsnachweis',
            ],
        ];

        $scores = [];
        foreach ($domainKeywords as $domain => $keywords) {
            $score = 0;
            foreach ($keywords as $kw) {
                if (str_contains($q, $kw)) {
                    $score += mb_strlen($kw); // Longer matched keywords give higher weight
                }
            }
            if ($score > 0) {
                $scores[$domain] = $score;
            }
        }

        if (empty($scores)) {
            return 'human';
        }

        arsort($scores);

        return array_key_first($scores);
    }

    /**
     * Provide a high-quality verified fallback response when the OpenRouter API is unavailable or offline.
     *
     * @return array{
     *     content: string,
     *     sources: array<int, array{title: string, url: string, description: string, tag: string}>,
     *     checklist?: array<int, string>,
     *     topic: string
     * }
     */
    public function getFallbackResponse(string $query, string $locale = 'de'): array
    {
        $domain = $this->detectDomain($query);
        $isGerman = str_starts_with(strtolower($locale), 'de');
        $sources = $this->getOfficialSources($domain, $locale);

        return match ($domain) {
            'passport' => $this->buildPassportFallback($isGerman, $sources),
            'permit' => $this->buildStudentPermitFallback($isGerman, $sources),
            'work_permit' => $this->buildWorkPermitFallback($isGerman, $sources),
            'residence' => $this->buildResidenceFallback($isGerman, $sources),
            'id_austria' => $this->buildIdAustriaFallback($isGerman, $sources),
            'tax' => $this->buildTaxFallback($isGerman, $sources),
            'health' => $this->buildHealthFallback($isGerman, $sources),
            'driving_vehicle' => $this->buildDrivingFallback($isGerman, $sources),
            'family' => $this->buildFamilyFallback($isGerman, $sources),
            'employment' => $this->buildEmploymentFallback($isGerman, $sources),
            'citizenship' => $this->buildCitizenshipFallback($isGerman, $sources),
            default => $this->buildHumanFallback($isGerman, $sources),
        };
    }

    /**
     * @param  array<int, array<string, string>>  $sources
     * @return array{content: string, sources: array<int, array<string, string>>, checklist: array<int, string>, topic: string}
     */
    protected function buildPassportFallback(bool $isGerman, array $sources): array
    {
        if ($isGerman) {
            $content = <<<'MD'
### Reisepass-Service der Republik Österreich (Passgesetz 1992)

Ein Reisepass kann in Österreich **nicht verlängert** werden – es wird stets ein **neuer Reisepass ausgestellt**. Der bisherige Reisepass darf bis zu 5 Jahre abgelaufen sein.

#### Ablauf der Neuausstellung
1. **Dokumente vorbereiten**: Bisherigen Reisepass und 1 aktuelles Passfoto (35 x 45 mm, max. 6 Monate alt nach EU-Biometriekriterien) bereitlegen.
2. **Termin online buchen**: Reservieren Sie einen Termin beim zuständigen Magistratischen Bezirksamt (in Wien) oder der Bezirkshauptmannschaft (BH).
3. **Persönlich vorsprechen**: Erfassung der biometrischen Fingerabdrücke und Unterschrift am Schalter (gesetzliche Pflicht).
4. **Zustellung abwarten**: Ihr neuer Reisepass wird per behördlichem RSb-Brief innerhalb von ca. 5 Werktagen direkt nach Hause zugestellt.

#### Benötigte Unterlagen
- [ ] **Bisheriger Reisepass**: Darf bis zu 5 Jahre abgelaufen sein (falls länger oder verloren: Geburtsurkunde & Staatsbürgerschaftsnachweis mitbringen).
- [ ] **1 Aktuelles Passfoto**: 35 x 45 mm, Hochformat, max. 6 Monate alt, neutraler Hintergrund nach ICAO/EU-Kriterien.
- [ ] **Gebühr**: 75,90 € Standard (100,00 € Express, 220,00 € Ein-Tages-Express) per Bankomat oder bar.
- [ ] **Verlustanzeige / Diebstahlsanzeige**: Nur erforderlich, wenn der alte Pass verloren ging oder gestohlen wurde.

#### Fristen & Gebühren
* **Standard-Reisepass (ab 18 Jahren):** **75,90 €** (Gültigkeit: 10 Jahre, Zustellung in ca. 5 Werktagen).
* **Expresspass:** **100,00 €** (Zustellung in 2–3 Werktagen).
* **Ein-Tages-Expresspass:** **220,00 €** (Zustellung am nächsten Werktag per Boten).
* **Kinder (2 bis 12 Jahre):** **30,00 €** (Gültigkeit: 5 Jahre).
* **Kinder bis 2 Jahre:** **Gebührenfrei** bei Erstausstellung (Gültigkeit: 2 Jahre).

[NEXT STEPS]
- Wo finde ich die nächste Passbehörde und freie Termine?
- Welche Kriterien muss das Passfoto genau erfüllen?
- Was muss ich tun, wenn mein Reisepass im Ausland gestohlen wurde?
- Wie beantrage ich einen Notpass am Flughafen Wien-Schwechat?
MD;
        } else {
            $content = <<<'MD'
### Austrian Passport Issuance & Renewal (Passport Act 1992)

In Austria, passports cannot be "extended"; every application is for the **issuance of a brand new passport**. Your current passport may be expired for up to 5 years.

#### Step-by-Step Procedure
1. **Prepare Required Documents**: Current or expired passport and 1 recent biometric photo (35x45 mm, max 6 months old).
2. **Book Appointment Online**: Schedule an appointment at any *Magistratisches Bezirksamt* (in Vienna) or district authority (*Bezirkshauptmannschaft*).
3. **Appear in Person**: Fingerprints, photo check, and signature must be submitted in person by Austrian law.
4. **Await Registered Delivery**: Your new passport is delivered to your Austrian address via registered mail (RSb) within approx. 5 working days.

#### Required Documents Checklist
- [ ] **Current or Expired Passport**: Valid or expired within the past 5 years.
- [ ] **1 Recent Passport Photograph**: 35 x 45 mm, portrait, neutral white background, max 6 months old.
- [ ] **Official Fee**: €75.90 standard fee (or €100.00 express) payable via debit card or cash.
- [ ] **Theft / Loss Report**: Only needed if your previous passport was lost or stolen.

#### Fees & Processing Times
* **Standard Adult Passport (18+):** **€75.90** (Valid for 10 years, delivered in approx. 5 working days).
* **Express Passport:** **€100.00** (Delivered in 2–3 working days).
* **One-Day Express:** **€220.00** (Delivered on the next business day).
* **Children (ages 2–12):** **€30.00** (Valid for 5 years).
* **Infants (up to 2 years):** **Free of charge** for first issuance (Valid for 2 years).

[NEXT STEPS]
- Where is the nearest passport office and appointment booking link?
- What are the exact biometric photo requirements?
- What should I do if my passport was lost or stolen abroad?
- How do I obtain an emergency passport at Vienna Airport?
MD;
        }

        return [
            'content' => $content,
            'sources' => $sources,
            'topic' => 'passport',
            'checklist' => $isGerman ? [
                'Bisherigen Reisepass bereitlegen',
                'Neues biometrisches Passbild (35x45mm) besorgen',
                'Termin bei der Passbehörde online buchen',
                '75,90 € Gebühr (Bankomat oder bar)',
            ] : [
                'Locate current or expired passport',
                'Obtain 1 biometric photo (35x45 mm)',
                'Book appointment at passport office',
                'Prepare fee (€75.90 via debit card or cash)',
            ],
        ];
    }

    /**
     * @param  array<int, array<string, string>>  $sources
     * @return array{content: string, sources: array<int, array<string, string>>, checklist: array<int, string>, topic: string}
     */
    protected function buildStudentPermitFallback(bool $isGerman, array $sources): array
    {
        if ($isGerman) {
            $content = <<<'MD'
### Aufenthaltstitel "Student" in Österreich (§ 64 NAG)

Drittstaatsangehörige (Nicht-EU/EWR/Schweiz), die ein ordentliches Studium an einer österreichischen Universität oder Fachhochschule absolvieren, benötigen eine **Aufenthaltsbewilligung für Studierende**.

#### Ablauf der Beantragung
1. **Zulassungsbescheid erhalten**: Die Aufnahmebestätigung (*Zulassungsbescheid*) der österreichischen Hochschule muss vorliegen.
2. **Antrag persönlich einreichen**: 
   - Bei Visumpflicht: Vor der Einreise bei der zuständigen österreichischen Vertretungsbehörde (Botschaft / Generalkonsulat) im Herkunftsland.
   - Bei visumfreier Einreise: Binnen der visumfreien Frist direkt bei der Inlandsbehörde (z. B. MA 35 in Wien oder Bezirkshauptmannschaft).
3. **Biometrie erfassen**: Abnahme von Fingerabdrücken, Foto und Unterschrift bei der Behörde.
4. **Aufenthaltstitel abholen**: Nach Genehmigung Abholung der Scheckkarte bei der Behörde.

#### Erforderliche Unterlagen
- [ ] **Gültiges Reisedokument**: Reisepass (Gültigkeit mindestens 3 Monate über den geplanten Aufenthalt hinaus).
- [ ] **Geburtsurkunde**: Oder gleichwertiges Dokument (ggf. mit Apostille/diplomatischer Beglaubigung & beeidigter Übersetzung).
- [ ] **Aktuelles Passfoto**: Biometrisch nach EU-Kriterien (35 x 45 mm, max. 6 Monate alt).
- [ ] **Zulassungsbestätigung**: Gültiger Zulassungsbescheid der österreichischen Universität/Hochschule.
- [ ] **Nachweis ausreichender Existenzmittel**: Für unter 24-Jährige ca. 672 €/Monat, ab 24 Jahren ca. 1.217 €/Monat (z. B. Bankguthaben auf eigenem Sparkonto, Verpflichtungserklärung oder Stipendium).
- [ ] **Rechtsanspruch auf Unterkunft**: Nachweis über eine ortsübliche Unterkunft (z. B. Mietvertrag, Studierendenwohnheimvertrag).
- [ ] **Krankenversicherungsschutz**: Alle Risken abdeckende Versicherung (z. B. Studentische Selbstversicherung bei der ÖGK).
- [ ] **Polizeiliches Führungszeugnis**: Strafregisterbescheinigung aus dem Herkunftsland (max. 3 Monate alt, beglaubigt).

#### Kosten & Gültigkeit
* **Gebühren:** Insgesamt **160,00 €** (120 € Bundesgebühr bei Antragstellung, 20 € bei Erteilung, 20 € Personalisierungsgebühr).
* **Gültigkeitsdauer:** In der Regel **12 Monate** (wird jeweils um 1 Jahr verlängert bei Nachweis des Studienerfolgs von mind. 16 ECTS-Punkten pro Studienjahr).

[NEXT STEPS]
- Benötigen Sie Unterstützung beim Nachweis der finanziellen Mittel?
- Wie beantrage ich die studentische Krankenversicherung bei der ÖGK?
- Wie buche ich einen Termin bei der Einwanderungsbehörde (MA 35)?
- Welche Bestimmungen gelten für Nebenjobs und Arbeitserlaubnis während des Studiums?
MD;
        } else {
            $content = <<<'MD'
### Student Residence Permit in Austria (*Aufenthaltsbewilligung Student* - § 64 NAG)

Third-country nationals (non-EU/EEA/Swiss citizens) studying full-time at an Austrian accredited university or higher education institution require a **Student Residence Permit**.

#### Step-by-Step Application Procedure
1. **Obtain University Admission**: You must have a formal letter of admission (*Zulassungsbescheid*) from an Austrian university.
2. **Submit Application in Person**:
   - If visa-required: Apply abroad before traveling to Austria at the competent Austrian embassy or consulate in your country of legal residence.
   - If visa-free travel applies: You may submit the application within your visa-free stay directly to the immigration authority in Austria (e.g. MA 35 in Vienna).
3. **Biometrics & Interview**: Fingerprints, biometric photo, and verification of original documents.
4. **Permit Card Collection**: Pick up your plastic residence card in person upon approval.

#### Required Documents Checklist
- [ ] **Valid Travel Passport**: Valid for at least 3 months beyond the intended validity of the permit.
- [ ] **Birth Certificate**: Legalized/Apostilled and officially translated into German by a sworn court translator.
- [ ] **1 Recent Biometric Photo**: 35 x 45 mm, EU standard, neutral white background, max 6 months old.
- [ ] **University Admission Letter**: Official *Zulassungsbescheid* or study confirmation.
- [ ] **Proof of Sufficient Financial Means**: Min. ~€672/month (under 24) or ~€1,217/month (24+) for 12 months ahead (e.g. personal bank balance, scholarship, or sponsor declaration).
- [ ] **Proof of Accommodation**: Valid rental lease or student dormitory booking contract in Austria.
- [ ] **Comprehensive Health Insurance**: Covering all health risks (e.g. ÖGK student self-insurance or private equivalent).
- [ ] **Police Clearance Certificate**: Criminal record extract from country of origin (max 3 months old, apostilled).

#### Fees & Validity
* **Statutory Fees:** Total **€160.00** (€120 application fee + €20 issuance fee + €20 personalization fee).
* **Validity:** Typically issued for **12 months** (renewable annually upon proving min. 16 ECTS credits per academic year).

[NEXT STEPS]
- Do you need help with the financial proof requirements?
- How do I apply for Austrian student health insurance (ÖGK)?
- How do I book an appointment with the immigration office (MA 35)?
- What are the work permit rules and part-time limits for international students?
MD;
        }

        return [
            'content' => $content,
            'sources' => $sources,
            'topic' => 'permit',
            'checklist' => $isGerman ? [
                'Zulassungsbescheid der Hochschule erhalten',
                'Reisepass (mind. 3 Monate über Gültigkeit) prüfen',
                'Finanzierungsnachweis (Bankguthaben / Stipendium) vorbereiten',
                'Mietvertrag oder Wohnheimplatz nachweisen',
                'Polizeiliches Führungszeugnis mit Apostille beschaffen',
                'Krankenversicherung (ÖGK Selbstversicherung) abschließen',
            ] : [
                'Receive official university admission letter (Zulassungsbescheid)',
                'Verify passport validity (at least 3 months past stay)',
                'Prepare financial proof (bank balance for 12 months or scholarship)',
                'Secure accommodation lease or student dormitory contract',
                'Obtain criminal record extract with apostille / translation',
                'Arrange comprehensive health insurance (ÖGK student policy)',
            ],
        ];
    }

    /**
     * @param  array<int, array<string, string>>  $sources
     * @return array{content: string, sources: array<int, array<string, string>>, checklist: array<int, string>, topic: string}
     */
    protected function buildWorkPermitFallback(bool $isGerman, array $sources): array
    {
        if ($isGerman) {
            $content = <<<'MD'
### Rot-Weiß-Rot – Karte & Arbeitserlaubnis in Österreich (AuslBG & NAG)

Die **Rot-Weiß-Rot – Karte** (RWR-Karte) ist der zentrale kombinierte Aufenthalts- und Arbeitstitel für qualifizierte Arbeitskräfte aus Drittstaaten. Sie bindet die Arbeitserlaubnis an einen konkreten Arbeitgeber.

#### Kategorien der Rot-Weiß-Rot – Karte
1. **Besonders Hochqualifizierte**: Punktesystem (mind. 70 von 100 Punkten). Berechtigt auch zu einem 6-monatigen Arbeitsplatzsuche-Visum.
2. **Fachkräfte in Mangelberufen**: Punktesystem (mind. 55 von 90 Punkten) in bundesweit oder regional definierten Mangelberufen.
3. **Sonstige Schlüsselkräfte**: Mindestbruttogehalt nach Kollektivvertrag bzw. gesetzlichen Schwellenwerten.
4. **Studienabsolventen österreichischer Hochschulen**: Nach Bachelor/Master 12 Monate zur Arbeitsplatzsuche; RWR-Karte ohne Arbeitsmarktprüfung bei qualifiziertem Jobangebot.

#### Ablauf der Beantragung
1. **Verbindliches Jobangebot einholen**: Arbeitgebererklärung (*Formular Arbeitgebererklärung*) vom österreichischen Unternehmen ausfüllen lassen.
2. **Antrag einreichen**: Durch den Antragsteller bei der Vertretungsbehörde im Ausland oder durch den österreichischen Arbeitgeber direkt bei der Inlandsbehörde (z. B. MA 35 in Wien).
3. **AMS-Prüfung**: Das AMS prüft die Punkte und Arbeitsmarktbedingungen.
4. **Ausstellung**: Nach positiver Gutachtenerstellung Ausstellung der Karte für 24 Monate. Nach 21 Monaten Umstieg auf die freie **Rot-Weiß-Rot – Karte Plus** möglich.

#### Benötigte Dokumente
- [ ] **Arbeitgebererklärung**: Offizielles AMS-Formular mit genauer Tätigkeitsbeschreibung und Gehalt.
- [ ] **Ausbildungs- & Qualifikationsnachweise**: Zeugnisse, Diplome, Berufserfahrungsbestätigungen (übersetzt & beglaubigt).
- [ ] **Sprachzertifikate**: Deutsch (Goethe/ÖSD) oder Englisch (IELTS/TOEFL) für zusätzliche Punkte.
- [ ] **Reisepass & Geburtsurkunde**: Gültiges Reisedokument und beglaubigte Geburtsurkunde.
- [ ] **Passfoto**: 35 x 45 mm, EU-Standard biometrisch.

[NEXT STEPS]
- Gehört mein Beruf zur aktuellen österreichischen Mangelberufsliste?
- Wie viele Punkte erreiche ich im RWR-Punkte-Rechner?
- Welche Voraussetzungen gelten für den Umstieg auf die Rot-Weiß-Rot – Karte Plus?
- Wie funktioniert die Blaue Karte EU im Vergleich zur RWR-Karte?
MD;
        } else {
            $content = <<<'MD'
### Red-White-Red Card & Work Permits in Austria (AuslBG & NAG)

The **Red-White-Red Card** (RWR Card) is Austria's points-based residence and work permit for qualified third-country professionals. It grants residency tied to a specific employer.

#### Main Categories of the Red-White-Red Card
1. **Very Highly Qualified Workers**: Points threshold (min. 70/100 points). Eligible for a 6-month Jobseeker Visa.
2. **Skilled Workers in Shortage Occupations**: Points threshold (min. 55/90 points) in officially designated shortage trades.
3. **Other Key Workers**: Must meet collective bargaining wage minimums.
4. **Graduates of Austrian Universities**: 12-month post-study job search visa; RWR card issued without labor market test for matching jobs.

#### Step-by-Step Procedure
1. **Secure Binding Job Offer**: Austrian employer completes the official *Arbeitgebererklärung* declaration.
2. **Submit Application**: At the Austrian embassy in your country of residence, or submitted directly in Austria by your employer.
3. **AMS Assessment**: The Public Employment Service (AMS) reviews your qualifications and points score.
4. **Permit Card Issuance**: Issued for 24 months. After 21 months with the same employer, you can transition to the unrestricted **Red-White-Red Card Plus**.

#### Required Documents Checklist
- [ ] **Employer Declaration (Arbeitgebererklärung)**: Signed official form detailing job role, duties, and gross salary.
- [ ] **Qualification & Degree Proof**: Certified university diplomas and employment certificates with sworn translations.
- [ ] **Language Certificates**: German (ÖSD/Goethe) or English (IELTS/TOEFL) for points evaluation.
- [ ] **Passport & Birth Certificate**: Valid passport (valid 3+ months beyond permit) and legalized birth certificate.
- [ ] **1 Biometric Photo**: 35 x 45 mm, max 6 months old.

[NEXT STEPS]
- Is my occupation currently on the Austrian shortage occupations list?
- How many points do I score on the official RWR points calculator?
- What are the rules for upgrading to the Red-White-Red Card Plus?
- What are the differences between the EU Blue Card and the Red-White-Red Card?
MD;
        }

        return [
            'content' => $content,
            'sources' => $sources,
            'topic' => 'work_permit',
            'checklist' => $isGerman ? [
                'Arbeitgebererklärung vom Arbeitgeber unterzeichnen lassen',
                'Diplome und Zeugnisse übersetzen und beglaubigen lassen',
                'Sprachzertifikat (Deutsch ÖSD/Goethe oder Englisch) bereitlegen',
                'Antrag bei der Botschaft oder Inlandsbehörde einreichen',
            ] : [
                'Obtain signed employer declaration form (Arbeitgebererklärung)',
                'Collect certified degree certificates with sworn German translations',
                'Prepare language proficiency test results (ÖSD/Goethe/IELTS)',
                'Submit application to Austrian embassy or local authority',
            ],
        ];
    }

    /**
     * @param  array<int, array<string, string>>  $sources
     * @return array{content: string, sources: array<int, array<string, string>>, checklist: array<int, string>, topic: string}
     */
    protected function buildResidenceFallback(bool $isGerman, array $sources): array
    {
        if ($isGerman) {
            $content = <<<'MD'
### Wohnsitz An- und Ummeldung in Österreich (Meldegesetz 1972)

In Österreich gilt nach dem Meldegesetz 1972 eine gesetzliche Meldepflicht. Die Meldung ist **vollständig gebührenfrei**.

#### Ablauf der Ummeldung
1. **Meldezettel ausfüllen**: Bundes-Meldezettel vollständig ausfüllen (Angaben zu Haupt- oder Nebenwohnsitz).
2. **Vermieter-Unterschrift einholen**: Bei Miete zwingend die Unterschrift der Unterkunftgeberin / des Unterkunftgebers einholen. Als Wohnungseigentümer unterschreiben Sie selbst.
3. **Meldung einreichen**: Digital per ID Austria in der App "Digitales Amt" oder persönlich am Gemeindeamt / Wiener Bezirksamt einreichen.
4. **Folgepflichten erledigen**: KFZ binnen 1 Woche ummelden, Adressänderung bei OBS (ehemals GIS / ORF-Beitrag) und Arbeitgeber bekanntgeben.

#### Benötigte Dokumente
- [ ] **Meldezettel-Formular**: Vollständig ausgefüllt mit allen Pflichtangaben.
- [ ] **Bestätigung des Unterkunftgebers**: Vermieter-Unterschrift bei Mietwohnungen.
- [ ] **Amtlicher Lichtbildausweis**: Reisepass oder Personalausweis für alle mitziehenden Personen.
- [ ] **Gebühr**: 0,00 € (Gesetzlich gebührenfrei).

#### Wichtige Fristen & Regeln
* **Gesetzliche Frist:** Innerhalb von **3 Werktagen** ab tatsächlichem Einzug (§ 1 Abs. 1 MeldeG).
* **Kosten:** Die Wohnsitzanmeldung ist **vollständig gebührenfrei**.
* **Automatische Abmeldung:** Bei Anmeldung des neuen Wohnsitzes wird der alte Wohnsitz automatisch abgemeldet!

[NEXT STEPS]
- Wie melde ich meinen Wohnsitz 100% online über ID Austria an?
- Wo kann ich das offizielle Meldezettel-PDF herunterladen?
- Was ist der Unterschied zwischen Hauptwohnsitz und Nebenwohnsitz?
- Wie melde ich den ORF-Beitrag (ehemals GIS) an die neue Adresse um?
MD;
        } else {
            $content = <<<'MD'
### Residence Registration in Austria (Residence Registration Act 1972)

Under the Austrian Residence Registration Act (*Meldegesetz 1972*), registration is mandatory for all residents. Registration is **completely free of charge**.

#### Registration Procedure
1. **Complete Meldezettel Form**: Fill in the official registration application form (Hauptwohnsitz or Nebenwohnsitz).
2. **Obtain Landlord Signature**: If renting, the property owner / landlord must sign section 2 of the form.
3. **Submit Registration**: 100% digitally with ID Austria via the "Digitales Amt" app, or in-person at city hall / district office.
4. **Complete Follow-up Steps**: Update vehicle registration within 1 week, notify broadcast fee (OBS / ORF fee).

#### Required Documents Checklist
- [ ] **Meldezettel Form**: Signed by applicant and accommodation provider (landlord).
- [ ] **Official Photo ID**: Passport or European National ID card.
- [ ] **Registration Fee**: €0.00 (Completely free by law).

#### Deadlines & Key Rules
* **Deadline:** You must register within **3 business days** of moving into your new accommodation.
* **Simultaneous Deregistration:** When registering your new address, your previous Austrian residence is automatically deregistered.

[NEXT STEPS]
- How do I submit my residence registration online with ID Austria?
- Where can I download the official Meldezettel PDF form?
- What is the legal difference between primary (Hauptwohnsitz) and secondary residence?
- How do I notify the ORF broadcast contribution fee about my move?
MD;
        }

        return [
            'content' => $content,
            'sources' => $sources,
            'topic' => 'residence',
            'checklist' => $isGerman ? [
                'Meldezettel-Formular ausfüllen',
                'Unterschrift der Vermieterin / des Vermieters einholen',
                'Amtlichen Lichtbildausweis bereitstellen',
                'Innerhalb von 3 Tagen online (ID Austria) oder am Meldeamt einreichen',
                'KFZ-Zulassungsstelle & ORF-Beitrag verständigen',
            ] : [
                'Complete Meldezettel registration form',
                'Obtain landlord signature (if renting)',
                'Prepare official photo ID (Passport / EU ID card)',
                'Submit within 3 days (online with ID Austria or in-person)',
                'Notify vehicle registry and ORF broadcast fee',
            ],
        ];
    }

    /**
     * @param  array<int, array<string, string>>  $sources
     * @return array{content: string, sources: array<int, array<string, string>>, checklist: array<int, string>, topic: string}
     */
    protected function buildIdAustriaFallback(bool $isGerman, array $sources): array
    {
        if ($isGerman) {
            $content = <<<'MD'
### ID Austria - Die digitale Identität der Republik Österreich

Die **ID Austria** ist die offizielle Weiterentwicklung der Handy-Signatur und dient als sicherer elektronischer Identitätsnachweis (eID) für alle digitalen Behördenwege in Österreich und der EU (eIDAS).

#### Die zwei Stufen der ID Austria
1. **Basisfunktion**: Ermöglicht elektronisches Unterschreiben und Zugang zu Web-Portalen (FinanzOnline, MeineSV).
2. **Vollfunktion**: Ermöglicht zusätzlich den digitalen Führerschein, digitalen Altersnachweis, digitalen Zulassungsschein und den digitalen Meldezettel am Smartphone.

#### Ablauf der Aktivierung (Vollfunktion)
1. **App herunterladen**: App "Digitales Amt" auf dem Smartphone installieren.
2. **Behördliche Registrierung**: Einmalige Identitätsfeststellung bei einer Passbehörde (Bezirksamt/BH) oder Landespolizeidirektion (bei Reisepass-Antrag automatisch möglich).
3. **Führung per Smartphone**: Fingerabdruck (Touch ID) oder Gesichtserkennung (Face ID) in der App verknüpfen.

#### Benötigte Unterlagen
- [ ] **Gültiger österreichischer Reisepass oder Personalausweis**: Zur behördlichen Identitätsfeststellung.
- [ ] **Aktuelles Passfoto**: Max. 6 Monate alt (nur wenn nicht zeitgleich ein Reisepass beantragt wird).
- [ ] **Smartphone**: Mit aktivierter Bildschirmsperre (Biometrie empfohlen).

[NEXT STEPS]
- Wie rüste ich meine bestehende Handy-Signatur auf ID Austria um?
- Wo kann ich die ID Austria ohne Termin aktivieren lassen?
- Welche digitalen Ausweise kann ich mit der ID Austria auf dem Handy nutzen?
- Wie melde ich mich mit ID Austria bei FinanzOnline und MeineSV an?
MD;
        } else {
            $content = <<<'MD'
### ID Austria - Official Digital Identity of the Republic of Austria

**ID Austria** is the successor to the Handy-Signatur, functioning as your secure, legally binding electronic identity (eID) for public administration across Austria and the European Union (eIDAS standard).

#### Two Tiers of ID Austria
1. **Basic Functionality**: Electronic document signing and digital login to public portals (FinanzOnline, MeineSV).
2. **Full Functionality (Vollfunktion)**: Digital driving license, mobile proof of age, digital vehicle registration, and instant address changes.

#### How to Activate Full Functionality
1. **Download App**: Install the official "Digitales Amt" app on your iOS or Android smartphone.
2. **Identity Verification**: In-person identity check at any passport authority (Magistratisches Bezirksamt, BH) or police department.
3. **Biometric Link**: Connect Face ID or fingerprint authentication on your mobile device.

#### Required Documents Checklist
- [ ] **Valid Austrian Passport or National ID**: For passport authority verification.
- [ ] **1 Recent Passport Photo**: Max 6 months old (if not applying for a passport concurrently).
- [ ] **Smartphone**: With active lock screen security (Touch ID / Face ID).

[NEXT STEPS]
- How do I upgrade my old Handy-Signatur to ID Austria?
- Where can I register for ID Austria in person?
- How do I access my digital driving license with ID Austria?
- How do I log in to FinanzOnline using ID Austria?
MD;
        }

        return [
            'content' => $content,
            'sources' => $sources,
            'topic' => 'id_austria',
            'checklist' => $isGerman ? [
                'App Digitales Amt installieren',
                'Österreichischen Reisepass oder Personalausweis bereithalten',
                'Passfoto besorgen (falls kein Passantrag parallel)',
                'Termin bei Passbehörde oder Polizei wahrnehmen',
            ] : [
                'Install Digitales Amt mobile app',
                'Prepare valid passport or Austrian ID card',
                'Bring recent passport photo (if standalone registration)',
                'Visit passport authority or police office for activation',
            ],
        ];
    }

    /**
     * @param  array<int, array<string, string>>  $sources
     * @return array{content: string, sources: array<int, array<string, string>>, checklist: array<int, string>, topic: string}
     */
    protected function buildTaxFallback(bool $isGerman, array $sources): array
    {
        if ($isGerman) {
            $content = <<<'MD'
### Steuerausgleich & FinanzOnline (Arbeitnehmerveranlagung - EStG 1988)

Die **Arbeitnehmerveranlagung** (Lohnsteuerausgleich) ermöglicht es Steuerzahlern in Österreich, zu viel bezahlte Lohnsteuer vom Finanzamt zurückzuerhalten. Der Antrag kann **bis zu 5 Jahre rückwirkend** gestellt werden.

#### Wichtige Absetzbeträge & Boni
1. **Familienbonus Plus**: Bis zu **2.000 € pro Kind** (unter 18 J.) bzw. 700 € (ab 18 J.) direkt von der Steuerschuld abziehbar.
2. **Pendlerpauschale & Pendlereuro**: Steuerentlastung für den Arbeitsweg bei Unzumutbarkeit öffentlicher Verkehrsmittel.
3. **Alleinverdiener- / Alleinerzieherabsetzbetrag**: Steuerabsetzbetrag für Alleinerziehende oder Familien mit einem Einkommen.
4. **Werbungskosten & Sonderausgaben**: Arbeitsmittel, Fortbildungskosten, Kirchenbeitrag (bis 600 €), Spenden.

#### Ablauf der Einreichung
1. **Lohnzettel abwarten**: Ihr Arbeitgeber übermittelt den Jahreslohnzettel bis spätestens Ende Februar an das Finanzamt.
2. **FinanzOnline öffnen**: Login auf [finanzonline.bmf.gv.at](https://finanzonline.bmf.gv.at/) mit ID Austria oder Zugangsdaten.
3. **Formular L1 ausfüllen**: Daten prüfen, Familienbonus, Pendlerpauschale und Sonderausgaben eintragen.
4. **Bescheid & Auszahlung erhalten**: Der Steuerbescheid wird digital zugestellt; Guthaben wird direkt auf Ihr Bankkonto überwiesen.

#### Fristen & Kosten
* **Frist:** Freiwillige Veranlagung kann bis zu **5 Jahre rückwirkend** eingereicht werden (z. B. Steuerjahr 2021 bis Ende 2026).
* **Kosten:** Die Einreichung über FinanzOnline ist **vollständig kostenlos**.

[NEXT STEPS]
- Wie beantrage ich den Familienbonus Plus im Steuerausgleich?
- Wie berechne ich meine Pendlerpauschale mit dem offiziellen Pendlerrechner?
- Bis zu welchem Datum kann ich den Steuerausgleich rückwirkend einreichen?
- Was ist die automatische (antragslose) Arbeitnehmerveranlagung?
MD;
        } else {
            $content = <<<'MD'
### Austrian Tax Return & FinanzOnline (*Arbeitnehmerveranlagung*)

The Austrian **Employee Tax Assessment** (*Arbeitnehmerveranlagung / Lohnsteuerausgleich*) allows taxpayers to claim back overpaid income tax from the tax office (*Finanzamt*). You can file retroactively for **up to 5 years**.

#### Key Deductions & Tax Credits
1. **Family Bonus Plus (Familienbonus Plus)**: Up to **€2,000 per child** under 18 (or €700 for adult children) directly credited against tax liability.
2. **Commuter Allowance (Pendlerpauschale & Pendlereuro)**: Tax deductions for daily travel to work where public transit is unavailable.
3. **Single Earner / Single Parent Credit**: Targeted refunds for single-income households and single parents.
4. **Work Expenses (Werbungskosten)**: Professional training, specialized literature, home office lump sum, and charitable donations.

#### Step-by-Step Filing Procedure
1. **Wait for Annual Wage Slip**: Employers submit the annual wage statement (L16) to the tax office by end of February.
2. **Log into FinanzOnline**: Access [finanzonline.bmf.gv.at](https://finanzonline.bmf.gv.at/) securely using ID Austria.
3. **Complete Form L1**: Review auto-filled earnings and enter eligible credits (Family Bonus, Commuter allowance).
4. **Receive Assessment & Refund**: The tax assessment notice is issued digitally, and refunds are transferred directly to your Austrian IBAN.

#### Deadlines & Fees
* **Deadline:** Voluntary filings can be submitted up to **5 calendar years retroactively**.
* **Filing Fee:** 100% **free of charge** via FinanzOnline.

[NEXT STEPS]
- How do I claim the Family Bonus Plus on my tax return?
- How do I calculate my commuter allowance with the official BMF calculator?
- What expenses qualify as deductible work costs (Werbungskosten)?
- What is the automatic tax assessment (antragslose Veranlagung)?
MD;
        }

        return [
            'content' => $content,
            'sources' => $sources,
            'topic' => 'tax',
            'checklist' => $isGerman ? [
                'Jahreslohnzettel (L16) auf FinanzOnline prüfen',
                'Nachweise für Werbungskosten und Fortbildungen sammeln',
                'Familienbonus Plus Angaben bereithalten',
                'Erklärung digital über FinanzOnline einreichen',
            ] : [
                'Verify employer wage statement (L16) on FinanzOnline',
                'Gather receipts for professional expenses and training',
                'Prepare child identification details for Family Bonus Plus',
                'Submit tax return electronically via FinanzOnline',
            ],
        ];
    }

    /**
     * @param  array<int, array<string, string>>  $sources
     * @return array{content: string, sources: array<int, array<string, string>>, checklist: array<int, string>, topic: string}
     */
    protected function buildHealthFallback(bool $isGerman, array $sources): array
    {
        if ($isGerman) {
            $content = <<<'MD'
### Gesundheit, e-card & Krankenversicherung (ÖGK & ASVG)

Das österreichische Gesundheitssystem bietet über die **Österreichische Gesundheitskasse (ÖGK)** und weitere Sozialversicherungsträger umfassenden Krankenversicherungsschutz für alle Bürgerinnen und Bürger.

#### e-card mit Foto Pflicht
Seit dem e-card-Foto-Gesetz müssen alle Versicherten ab 14 Jahren ein Passfoto auf ihrer e-card haben.
1. **Prüfen**: Auf [chipkarte.at](https://www.chipkarte.at/) den Fotostatus prüfen.
2. **Foto registrieren**: Wenn kein Reisepass oder Personalausweis vorliegt, Foto bei einer Registrierungsstelle (Passbehörde oder Polizei) hinterlegen.
3. **Ausstellung**: Die neue e-card wird automatisch per Post zugestellt.

#### Selbstversicherung für Studierende & Teilzeit
* **Studentische Selbstversicherung**: Ordentliche Studierende können sich bei der ÖGK für ca. **69 €/Monat** krankenversichern (Studiennachweis und ECTS-Erfolg erforderlich).
* **Geringfügig Beschäftigte**: Selbstversicherung bei geringfügiger Beschäftigung (unter ca. 518 €/Monat) für ca. **73 €/Monat** (beinhaltet Kranken- und Pensionsversicherung).

#### Rezeptgebühr & Befreiung
* **Gesetzliche Rezeptgebühr:** ca. **7,10 €** pro Medikament.
* **Rezeptgebührenobergrenze:** Maximal **2 % des Nettoeinkommens** pro Kalenderjahr. Sobald dieser Betrag erreicht ist, sind alle weiteren Rezepte im Kalenderjahr automatisch gebührenfrei!

[NEXT STEPS]
- Wie beantrage ich die studentische Selbstversicherung bei der ÖGK?
- Wie prüfe ich, ob ein Foto für meine e-card im System vorhanden ist?
- Wo finde ich die Antragsformulare für eine Rezeptgebührenbefreiung?
- Wie funktioniert die Europäische Krankenversicherungskarte (EKVK) im EU-Ausland?
MD;
        } else {
            $content = <<<'MD'
### Healthcare, e-card & Health Insurance in Austria (ÖGK & ASVG)

Healthcare in Austria is primarily provided through the **Austrian Health Insurance Fund (ÖGK)**, granting comprehensive access to public doctors, hospitals, and subsidized prescription medicines.

#### Mandatory Photo on e-card
Under Austrian law, all insured individuals aged 14 and older must have a portrait photo registered on their health card (*e-card mit Foto*).
1. **Check Status**: Check your photo status at [chipkarte.at](https://www.chipkarte.at/).
2. **Photo Registration**: If you do not hold an Austrian passport or driver's license, submit a biometric photo at designated registration centers (police or passport offices).
3. **Delivery**: The updated card is delivered by mail to your registered Austrian residence.

#### Low-Cost Self-Insurance Options
* **Student Self-Insurance (ÖGK)**: Enrolled higher education students can purchase full statutory health coverage for approx. **€69/month**.
* **Marginal Employment Insurance**: Workers earning below the monthly ceiling (~€518/month) can self-insure for health and retirement for approx. **€73/month**.

#### Prescription Fees & Exemption
* **Standard Prescription Fee:** Approx. **€7.10** per prescribed item.
* **Prescription Fee Cap:** Capped at **2% of annual net income**. Once reached, prescription fees are waived automatically for the rest of the calendar year.

[NEXT STEPS]
- How do I apply for the student health insurance policy with ÖGK?
- How do I verify whether my e-card photo is registered?
- How do I qualify for prescription fee exemption in Austria?
- How do I use the European Health Insurance Card (EHIC) on the back of my e-card?
MD;
        }

        return [
            'content' => $content,
            'sources' => $sources,
            'topic' => 'health',
            'checklist' => $isGerman ? [
                'Fotostatus der e-card auf chipkarte.at überprüfen',
                'Biometrisches Passfoto bei Bedarf bei der Registrierungsstelle hinterlegen',
                'Inskriptionsbestätigung für studentische Selbstversicherung bereitlegen',
                'Krankmeldung bei Arbeitsunfähigkeit unverzüglich an ÖGK & Arbeitgeber senden',
            ] : [
                'Check e-card photo status online at chipkarte.at',
                'Submit biometric photo at registration office if required',
                'Prepare university enrollment confirmation for student insurance',
                'Report sick leave to employer and ÖGK immediately when ill',
            ],
        ];
    }

    /**
     * @param  array<int, array<string, string>>  $sources
     * @return array{content: string, sources: array<int, array<string, string>>, checklist: array<int, string>, topic: string}
     */
    protected function buildDrivingFallback(bool $isGerman, array $sources): array
    {
        if ($isGerman) {
            $content = <<<'MD'
### Führerschein, KFZ-Zulassung & Vignette (FSG, KFG & ASFINAG)

Alles Wissenswerte zu Straßenverkehr, Führerscheinen und Fahrzeuganmeldungen in Österreich.

#### Führerschein-Umschreibung ausländischer Führerscheine (§ 23 FSG)
* **EU/EWR-Führerscheine:** Bleiben in Österreich unbegrenzt gültig und müssen bei Wohnsitzwechsel **nicht** umgeschrieben werden.
* **Drittstaats-Führerscheine:** Dürfen nach Begründung des Hauptwohnsitzes in Österreich maximal **6 Monate** verwendet werden. Danach ist die behördliche Umschreibung bei der Landespolizeidirektion oder Bezirkshauptmannschaft zwingend erforderlich (ggf. mit praktischer Fahrprüfung und ärztlichem Gutachten).

#### KFZ-Zulassung & Ummeldung
1. **Versicherungsbestätigung holen**: Vorab bei einer österreichischen KFZ-Haftpflichtversicherung eine elektronische Versicherungsbestätigung anfordern.
2. **Zulassungsstelle aufsuchen**: Jede beliebige KFZ-Zulassungsstelle im zuständigen Zulassungsbezirk aufsuchen.
3. **Dokumente vorlegen**: Typenschein/COC-Papier, Kaufvertrag/Rechnung, gültiges § 57a Prüfgutachten ("Pickerl"), Meldezettel und Lichtbildausweis.
4. **Kennzeichen & Zulassungsschein erhalten**: Gebühr ca. **199,50 €** (inkl. Kennzeichentafeln und Pickerl-Plakette).

#### § 57a "Pickerl" & Autobahn-Vignette
* **Pickerl-Intervall für PKW (3-2-1 Regel):** Erste Begutachtung nach 3 Jahren, zweite nach weiteren 2 Jahren, danach jährlich. Toleranzfrist: 1 Monat vor bis 4 Monate nach Fälligkeitsmonat.
* **ASFINAG Digitale Vignette:** Jahresvignette, 2-Monats-Vignette, 10-Tages-Vignette oder 1-Tages-Vignette. Im Webshop 18 Tage Konsumentenschutzfrist vor Gültigkeitsbeginn beachten!

[NEXT STEPS]
- Welche Länder haben ein Abkommen zur prüfungsfreien Führerschein-Umschreibung?
- Wo befindet sich die nächste KFZ-Zulassungsstelle in meinem Bezirk?
- Wie aktiviere ich den digitalen Führerschein am Smartphone mit ID Austria?
- Wie kaufe ich eine digitale ASFINAG-Vignette mit sofortiger Gültigkeit?
MD;
        } else {
            $content = <<<'MD'
### Driving License, Vehicle Registration & Motorway Tolls (FSG & KFG)

Official guidance for motor vehicles, driving licenses, and highway travel across the Republic of Austria.

#### Foreign Driving License Conversions (§ 23 FSG)
* **EU/EEA Driving Licenses:** Fully recognized indefinitely without any obligation to exchange.
* **Non-EU / Third-Country Licenses:** Valid for only **6 months** from the date you establish permanent residence in Austria. You must convert it through the police authority or district commission (practical driving test or medical certificate may be required depending on country of origin).

#### Vehicle Registration Procedure (KFZ-Zulassung)
1. **Obtain Insurance Confirmation**: Request electronic insurance proof from an Austrian motor vehicle liability provider.
2. **Visit Registration Office**: Visit any certified registration office (*Zulassungsstelle*) in your political district.
3. **Submit Vehicle Documents**: Certificate of conformity (COC) / Typenschein, purchase contract/invoice, valid § 57a inspection certificate (*Pickerl*), residence registration (*Meldezettel*), and ID.
4. **Receive Number Plates**: Standard registration fee is approx. **€199.50** (including license plates and inspection sticker).

#### Vehicle Inspection (*Pickerl*) & Highway Vignette
* **§ 57a Inspection (3-2-1 Rule):** New private cars must be inspected after 3 years, then after 2 years, and annually thereafter. Grace period: 1 month before to 4 months after the due month.
* **ASFINAG Digital Toll Vignette:** Mandatory for motorways. When buying online, take note of the 18-day consumer protection delay before validity begins, or select immediate business use.

[NEXT STEPS]
- Does my home country qualify for license conversion without a practical test?
- Where is the closest vehicle registration office in my district?
- How do I set up the mobile digital driving license on my smartphone?
- How do I purchase an ASFINAG digital vignette with immediate validity?
MD;
        }

        return [
            'content' => $content,
            'sources' => $sources,
            'topic' => 'driving_vehicle',
            'checklist' => $isGerman ? [
                'Elektronische KFZ-Versicherungsbestätigung anfordern',
                'Typenschein / COC-Dokument und Kaufvertrag bereitlegen',
                'Gültiges § 57a Prüfgutachten ("Pickerl") vorlegen',
                'Drittstaats-Führerschein innerhalb von 6 Monaten umschreiben lassen',
            ] : [
                'Request electronic motor insurance confirmation',
                'Locate vehicle title (Typenschein/COC) and bill of sale',
                'Ensure § 57a vehicle inspection certificate is valid',
                'Apply for non-EU driver license conversion within 6 months',
            ],
        ];
    }

    /**
     * @param  array<int, array<string, string>>  $sources
     * @return array{content: string, sources: array<int, array<string, string>>, checklist: array<int, string>, topic: string}
     */
    protected function buildFamilyFallback(bool $isGerman, array $sources): array
    {
        if ($isGerman) {
            $content = <<<'MD'
### Familie, Kinderbetreuungsgeld & Eheschließung (FLAG, KBGG & PStG)

Die Republik Österreich unterstützt Familien mit umfassenden staatlichen Leistungen von der Geburt bis zum Berufseinstieg.

#### Familienbeihilfe (Kinderbeihilfe)
* **Voraussetzung:** Hauptwohnsitz und tatsächlicher Aufenthalt des Kindes in Österreich.
* **Automatische Zuerkennung:** Bei der Geburt eines Kindes in Österreich erfolgt die Zuerkennung der Familienbeihilfe meist **automatisch** ("antragslose Familienbeihilfe").
* **Höhe:** Gestaffelt nach Alter des Kindes (ca. 132 € bis 191 € pro Monat) plus Geschwisterstaffelung und Schulstartgeld im September.

#### Kinderbetreuungsgeld (KBG)
Eltern können zwischen zwei Varianten wählen:
1. **Pauschales Kinderbetreuungsgeld-Konto**: Flexibel wählbare Bezugsdauer (12 bis 28 Monate bei Inanspruchnahme durch beide Elternteile, Tagesbetrag ca. 16,87 € bis 39,30 €).
2. **Einkommensabhängiges Kinderbetreuungsgeld**: 80 % des Letzteinkommens (max. 66 € pro Tag) für bis zu 14 Monate (bei Beteiligung beider Eltern).

#### Eheschließung & Standesamt
* **Aufgebot (Ermittlung der Ehefähigkeit):** Voranmeldung beim Standesamt frühestens 6 Monate vor dem gewünschten Trauungstermin.
* **Erforderliche Unterlagen:** Amtlicher Lichtbildausweis, Geburtsurkunde, Staatsbürgerschaftsnachweis, ggf. Nachweis über Vorehen (Scheidungsbeschluss mit Rechtskraft).

[NEXT STEPS]
- Welche Variante des Kinderbetreuungsgeldes ist für meine Situation finanziell günstiger?
- Wie melde ich die Geburt meines Kindes digital über "Digitales Amt" an?
- Welche Fristen gelten für den Partnerschaftsbonus beim Kinderbetreuungsgeld?
- Welche Unterlagen werden für eine Eheschließung mit einer ausländischen Staatsbürgerschaft benötigt?
MD;
        } else {
            $content = <<<'MD'
### Family Benefits, Childcare Allowance & Marriage in Austria

The Republic of Austria provides extensive statutory financial support for families, new parents, and children.

#### Family Allowance (*Familienbeihilfe*)
* **Eligibility:** Permanent residence of parent and child in Austria.
* **Automatic Approval:** For children born in Austria, family allowance is processed **automatically** (*antragslose Familienbeihilfe*) without manual application forms.
* **Amount:** Age-tiered (approx. €132 to €191 per month per child), plus sibling supplements and annual school start allowances.

#### Childcare Allowance (*Kinderbetreuungsgeld - KBG*)
Parents choose between two distinct statutory models:
1. **Flat-Rate Childcare Account (*KBG-Konto*)**: Flexible duration from 12 to 28 months (if shared by both parents), paying between €16.87 and €39.30 daily.
2. **Income-Dependent Model (*Einkommensabhängiges KBG*)**: Replaces 80% of recent net earnings (up to approx. €66/day) for up to 14 months.

#### Civil Marriage Formalities (*Standesamt*)
* **Marriage Registration (*Aufgebot*)**: Book appointment at the registry office within 6 months of your chosen wedding date.
* **Required Records**: Photo ID, certified birth certificates, proof of nationality, and proof of dissolution of any previous marriages (divorce decrees).

[NEXT STEPS]
- Which childcare allowance model provides higher financial support for my income?
- How do I register a newborn baby digitally via the "Digitales Amt" app?
- What are the eligibility rules for the parental partnership bonus?
- What documents are needed for civil marriage with a foreign citizen in Austria?
MD;
        }

        return [
            'content' => $content,
            'sources' => $sources,
            'topic' => 'family',
            'checklist' => $isGerman ? [
                'Geburtsurkunde und Staatsbürgerschaftsnachweis für Neugeborenes beantragen',
                'Antrag auf Kinderbetreuungsgeld bei der ÖGK einreichen',
                'Mutter-Kind-Pass (Eltern-Kind-Pass) Untersuchungen fristgerecht absolvieren',
                'Termin für Ermittlung der Ehefähigkeit am Standesamt reservieren',
            ] : [
                'Obtain birth certificate and citizenship proof for newborn',
                'Submit Childcare Allowance (KBG) application to health insurance fund',
                'Complete mandatory Parent-Child booklet examinations on schedule',
                'Schedule marriage registration appointment at civil registry office',
            ],
        ];
    }

    /**
     * @param  array<int, array<string, string>>  $sources
     * @return array{content: string, sources: array<int, array<string, string>>, checklist: array<int, string>, topic: string}
     */
    protected function buildEmploymentFallback(bool $isGerman, array $sources): array
    {
        if ($isGerman) {
            $content = <<<'MD'
### Arbeit, Kündigung & Arbeitslosengeld (AMS & AlVG)

Das **Arbeitsmarktservice (AMS)** unterstützt bei Arbeitsplatzverlust, Jobsuche und beruflicher Aus- und Weiterbildung.

#### Arbeitslosengeld: Voraussetzungen
1. **Arbeitslosmeldung:** Unverzüglich (spätestens am 1. Tag der Arbeitslosigkeit) beim AMS melden (online via eJob-Room oder persönlich).
2. **Anwartschaft erfüllen:** Innerhalb der letzten 24 Monate vor Antragstellung mindestens **52 Wochen** arbeitslosenversicherungspflichtig beschäftigt gewesen sein (für unter 25-Jährige: 26 Wochen binnen 12 Monaten).
3. **Arbeitswillig & arbeitsfähig:** Verfügbarkeit für den Arbeitsmarkt (mind. 20 bzw. 16 Wochenstunden bei Betreuungspflichten).

#### Höhe & Bezugsdauer
* **Grundbetrag:** ca. **55 % des täglichen Nettoeinkommens**, ergänzt um Familienzuschläge für unterhaltsberechtigte Kinder.
* **Dauer:** Mindestens **20 Wochen**; bei längeren Versicherungszeiten und höherem Lebensalter verlängerbar auf 30, 39 oder 52 Wochen.
* **Notstandshilfe:** Anschlussleistung nach Auslaufen des Arbeitslosengeldes bei Vorliegen einer Notlage.

#### Geringfügige Beschäftigung
* Während des Bezugs von Arbeitslosengeld ist ein Zuverdienst bis zur **Geringfügigkeitsgrenze** (ca. 518 €/Monat) ohne Kürzung des Arbeitslosengeldes erlaubt, muss dem AMS aber vorab gemeldet werden!

[NEXT STEPS]
- Wie melde ich mich online über das eAMS-Konto arbeitslos?
- Wie berechne ich die voraussichtliche Höhe meines Arbeitslosengeldes?
- Welche Fristen gelten für die Kündigungsschutzklage bei der Arbeiterkammer?
- Welche Aus- und Weiterbildungsbeihilfen (z. B. Fachkräftestipendium) bietet das AMS?
MD;
        } else {
            $content = <<<'MD'
### Employment, Termination & Unemployment Benefits in Austria (AMS & AlVG)

The **Public Employment Service Austria (AMS)** administers statutory unemployment compensation, job placement, and career retraining subsidies.

#### Unemployment Benefits (*Arbeitslosengeld*) Requirements
1. **Timely Registration**: Register with the AMS immediately (no later than your first day of unemployment) online via the eJob-Room portal or in person.
2. **Qualifying Period**: You must have worked in employment subject to unemployment insurance for at least **52 weeks within the last 24 months** (or 26 weeks within 12 months if under 25).
3. **Availability for Work**: Capable and willing to work at least 20 hours per week (or 16 hours if caring for minor children).

#### Benefit Amount & Duration
* **Base Amount:** Approx. **55% of your daily net income**, plus statutory family supplements for dependent children.
* **Duration:** Typically **20 weeks**, extendable to 30, 39, or 52 weeks depending on age and total contribution history.
* **Emergency Assistance (*Notstandshilfe*)**: Means-tested continuation benefit once standard unemployment benefits expire.

#### Marginal Earnings Rule
* While receiving unemployment benefits, you may earn income from marginal employment (*Geringfügigkeitsgrenze*, approx. **€518/month**) without reducing your benefit, but you must report it to the AMS before starting work.

[NEXT STEPS]
- How do I register as unemployed online via my eAMS account?
- How is the exact amount of unemployment benefits calculated?
- What are the legal notice periods and employee protections under Austrian labor law?
- What job qualification and training stipends are offered by the AMS?
MD;
        }

        return [
            'content' => $content,
            'sources' => $sources,
            'topic' => 'employment',
            'checklist' => $isGerman ? [
                'Arbeitslosmeldung am 1. Tag der Arbeitslosigkeit beim AMS einreichen',
                'Letzte Lohnabrechnungen und Kündigungsschreiben bereitlegen',
                'eAMS-Konto mit ID Austria aktivieren',
                'Eventuelle geringfügige Nebenbeschäftigung vorab dem AMS melden',
            ] : [
                'Submit unemployment claim to AMS on first day without work',
                'Gather recent payslips and formal notice of termination',
                'Activate eAMS online account using ID Austria',
                'Report any marginal employment to the AMS before commencing work',
            ],
        ];
    }

    /**
     * @param  array<int, array<string, string>>  $sources
     * @return array{content: string, sources: array<int, array<string, string>>, checklist: array<int, string>, topic: string}
     */
    protected function buildCitizenshipFallback(bool $isGerman, array $sources): array
    {
        if ($isGerman) {
            $content = <<<'MD'
### Österreichische Staatsbürgerschaft & Einbürgerung (StbG 1985)

Die Verleihung der **österreichischen Staatsbürgerschaft** erfolgt durch die jeweilige Landesregierung (z. B. MA 35 in Wien oder Amt der Landesregierung).

#### Allgemeine Einbürgerungsvoraussetzungen
1. **Rechtmäßiger & ununterbrochener Aufenthalt**: In der Regel mindestens **10 Jahre** legaler Aufenthalt in Österreich (Verkürzung auf **6 Jahre** möglich bei Deutsch B2, nachhaltiger Integration oder ehrenamtlichem Engagement).
2. **Finanzielle Selbsterhaltungsfähigkeit**: Nachweis fester und regelmäßiger eigener Einkünfte (in der Regel mind. 36 Monate innerhalb der letzten 6 Jahre).
3. **Deutschkenntnisse & Integrationsprüfung**: Nachweis von Deutschkenntnissen auf Niveau B1 (bzw. B2 für 6-Jahres-Frist) und Bestehen der Staatsbürgerschaftsprüfung (Grundwissen über die demokratische Ordnung, Geschichte und Landeskunde).
4. **Unbescholtenheit**: Keine schwerwiegenden Vorstrafen, kein anhängiges Strafverfahren, keine schwerwiegenden Finanzvergehen.
5. **Ausscheiden aus dem bisherigen Staatsverband**: Österreichisches Recht verlangt grundsätzlich die Aufgabe der bisherigen Staatsbürgerschaft (Verbot der doppelten Staatsbürgerschaft, bis auf wenige gesetzliche Ausnahmen).

#### Benötigte Dokumente
- [ ] **Geburtsurkunde & Reisepass**: Mit Apostille bzw. diplomatischer Beglaubigung und beeidigter Übersetzung.
- [ ] **Einkommensnachweise**: Lohnzettel, Steuerbescheide und Versicherungsdatenauszug der letzten 3 Jahre.
- [ ] **Deutsch- und Staatsbürgerschaftsprüfungs-Zeugnisse**: ÖIF-Zertifikate.
- [ ] **Wohnungsnachweis & Meldezettel**: Mietvertrag und aktueller Meldenachweis.

[NEXT STEPS]
- Erfülle ich die Kriterien für eine Einbürgerung nach 6 Jahren?
- Wo finde ich die Übungsfragen für die offizielle österreichische Staatsbürgerschaftsprüfung?
- Welche Ausnahmen gibt es vom Verbot der doppelten Staatsbürgerschaft?
- Welche Gebühren fallen bei der Verleihung der Staatsbürgerschaft an?
MD;
        } else {
            $content = <<<'MD'
### Austrian Citizenship & Naturalization (*Staatsbürgerschaftsgesetz 1985*)

Austrian citizenship is granted by the respective provincial provincial government (*Landesregierung*, e.g., MA 35 in Vienna).

#### General Naturalization Criteria
1. **Lawful & Unbroken Residence**: Generally at least **10 years** of continuous legal residence in Austria (reduced to **6 years** for applicants with B2 German, sustained professional integration, or major civic engagement).
2. **Financial Self-Sufficiency**: Proof of regular, stable income sufficient for independent living without social assistance for at least 36 months out of the prior 6 years.
3. **Language & Civic Exam**: Certified German proficiency at level B1 (or B2 for the 6-year fast-track) and passing the Austrian Citizenship Exam (democratic system, Austrian history, and provincial civics).
4. **Clean Criminal Record**: No convictions, pending criminal investigations, or serious administrative offenses.
5. **Renunciation of Previous Nationality**: Austrian law strictly restricts dual citizenship; applicants must generally renounce their previous citizenship upon naturalization.

#### Required Documents Checklist
- [ ] **Birth Certificate & Current Passport**: Legalized/apostilled and translated by a certified court translator.
- [ ] **Proof of Income**: Payslips, tax assessment notices, and insurance contribution history for the last 36 months.
- [ ] **Language & Civic Test Certificates**: Certified ÖIF certificates.
- [ ] **Proof of Accommodation**: Lease contract and historical residence registration (*Meldebestätigung*).

[NEXT STEPS]
- Do I qualify for the expedited 6-year naturalization path?
- Where can I access practice questions for the Austrian citizenship test?
- What are the rare statutory exceptions to the dual citizenship prohibition?
- What are the total provincial and federal fees for naturalization?
MD;
        }

        return [
            'content' => $content,
            'sources' => $sources,
            'topic' => 'citizenship',
            'checklist' => $isGerman ? [
                'Aufenthaltszeiten und lückenlose Meldebestätigungen prüfen',
                'Einkommensnachweise der letzten 36 Monate sammeln',
                'Deutschzertifikat (B1 oder B2) und Staatsbürgerschaftsprüfung absolvieren',
                'Antrag bei der zuständigen Landesregierung oder MA 35 einreichen',
            ] : [
                'Verify unbroken legal residence history in Austria',
                'Compile income statements and tax assessments for last 36 months',
                'Obtain certified B1/B2 German and citizenship exam certificates',
                'Submit application to provincial government authority or MA 35',
            ],
        ];
    }

    /**
     * @param  array<int, array<string, string>>  $sources
     * @return array{content: string, sources: array<int, array<string, string>>, checklist: array<int, string>, topic: string}
     */
    protected function buildHumanFallback(bool $isGerman, array $sources): array
    {
        if ($isGerman) {
            $content = <<<'MD'
### Digitaler Bürgerservice Republik Österreich (Bundesservice 2030)

Ich helfe Ihnen gerne bei allen behördlichen Fragen in Österreich. Zu unseren umfassenden Themenbereichen gehören:

* **Reisepass & Ausweise:** Neuausstellung, Passgebühren (€ 75,90), Express-Optionen, Verlust- und Diebstahlsmeldungen.
* **Wohnsitz & Meldewesen:** Gesetzliche 3-Tage-Frist, Meldezettel, Vermieter-Unterschrift, Wohnungswechsel.
* **Aufenthalt & Einwanderung:** Aufenthaltstitel Student (§ 64 NAG), Rot-Weiß-Rot – Karte, Visa für Drittstaaten.
* **Steuern & Finanzen:** Arbeitnehmerveranlagung (Lohnsteuerausgleich via FinanzOnline), Familienbonus Plus, Pendlerpauschale.
* **Gesundheit & e-card:** e-card mit Foto Pflicht, ÖGK Selbstversicherung für Studierende (~69 €/Monat), Rezeptgebührenbefreiung.
* **Führerschein & KFZ:** Führerschein-Umschreibung ausländischer Lizenzen, KFZ-Zulassung, Pickerl (§ 57a KFG), ASFINAG Digitale Vignette.
* **Familie & Kinder:** Familienbeihilfe, Kinderbetreuungsgeld (Konto vs. Einkommensabhängig), Standesamt.
* **Arbeit & AMS:** Arbeitslosengeld, Zuverdienstgrenzen, Kündigung, Rechte bei der Arbeiterkammer.
* **Staatsbürgerschaft:** Einbürgerungsvoraussetzungen, Fristen, Sprach- und Integrationsprüfungen.
* **ID Austria:** Einrichtung der Vollfunktion, mobile Ausweise im "Digitalen Amt".

Wählen Sie oben einen Schnell-Assistenten oder tippen Sie Ihre Frage in Alltagssprache ein. Für eine persönliche Beratung durch Beamtinnen und Beamte:
* **Telefon:** [+43 1 53115-0](tel:+431531150) (Mo–Fr 08:00–16:00 Uhr)
* **E-Mail:** [buergerservice@bka.gv.at](mailto:buergerservice@bka.gv.at)
MD;
        } else {
            $content = <<<'MD'
### Republic of Austria - Digital Citizen Service 2030

I am here to guide you through Austrian public administration in plain language. Our knowledge engine covers all primary public domains:

* **Passports & IDs:** New passports, fees (€75.90), express options, loss & theft reports.
* **Residence Registration:** 3-day statutory deadline, Meldezettel forms, landlord signatures, address changes.
* **Visas & Residence Permits:** Student Residence Permit (§ 64 NAG), Red-White-Red Card, third-country immigration.
* **Taxes & FinanzOnline:** Employee Tax Assessment (Lohnsteuerausgleich), Family Bonus Plus, commuter allowance.
* **Healthcare & e-card:** Mandatory photo on e-card, ÖGK student self-insurance (~€69/month), prescription fee caps.
* **Driving & Vehicles:** Foreign license conversions, car registration (*Zulassung*), vehicle inspection (*Pickerl*), ASFINAG digital vignette.
* **Family & Children:** Family allowance (*Familienbeihilfe*), Childcare allowance (*Kinderbetreuungsgeld*), civil marriage.
* **Employment & AMS:** Unemployment benefits, marginal earnings rules, severance funds, worker protections.
* **Citizenship:** Naturalization rules (6–10 years), civic and German language exam requirements.
* **ID Austria:** Full digital identity setup, mobile credentials in the "Digitales Amt" app.

Type your question naturally or select a popular service above. For personalized human assistance from Austrian authorities:
* **Phone:** [+43 1 53115-0](tel:+431531150) (Mon–Fri 08:00–16:00)
* **Email:** [buergerservice@bka.gv.at](mailto:buergerservice@bka.gv.at)
MD;
        }

        return [
            'content' => $content,
            'sources' => $sources,
            'topic' => 'human',
            'checklist' => $isGerman ? [
                'Bürgerservice Hotline des Bundeskanzleramts: +43 1 53115-0',
                'E-Mail an buergerservice@bka.gv.at senden',
                'Termin beim örtlichen Bezirksamt oder Gemeindeamt vereinbaren',
            ] : [
                'Call Federal Chancellery Helpdesk: +43 1 53115-0',
                'Send email to buergerservice@bka.gv.at',
                'Schedule appointment at your municipal district authority',
            ],
        ];
    }
}
