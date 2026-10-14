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

GRUNDSÄTZE:
1. EINFACHE SPRACHE: Übersetze Amtsdeutsch in klare, leicht verständliche Alltagssprache ohne Amtskauderwelsch.
2. STRUKTUR: Antworte stets übersichtlich mit kurzen Absätzen, Zwischenüberschriften und Aufzählungszeichen (z. B. "Benötigte Dokumente", "Fristen & Gebühren", "Wo beantragen", "Nächste Schritte").
3. VERLÄSSLICHKEIT & ÖSTERREICH-RECHT:
   - Alle Angaben basieren strikt auf österreichischem Bundesrecht (z. B. Passgesetz 1992, Meldegesetz 1972).
   - Erfinde niemals Gesetze, Fristen oder Gebühren.
   - Gebühren Reisepass (Erwachsene): 75,90 € Standard (ca. 5 Werktage), 100 € Express (2-3 Werktage), 220 € Ein-Tages-Expresspass. Kinder bis 2 Jahre: kostenlos (Erstausstellung) bzw. 30 €, Kinder 2-12 Jahre: 30 €.
   - Wohnsitz-Meldepflicht: Gesetzliche Frist von 3 Tagen ab tatsächlichem Bezug (§ 1 Abs. 1 Meldegesetz). Gebührenfrei! Vermieter-Unterschrift ("Bestätigung des Unterkunftgebers") bei Miete erforderlich.
   - Wechsle zwischen digitaler Abwicklung über "ID Austria" / "Digitales Amt" und persönlicher Vorsprache (Bezirksamt, BH, Gemeindeamt).
4. KLARSTELLUNG: Unterscheide eindeutig zwischen rechtlich bindenden Voraussetzungen und unverbindlichen Empfehlungen. Weise darauf hin, dass persönliche Termine (z. B. Fingerabdrücke beim Reisepass) gesetzlich verpflichtend sind.
5. MENSCHLICHE UNTERSTÜTZUNG: Wenn ein Fall unklar ist, rechtliche Sonderfälle vorliegen oder der Bürger einen Menschen verlangt, biete sofort die Bürgerservice-Hotline des Bundeskanzleramts (+43 1 53115-0) oder das zuständige Gemeindeamt an und biete an, ein "Behörden-Dossier" zusammenzufassen.
6. SPRACHE: Antworte auf Deutsch in höflicher "Sie"-Form, es sei denn, der Nutzer wünscht das "Du" oder schreibt auf Englisch.
7. KARTEN-STRUKTUR FÜR PROZESSE & CHECKLISTEN:
   Wenn du einen Prozess, Ablauf oder benötigte Unterlagen erklärst:
   - Beginne immer mit einem einleitenden Absatz (1-2 kurze Sätze Erklärung).
   - Formatiere Prozessschritte immer als nummerierte Schritte:
     1. **[Schritt-Titel]**: [Verständliche Erklärung des Schritts]
   - Formatiere Unterlagen oder Dokumente immer als Checkliste:
     - [ ] **[Dokument-Name]**: [Vorgaben und Details]
   - Fasse am Ende Gebühren, Fristen und nächste Schritte prägnant zusammen.
8. NÄCHSTE SCHRITTE & FOLGEFRAGEN (BUTTONS):
   Schließe deine Antwort am Ende immer mit 2 bis 4 konkreten Folgefragen oder Handlungsschritten für die Bürgerin / den Bürger ab:
   [NÄCHSTE SCHRITTE]
   - [Konkrete Frage oder Handlung, z. B.: Haben Sie bereits einen gültigen Reisepass?]
   - [Folgefrage, z. B.: Benötigen Sie Informationen zur Krankenversicherung (ÖGK)?]
   - [Folgefrage, z. B.: Wo finde ich das Meldezettel-Formular?]
PROMPT;
        }

        return <<<'PROMPT'
You are the official AI Citizen Assistant of the Republic of Austria ("Digital Austria 2030 / Bundesservice").
Your role is to guide citizens, residents, and visitors through Austrian public administration naturally, securely, and in plain, accessible language.

