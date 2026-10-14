<?php

/*
 * Kompetent – Messages the assistant writes itself (welcome, guided workflows, human support).
 * Factual content: only reviewed languages have this file; others fall back (see App\Enums\Language::contentChain).
 */

return [
    'dossier_general' => '### Citizen Dossier (Escalated to Civil Servant)

* **Date/Time:** :datetime
* **Status:** Pending civil servant review
* **Language:** English

#### Citizen inquiries in chat:
* :requests',
    'dossier_general_empty' => 'General public service inquiry',
    'welcome' => 'Welcome! I am your official **AI Citizen Assistant for the Republic of Austria**.

I explain Austrian administrative procedures in plain language and guide you through public services step by step. Feel free to choose an option below or ask your question directly:',
    'passport_intro' => '### Interactive Passport Assistant

I will guide you step by step through your Austrian passport application. Please select your current situation:',
    'residence_intro' => '### Interactive Residence Registration Assistant

Under Austrian law, you must register your address within 3 days of moving in. Have you already moved or are you planning to move?',
    'passport_scenario' => [
        'expiring' => 'Passport expiring soon',
        'expired_recently' => 'Passport expired recently (< 5 years)',
        'expired_over_5_years' => 'Passport expired > 5 years ago',
        'lost_stolen' => 'Passport lost or stolen',
        'child' => 'Passport for a child',
    ],
    'passport_scenario_ack' => 'Noted: **:label**.

How quickly do you need your passport? Please choose your delivery speed:',
    'passport_overview' => '### Your Personalized Passport Overview

* **Official Fee:** **:fee**
* **Processing Time:** :duration
* **Validity:** :validity

Here is your required document checklist. Check off items as you prepare them:',
    'residence_planning' => 'I am planning to move',
    'residence_moved' => 'I have already moved (:days days ago)',
    'residence_type_question' => 'Is this new address your **Primary Residence** (*Hauptwohnsitz* - center of vital interests) or a **Secondary Residence** (*Nebenwohnsitz*)?',
    'residence_type_primary' => 'Primary Residence',
    'residence_type_secondary' => 'Secondary Residence',
    'residence_housing_question' => 'Are you living in a **rented flat** or are you the **property owner**?',
    'residence_housing_rented' => 'Rented property',
    'residence_housing_owner' => 'Property owner',
    'residence_hint_rented' => '⚠️ **Key Rule:** As a tenant, your landlord/property manager must sign the *Meldezettel* form.',
    'residence_hint_owner' => 'As property owner, you sign the landlord section yourself.',
    'residence_channel_question' => 'Which registration method do you prefer?',
    'residence_channel_id_austria' => 'Digital via ID Austria',
    'residence_channel_in_person' => 'In person at municipal office',
    'residence_checklist_intro' => '### Checklist for your Residence Registration

* **Cost:** **€0.00** (Completely free by law)
* **Deadline:** Within 3 working days of moving in
* **Previous address:** Automatically deregistered!

Here is your interactive checklist including mandatory follow-ups:',
    'escalation_reason_chat' => 'Citizen requested human caseworker assistance in chat',
    'escalation_reason_default' => 'Citizen requested human caseworker assistance',
    'limited_mode_notice' => '> [!NOTE]
> **Limited mode:** The AI service is currently unavailable. Below is stored standard information. Please confirm it with the authority or talk to a person.',
    'human_support' => '### Official Human Support (Case Escalated to Public Service Desk)

I have connected you with the official human representatives of the Austrian public administration. Your case has been noted in the **Civil Servant Portal** for human review.

* **Federal Chancellery Citizen Service Helpdesk:**
  * 📞 **Phone:** [+43 1 53115-0](tel:+431531150) (Monday to Friday, 08:00 – 16:00)
  * ✉️ **Email:** [buergerservice@bka.gv.at](mailto:buergerservice@bka.gv.at)
* **ID Austria Hotline:** 📞 [+43 1 71100-805555](tel:+43171100805555)
* **Local Registry Offices:** Municipal town hall (*Gemeindeamt*) or any *Magistratisches Bezirksamt* in Vienna.

💡 **Tip:** Click **"Generate Civil Servant Dossier"** below to create a copyable summary of your situation.',
];
