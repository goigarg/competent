# Kompetent · Digitaler Bürgerassistent 2030
## Pitch Deck & Presentation Guide

> **Challenge**: Digitale Assistenten: Evolution bis 2030 (Öffentlicher Sektor)  
> **Team**: Goi Garg, Cajetan Perwein, Jeet Gupta, Reyan Zein  
> **Live Prototype**: [https://competent.shiv.ovh](https://competent.shiv.ovh)  
> **Staff Handover Portal**: [https://competent.shiv.ovh/portal](https://competent.shiv.ovh/portal)  
> **Target Pitch Duration**: 3 – 5 Minutes

---

### Slide 1: Title & Hook
- **Slide Title**: Kompetent · Digitaler Bürgerassistent 2030
- **Subtitle**: Vom bürokratischen Labyrinth zur empathischen Bürgerbegleitung
- **Visuals**: Austrian Flag Crest (`kp-flag`), Live URL badge (`competent.shiv.ovh`), Clean governmental aesthetic.
- **Key Talking Points**:
  - "Austria provides exceptional administrative services, but navigating them in 2026 still feels like an obstacle course of fragmented portals, complex statutes, and PDF forms."
  - "Today, we present **Kompetent**: Austria’s next-generation universal civic assistant for 2030 — conversational, 100% grounded in Austrian law, accessible to every resident, and backed by a seamless human caseworker handover."

---

### Slide 2: The Problem (Status Quo 2026)
- **Slide Title**: Die Bürgerschnittstelle im Jahr 2026
- **Key Pain Points**:
  1. **Fragmented Portals**: Citizens jump between `oesterreich.gv.at`, `bmi.gv.at`, `migration.gv.at`, and `bmf.gv.at`.
  2. **The "Legalese" Barrier**: Jargon like *NAG*, *Unterkunftgeber*, *AuslBG*, and *Arbeitnehmerveranlagung* alienates residents.
  3. **High Hallucination Risk in Generic AI**: Standard ChatGPT models invent deadlines, make up fees, or cite outdated legislation.
  4. **Overwhelmed Municipal Desks**: Civil servants spend up to 70% of their workday answering repetitive basic questions.
- **Speaker Note**: "Citizens don’t want to read statutes; they want to know: *Where do I go? By when? What documents must I bring?*"

---

### Slide 3: The Vision (Digitales Österreich 2030)
- **Slide Title**: Unsere Vision: Der digitale Amtshelfer für alle
- **Core Pillars**:
  - **Conversational & Proactive**: Understands unstructured, colloquial speech and multiple concerns at once.
  - **Zero Hallucination & Legally Grounded**: Every claim cites verified Austrian government sources (`.gv.at`, `ams.at`).
  - **Inclusive by Design**: Accessible to all age groups, language backgrounds, and literacy levels.
  - **Human-in-the-Loop**: Seamless collaboration between AI and public servants.

---

### Slide 4: Personas & Inclusive Design
- **Slide Title**: Bürger-Personas im Mittelpunkt
- **The Three Personas**:
  - **Erika (68, Linz)**: Needs to renew her passport before vacation. Relies on **High Contrast Mode & 200% Text Zoom**. Gets instant urgency pricing (€75.90 vs €100 express) and a printable document checklist.
  - **Tariq (29, Software Engineer, Cairo &rarr; Vienna)**: Relocating for work. Interacts in English; the assistant translates administrative concepts into plain language while anchoring official German terms in brackets (e.g. *Arbeitgebererklärung*).
  - **Elena (34, Mother of two, Graz)**: Relocating from abroad with multiple questions. The assistant detects both her residence registration and family allowance concerns, warning her of the mandatory 3-day deadline (§ 1 MeldeG).

---

### Slide 5: Innovation 1 — The Two-Phase CI Answer Pattern
- **Slide Title**: Höflich, präzise, fehlertolerant: Der Zwei-Phasen-Ablauf
- **How It Works**:
  1. **Phase 1: Clarification ([CLARIFY])**:
     - The assistant never rushes to assume. It summarizes: *"Habe ich Ihr Anliegen richtig verstanden?"*
     - Citizen clicks **"Ja, genau"** or **"Nicht ganz"** chips.
  2. **Phase 2: Structured Plain-Language Answer ([ANSWER])**:
     - Sequential steps answering: **Where?**, **Deadline?**, **Documents?**
     - Verified numbered citations `[1][2]` linked directly to government portals.
     - Interactive next-step action buttons.
  3. **Integrated Privacy Firewall**:
     - Automatically intercepts and redacts 10-digit Austrian Social Insurance Numbers (*SVNr*) with a privacy notice.

---

### Slide 6: Innovation 2 — Universal Scale & Dynamic Form Resolution
- **Slide Title**: Universalität & Das Formular-Link-Problem gelöst
- **Universal Breadth**:
  - Covers **hundreds of services** across: *Passports, Residence, Immigration & Work, Taxes, Family Benefits, Health, Vehicle & Driving*.
- **The Dynamic Form Index**:
  - *The problem*: Government PDF links constantly change and break (link rot).
  - *Our solution*: A hybrid Two-Tier Form Resolver. It detects form intent (*"Where can I download the form?"*) and dynamically resolves live, verified BMI/AMS PDF downloads (e.g. *Antrag auf Erteilung eines Aufenthaltstitels*, *Arbeitgebererklärung*, *Meldezettel*) with instant fallback to official directories.

---

### Slide 7: Innovation 3 — 100% Inclusivity & Accessibility
- **Slide Title**: Barrierefreiheit nach WCAG 2.2 AA
- **Key Capabilities**:
  - **8 Language Modes**: German, English, Arabic (with native RTL mirroring), Turkish, Ukrainian, Romanian, Bosnian/Croatian/Serbian, and **Leichte Sprache** (*Simple German*).
  - **Visual Accessibility**: Text scale toggle (`normal`, `large`, `xlarge`), high-contrast color scheme, 44×44px touch targets.
  - **Assistive Tech**: Screen-reader live regions (`aria-live="polite"`), semantic heading hierarchy.

---

### Slide 8: Innovation 4 — Civic Handover & Staff Portal
- **Slide Title**: Die Brücke zum Beamten: Das Sachbearbeiter-Portal
- **How Handover Works**:
  - Citizen clicks **"Mit einer Person sprechen"** at any point.
  - The assistant compiles an automated **Case Dossier** in German summarizing the case facts, saving 5–10 minutes per inquiry.
  - Officers access `/portal`, view the real-time escalation queue, inspect the dossier, and **join the chat live** with instant visual feedback to the citizen.

---

### Slide 9: Technical Architecture & Resilience
- **Slide Title**: Robuste & Souveräne Technologie
- **The Modern Stack**:
  - **Framework**: Laravel 12 on PHP 8.4
  - **Reactivity**: Livewire 3 (instant reactive UI without bloated client-side SPA complexity)
  - **Design System**: Official Kompetent CI Guidelines implemented via Tailwind CSS tokens
  - **Testing**: 152 automated Pest feature and unit tests with a 100% pass rate
  - **Resilience**: Multi-model LLM fallback pipeline + offline knowledge base fallback mode

---

### Slide 10: Value Proposition & ROI by 2030
- **Slide Title**: Der Mehrwert für Österreich bis 2030
- **Impact Metrics**:
  - **70–80% deflection** of repetitive inquiries at municipal counters.
  - **5–10 minutes saved** per complex inquiry through auto-generated Case Dossiers.
  - **Zero legal risk**: Grounded in statutory facts, no hallucinations.
  - **Trust in government**: High citizen satisfaction through 24/7 transparent, verified service.

---

### Slide 11: Live Demo & Q&A
- **Slide Title**: Erleben Sie Kompetent live
- **Links**:
  - **Citizen Chat**: [https://competent.shiv.ovh](https://competent.shiv.ovh)
  - **Caseworker Portal**: [https://competent.shiv.ovh/portal](https://competent.shiv.ovh/portal)
  - **Source Code**: [https://github.com/goigarg/competent](https://github.com/goigarg/competent)
- **Call to Action**: "Vielen Dank! Wir freuen uns auf Ihre Fragen."