PRINCIPLES:
1. PLAIN LANGUAGE: Translate complex Austrian administrative terminology into plain, accessible English without bureaucratic jargon.
2. STRUCTURE: Always structure your response clearly using short paragraphs, headings, and bulleted lists ("Required Documents", "Fees & Deadlines", "Where to Apply", "Next Steps").
3. RELIABILITY & AUSTRIAN LAW:
   - Ground all guidance strictly in Austrian federal law (Passport Act 1992, Residence Registration Act 1972).
   - Never fabricate fees, deadlines, or requirements.
   - Passport fees (Adults 18+): €75.90 standard (approx. 5 working days), €100 express (2-3 working days), €220 one-day express. Children under 2: free (first issue) or €30, children 2-12: €30.
   - Residence registration: Statutory deadline of 3 days from moving in. Registration is free of charge! Landlord signature required on the "Meldezettel" if renting.
   - Explain both digital pathways via "ID Austria" / "Digitales Amt" app and in-person options at municipal offices (Magistratisches Bezirksamt in Vienna, Bezirkshauptmannschaft or Gemeindeamt).
4. HUMAN ASSISTANCE: Whenever an inquiry is ambiguous or the citizen asks for a human, provide official contact channels (Federal Chancellery Citizen Service +43 1 53115-0) and offer to generate a concise summary dossier for the case worker.
5. CARD-FRIENDLY STRUCTURE FOR PROCESSES & CHECKLISTS:
   Whenever explaining a procedure, workflow, or required documents:
   - Begin with a concise introductory paragraph (1-2 sentences).
   - Format action steps as a numbered sequence:
     1. **[Step Title]**: [Clear explanation of this step]
   - Format document requirements as a checklist:
     - [ ] **[Document Name]**: [Specifications and details]
   - Conclude with concise summary of fees, deadlines, and authority location.
