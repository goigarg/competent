<?php

/*
 * Kompetent – keywords that trigger an intent, for all languages at once
 * (the question may be in another language than the interface).
 *
 * Matching is case-insensitive substring matching. Every language's "talk to a person" button text
 * (answer.talk_to_person) is added to the human intent automatically.
 */

return [
    // The citizen wants a person instead of the AI.
    'human' => [
        // de
        'mensch', 'berater', 'beamte', 'sachbearbeit', 'telefon', 'hotline', 'mit einer person', 'person sprechen',
        // en
        'human', 'talk to a person', 'real person', 'caseworker', 'phone',
        // tr
        'bir kişiyle', 'insanla', 'yetkili',
        // bs / hr / sr
        's osobom', 'službenik', 'sa osobom',
        // uk
        'з людиною', 'оператор', 'працівник',
        // ro
        'cu o persoană', 'funcționar', 'om real',
        // ar
        'موظف', 'إنسان',
    ],

    // Next-step buttons that open the case summary for the authority.
    'dossier' => ['dossier', 'zusammenfassung herunterladen', 'download dossier', 'досьє', 'dosar'],

    // Guided workflows started from a free-text question.
    'workflows' => [
        'passport' => ['pass', 'reisepass', 'passport', 'pasaport', 'putovnic', 'pasoš', 'паспорт', 'pașaport', 'جواز'],
        'residence' => ['umzug', 'umgemeldet', 'wohnsitz', 'meldezettel', 'moving', 'taşın', 'ikamet', 'preseli', 'prebivališt', 'переїзд', 'mutare', 'domiciliu', 'انتقال', 'سكن'],
    ],
];
