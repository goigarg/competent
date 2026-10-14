<?php

/*
 * Kompetent – texts of the guided passport and residence workflows (checklists, deadlines, dossiers).
 *
 * Factual content: only reviewed languages have this file; others fall back (App\Enums\Language::contentChain).
 */

return [
    'passport' => [
        'fee' => [
            'format' => '€:amount',
            'decimal_separator' => '.',
            'thousands_separator' => ',',
            'free_first_issue' => 'Free of charge (first issue) or €30.00',
            'duration_one_day' => 'Delivered on the next working day (one-day express)',
            'duration_express' => 'Delivered in 2–3 working days (express passport)',
            'duration_standard' => 'approx. 5 working days (registered mail)',
            'validity' => ':years years',
        ],
        'items' => [
            'loss_report' => [
                'text' => 'Official Loss or Police Theft Report',
                'detail' => 'From police in case of theft, or municipal lost & found for loss.',
            ],
            'other_photo_id' => [
                'text' => 'Alternative Photo ID (e.g., Driving Licence or EU ID Card)',
                'detail' => 'To verify your identity at the service desk.',
            ],
            'certificates' => [
                'text' => 'Birth Certificate & Citizenship Proof',
                'detail' => 'Mandatory when passport has been expired for more than 5 years.',
            ],
            'old_passport' => [
                'text' => 'Current or Expired Passport',
                'detail' => 'Will be cancelled and handed back to you as a keepsake if desired.',
            ],
            'photo' => [
                'text' => '1 Recent Biometric Passport Photograph (35 x 45 mm)',
                'detail' => 'Max 6 months old, neutral expression, high resolution.',
            ],
            'fee' => [
                'text' => 'Fee :fee (Debit card or cash)',
                'detail' => 'Includes manufacturing and registered postal delivery (:duration).',
            ],
            'appointment' => [
                'text' => 'Booked Online Appointment at Passport Office',
                'detail' => 'Avoid waiting times at your local district municipal office.',
            ],
        ],
        'dossier' => [
            'title' => '=== CITIZEN DOSSIER: AUSTRIAN PASSPORT APPLICATION 2030 ===',
            'inquiry' => 'Inquiry: Passport Issuance / Renewal',
            'scenario' => 'Case Scenario: :value',
            'urgency' => 'Urgency: :value (:duration)',
            'fee' => 'Calculated Fee: :fee',
            'progress' => 'Document Preparation: :done of :total items prepared',
            'checklist' => 'Checklist Status:',
            'authority' => 'Official Authority: Any passport office in Austria (Magistrate / District Commission)',
            'default_value' => 'Standard',
        ],
    ],
    'residence' => [
        'deadline' => [
            'planning' => 'Great preparation! The statutory 3-day deadline starts only once you physically move into the residence.',
            'within' => 'You are within the statutory 3-day window under Austrian residence law. Please finalize registration soon.',
            'overdue' => 'Attention: The 3-day statutory deadline is exceeded by :days days. Please complete registration promptly to avoid administrative warnings.',
        ],
        'items' => [
            'id_austria_ready' => [
                'text' => 'Active ID Austria on Smartphone',
                'detail' => 'Enables 100% digital registration without visiting city hall.',
            ],
            'meldezettel' => [
                'text' => 'Meldezettel Application Form Filled Out',
                'detail' => 'Official registration form with personal and address details.',
            ],
            'landlord_signature' => [
                'text' => 'Landlord / Property Owner Signature',
                'detail' => 'Legally mandatory on the form for tenants/renters.',
            ],
            'owner_proof' => [
                'text' => 'Proof of Ownership (Title deed / Land registry)',
                'detail' => 'As homeowner, you sign the landlord section yourself.',
            ],
            'photo_id' => [
                'text' => 'Official Photo ID (Passport or EU National ID)',
                'detail' => 'Required for all moving household members.',
            ],
            'car_registry' => [
                'text' => 'Follow-up: Vehicle registration update within 1 week',
                'detail' => 'If moving into a different motor vehicle registration district.',
            ],
            'orf_contribution' => [
                'text' => 'Follow-up: Public broadcast fee (ORF-Beitrag) address update',
                'detail' => 'Per household fee; update address online on the OBS portal.',
            ],
        ],
        'dossier' => [
            'title' => '=== CITIZEN DOSSIER: RESIDENCE REGISTRATION 2030 ===',
            'inquiry' => 'Inquiry: Change of Residence / Registration',
            'type' => 'Residence Category: :value',
            'type_primary' => 'Primary Residence (Hauptwohnsitz)',
            'type_secondary' => 'Secondary Residence (Nebenwohnsitz)',
            'housing' => 'Housing Status: :value',
            'housing_rented' => 'Rental (Landlord signature required)',
            'housing_owner' => 'Owner-occupied',
            'channel' => 'Registration Channel: :value',
            'channel_id_austria' => 'Digital via ID Austria',
            'channel_in_person' => 'In person at registration office',
            'deadline' => 'Deadline Compliance: :value',
            'fee' => 'Cost: €0.00 (Free of charge by law)',
            'progress' => 'Document Status: :done of :total items prepared',
            'checklist' => 'Checklist Details:',
            'authority' => 'Competent Office: Municipal registration office (Gemeindeamt / Magistrat in Vienna)',
        ],
    ],
];
