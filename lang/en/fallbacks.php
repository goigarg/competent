<?php

/*
 * Kompetent – checklists of the stored fallback answers (resources/content/{locale}/fallbacks/*.md).

 * Factual content: only reviewed languages have this file; others fall back (App\Enums\Language::contentChain).
 */

return [
    'passport' => [
        'Locate current or expired passport',
        'Obtain 1 biometric photo (35x45 mm)',
        'Book appointment at passport office',
        'Prepare fee (€75.90 via debit card or cash)',
    ],
    'permit' => [
        'Receive official university admission letter (Zulassungsbescheid)',
        'Verify passport validity (at least 3 months past stay)',
        'Prepare financial proof (bank balance for 12 months or scholarship)',
        'Secure accommodation lease or student dormitory contract',
        'Obtain criminal record extract with apostille / translation',
        'Arrange comprehensive health insurance (ÖGK student policy)',
    ],
    'work_permit' => [
        'Obtain signed employer declaration form (Arbeitgebererklärung)',
        'Collect certified degree certificates with sworn German translations',
        'Prepare language proficiency test results (ÖSD/Goethe/IELTS)',
        'Submit application to Austrian embassy or local authority',
    ],
    'residence' => [
        'Complete Meldezettel registration form',
        'Obtain landlord signature (if renting)',
        'Prepare official photo ID (Passport / EU ID card)',
        'Submit within 3 days (online with ID Austria or in-person)',
        'Notify vehicle registry and ORF broadcast fee',
    ],
    'id_austria' => [
        'Install Digitales Amt mobile app',
        'Prepare valid passport or Austrian ID card',
        'Bring recent passport photo (if standalone registration)',
        'Visit passport authority or police office for activation',
    ],
    'tax' => [
        'Verify employer wage statement (L16) on FinanzOnline',
        'Gather receipts for professional expenses and training',
        'Prepare child identification details for Family Bonus Plus',
        'Submit tax return electronically via FinanzOnline',
    ],
    'health' => [
        'Check e-card photo status online at chipkarte.at',
        'Submit biometric photo at registration office if required',
        'Prepare university enrollment confirmation for student insurance',
        'Report sick leave to employer and ÖGK immediately when ill',
    ],
    'driving_vehicle' => [
        'Request electronic motor insurance confirmation',
        'Locate vehicle title (Typenschein/COC) and bill of sale',
        'Ensure § 57a vehicle inspection certificate is valid',
        'Apply for non-EU driver license conversion within 6 months',
    ],
    'family' => [
        'Obtain birth certificate and citizenship proof for newborn',
        'Submit Childcare Allowance (KBG) application to health insurance fund',
        'Complete mandatory Parent-Child booklet examinations on schedule',
        'Schedule marriage registration appointment at civil registry office',
    ],
    'employment' => [
        'Submit unemployment claim to AMS on first day without work',
        'Gather recent payslips and formal notice of termination',
        'Activate eAMS online account using ID Austria',
        'Report any marginal employment to the AMS before commencing work',
    ],
    'citizenship' => [
        'Verify unbroken legal residence history in Austria',
        'Compile income statements and tax assessments for last 36 months',
        'Obtain certified B1/B2 German and citizenship exam certificates',
        'Submit application to provincial government authority or MA 35',
    ],
    'human' => [
        'Call Federal Chancellery Helpdesk: +43 1 53115-0',
        'Send email to buergerservice@bka.gv.at',
        'Schedule appointment at your municipal district authority',
    ],
];