6. RECOMMENDED NEXT STEPS (ACTION BUTTONS):
   Conclude every response with 2 to 4 actionable follow-up questions or next steps for the citizen:
   [NEXT STEPS]
   - [Actionable question, e.g.: Do you already have a valid passport?]
   - [Follow-up question, e.g.: Do you need information for how to apply for health insurance (ÖGK)?]
   - [Follow-up question, e.g.: How do I get landlord confirmation?]
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
        $q = mb_strtolower($query);
        $isGerman = str_starts_with(strtolower($locale), 'de');

        if (str_contains($q, 'pass') || str_contains($q, 'ausweis') || str_contains($q, 'passport') || str_contains($q, 'verlänger')) {
            $topic = 'passport';
            $sources = $this->getOfficialSources('passport', $locale);

            if ($isGerman) {
                $content = <<<'MD'
### Reisepass-Service der Republik Österreich

Ein Reisepass kann in Österreich **nicht verlängert** werden – es wird stets ein **neuer Reisepass ausgestellt**.

#### Ablauf der Neuausstellung
1. **Dokumente vorbereiten**: Bisherigen Reisepass (darf bis zu 5 Jahre abgelaufen sein) und aktuelles biometrisches Passfoto bereitlegen.
2. **Termin online buchen**: Reservieren Sie einen Termin beim Magistratischen Bezirksamt oder der Bezirkshauptmannschaft.
3. **Persönlich vorsprechen**: Erfassung der biometrischen Fingerabdrücke und Unterschrift am Schalter.
4. **Zustellung abwarten**: Ihr neuer Reisepass wird per behördlichem RSb-Brief innerhalb von ca. 5 Werktagen direkt nach Hause zugestellt.

#### Benötigte Unterlagen
- [ ] **Bisheriger Reisepass**: Darf bis zu 5 Jahre abgelaufen sein (falls länger: Geburtsurkunde & Staatsbürgerschaftsnachweis mitbringen).
- [ ] **1 Aktuelles Passfoto**: 35 x 45 mm, Hochformat, max. 6 Monate alt, neutraler Hintergrund nach EU-Passbildkriterien.
- [ ] **Gebühr**: 75,90 € Standard (100,00 € Express, 220,00 € Ein-Tages-Express) per Bankomat oder bar.
- [ ] **Unterkunftgeber**: Für den Reisepass nicht erforderlich (nur beim Wohnsitz).

#### Fristen & Gebühren
* **Standard-Reisepass (ab 18 Jahren):** **75,90 €** (Gültigkeitsdauer: 10 Jahre, Zustellung in ca. 5 Arbeitstagen).
* **Expresspass:** **100,00 €** (Zustellung in 2–3 Werktagen).
* **Ein-Tages-Expresspass:** **220,00 €** (Zustellung am nächsten Werktag per Boten).
* **Kinder (2 bis 12 Jahre):** **30,00 €** (Gültigkeit: 5 Jahre).
* **Kinder bis 2 Jahre:** **Gebührenfrei** bei Erstausstellung (Gültigkeit: 2 Jahre).
MD;
            } else {
                $content = <<<'MD'
### Austrian Passport Issuance & Renewal (Official Guidance)

Passports cannot be "extended" in Austria; an application is always for the **issuance of a new passport**.

#### Step-by-Step Procedure
1. **Prepare Required Documents**: Gather current or expired passport and 1 recent biometric photo (35x45 mm).
2. **Book Appointment Online**: Schedule an appointment at any *Magistratisches Bezirksamt* or *Bezirkshauptmannschaft*.
3. **Appear in Person**: Fingerprints and signature must be submitted in person by law.
4. **Await Registered Delivery**: Your passport is delivered to your home via registered mail within approx. 5 working days.

#### Required Documents Checklist
- [ ] **Current or Expired Passport**: Valid or expired within the past 5 years.
- [ ] **1 Recent Passport Photograph**: 35 x 45 mm, biometric, neutral background, max 6 months old.
- [ ] **Official Fee**: €75.90 standard adult fee (or €100.00 express) via debit card or cash.

#### Fees & Processing Times
* **Standard Adult Passport (18+):** **€75.90** (Valid for 10 years, delivery in approx. 5 working days).
* **Express Passport:** **€100.00** (Delivery in 2–3 working days).
* **One-Day Express:** **€220.00** (Delivery next working day).
* **Children (ages 2–12):** **€30.00** (Valid for 5 years).
* **Infants (up to 2 years):** **Free of charge** for first issuance (Valid for 2 years).
MD;
            }

            return [
                'content' => $content,
                'sources' => $sources,
                'topic' => $topic,
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

        if (str_contains($q, 'student') || str_contains($q, 'residence permit') || str_contains($q, 'aufenthalt') || str_contains($q, 'permit') || str_contains($q, 'visum') || str_contains($q, 'visa') || str_contains($q, 'nag') || str_contains($q, 'rot-weiß-rot')) {
            $topic = 'permit';
            $sources = $this->getOfficialSources('permit', $locale);

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
                'topic' => $topic,
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

        if (str_contains($q, 'umzug') || str_contains($q, 'umgezogen') || str_contains($q, 'umzieh') || str_contains($q, 'wohn') || str_contains($q, 'meld') || str_contains($q, 'resid') || str_contains($q, 'mov') || str_contains($q, 'adr')) {
            $topic = 'residence';
            $sources = $this->getOfficialSources('residence', $locale);

            if ($isGerman) {
                $content = <<<'MD'
### Wohnsitz An- und Ummeldung in Österreich (Geprüfte Information)

In Österreich gilt nach dem Meldegesetz 1972 eine gesetzliche Meldepflicht. Die Meldung ist **vollständig gebührenfrei**.

#### Ablauf der Ummeldung
1. **Meldezettel ausfüllen**: Bundes-Meldezettel vollständig ausfüllen (Angaben zu Haupt- oder Nebenwohnsitz).
2. **Vermieter-Unterschrift einholen**: Bei Miete zwingend die Unterschrift der Unterkunftgeberin / des Unterkunftgebers einholen.
3. **Meldung einreichen**: Digital per ID Austria in der App "Digitales Amt" oder persönlich am Gemeindeamt / Wiener Bezirksamt einreichen.
4. **Folgepflichten erledigen**: KFZ binnen 1 Woche ummelden, Adressänderung bei OBS (ehemals GIS) und Arbeitgeber bekanntgeben.

#### Benötigte Dokumente
- [ ] **Meldezettel-Formular**: Vollständig ausgefüllt mit allen Pflichtangaben.
- [ ] **Bestätigung des Unterkunftgebers**: Vermieter-Unterschrift bei Mietwohnungen (als Eigentümer unterschreiben Sie selbst).
- [ ] **Amtlicher Lichtbildausweis**: Reisepass oder Personalausweis für alle mitziehenden Personen.
- [ ] **Gebühr**: 0,00 € (Gesetzlich gebührenfrei).

#### Wichtige Fristen & Regeln
* **Gesetzliche Frist:** Innerhalb von **3 Werktagen** ab tatsächlichem Einzug (§ 1 Abs. 1 MeldeG).
* **Kosten:** Die Wohnsitzanmeldung ist **vollständig gebührenfrei**.
* **Automatische Abmeldung:** Bei Anmeldung des neuen Wohnsitzes wird der alte Wohnsitz automatisch abgemeldet!
MD;
            } else {
                $content = <<<'MD'
### Residence Registration in Austria (Official Guidance)

Under the Austrian Residence Registration Act (*Meldegesetz 1972*), registration is mandatory for all residents. Registration is **free of charge**.

#### Registration Procedure
1. **Complete Meldezettel Form**: Fill in the official registration application form.
2. **Obtain Landlord Signature**: If renting, the property owner / landlord must sign the form.
3. **Submit Registration**: 100% digitally with ID Austria via the "Digitales Amt" app, or in-person at city hall.
4. **Complete Follow-up Steps**: Update vehicle registration within 1 week, notify broadcast fee (OBS).

#### Required Documents Checklist
- [ ] **Meldezettel Form**: Signed by applicant and accommodation provider.
- [ ] **Official Photo ID**: Passport or European National ID card.
- [ ] **Registration Fee**: €0.00 (Completely free by law).

#### Deadlines & Key Rules
* **Deadline:** You must register within **3 business days** of moving into your new accommodation.
* **Simultaneous Deregistration:** When registering your new address, your previous Austrian residence is automatically deregistered.
MD;
            }

            return [
                'content' => $content,
                'sources' => $sources,
                'topic' => $topic,
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

        // General / Human inquiry fallback
        $topic = 'human';
        $sources = $this->getOfficialSources('human', $locale);

        if ($isGerman) {
            $content = <<<'MD'
### Digitaler Bürgerservice Republik Österreich

Ich helfe Ihnen gerne bei allen behördlichen Fragen in Österreich. Zu den häufigsten Anliegen gehören:

* **Reisepass & Personalausweis:** Neuausstellung, Gebühren (€ 75,90), Express-Optionen, Verlustmeldungen.
* **Wohnsitz An- & Ummeldung:** Gesetzliche 3-Tage-Frist, Meldezettel, Vermieter-Unterschrift, Online-Meldung.
* **ID Austria:** Einrichtung, Vollfunktion, Behördenwege auf dem Smartphone.
* **Menschliche Beratung:** Offizielle Kontaktdaten zu Bürgerbüros und Bundesministerien.

Sie können direkt oben eine Schnellaktion wählen oder Ihre Frage in eigenen Worten formulieren. Wenn Sie eine persönliche Fallberatung durch Beamtinnen und Beamte benötigen, nutzen Sie bitte das Bürgerservice des Bundeskanzleramts:
* **Telefon:** [+43 1 53115-0](tel:+431531150) (Mo–Fr 08:00–16:00 Uhr)
* **E-Mail:** [buergerservice@bka.gv.at](mailto:buergerservice@bka.gv.at)
MD;
        } else {
            $content = <<<'MD'
### Republic of Austria - Digital Citizen Service 2030

I am here to assist you with Austrian public services in plain language. Key topics include:

* **Passports & ID Cards:** Applications, renewals, fees (€75.90), express options, loss reporting.
* **Change of Residence:** 3-day deadline, Meldezettel forms, landlord signatures, online registration.
* **ID Austria:** Setup, full digital identity, mobile services.
* **Human Support:** Direct contact channels with Austrian civil service offices.

Select one of the shortcuts above or type your question naturally. For personalized human assistance from Austrian authorities:
* **Phone:** [+43 1 53115-0](tel:+431531150) (Mon–Fri 08:00–16:00)
* **Email:** [buergerservice@bka.gv.at](mailto:buergerservice@bka.gv.at)
MD;
        }

        return [
            'content' => $content,
            'sources' => $sources,
            'topic' => $topic,
        ];
    }
}
