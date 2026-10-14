<?php

/*
 * Kompetent – official sources per topic (language-neutral part).

 * Titles and descriptions live in lang/{locale}/sources.php under the same id.
 * Only Austrian authority sources (CI chapter 16). Order matters: it is the order offered to the model.
 */

return [
    'passport' => [
        ['id' => 'passport.passport_renewal', 'url' => 'https://www.oesterreich.gv.at/themen/dokumente_und_recht/reisepass/Seite.020100.html', 'tag' => 'Passgesetz 1992'],
        ['id' => 'passport.biometric_passport_photo', 'url' => 'https://www.oesterreich.gv.at/themen/dokumente_und_recht/reisepass/kriterien_fuer_die_erstellung_von_passbildern.html', 'tag' => 'BMI Standard'],
        ['id' => 'passport.lost_stolen_passport', 'url' => 'https://www.oesterreich.gv.at/themen/dokumente_und_recht/reisepass/Seite.020500.html', 'tag' => 'Verlustanzeige'],
    ],
    'residence' => [
        ['id' => 'residence.residence_registration_deregistration', 'url' => 'https://www.oesterreich.gv.at/themen/dokumente_und_recht/an__abmeldung_des_wohnsitzes/Seite.1180200.html', 'tag' => '§ 1 MeldeG'],
        ['id' => 'residence.online_residence_registration', 'url' => 'https://www.oesterreich.gv.at/id-austria.html', 'tag' => 'ID Austria Vollfunktion'],
        ['id' => 'residence.meldezettel_pdf_form', 'url' => 'https://www.bmi.gv.at/202/files/Meldezettel_neu.pdf', 'tag' => 'Formular BMI'],
    ],
    'human' => [
        ['id' => 'human.federal_chancellery_citizen', 'url' => 'https://www.bundeskanzleramt.gv.at/service/buergerservice.html', 'tag' => 'Bürgerservice BKA'],
        ['id' => 'human.city_vienna_citizen', 'url' => 'https://www.wien.gv.at/verwaltung/stadtservice/', 'tag' => 'Stadt Wien'],
    ],
    'id_austria' => [
        ['id' => 'id_austria.id_austria_app', 'url' => 'https://www.oesterreich.gv.at/id-austria.html', 'tag' => 'Bundeskanzleramt'],
        ['id' => 'id_austria.digitales_amt_mobile', 'url' => 'https://www.oesterreich.gv.at/digitales-amt.html', 'tag' => 'Digitales Amt'],
    ],
    'permit' => [
        ['id' => 'permit.student_residence_permit', 'url' => 'https://www.oesterreich.gv.at/themen/leben_in_oesterreich/aufenthalt/Seite.120120.html', 'tag' => '§ 64 NAG'],
        ['id' => 'permit.oead_entry_residence', 'url' => 'https://oead.at/de/nach-oesterreich/einreise-und-aufenthalt', 'tag' => 'OeAD Richtlinie'],
        ['id' => 'permit.immigration_authority_ma', 'url' => 'https://www.wien.gv.at/kontakte/ma35/', 'tag' => 'Aufenthaltsbehörde'],
    ],
    'work_permit' => [
        ['id' => 'work_permit.red_white_red', 'url' => 'https://www.migration.gv.at/de/formen-der-zuwanderung/dauerhafte-zuwanderung/', 'tag' => 'AuslBG & NAG'],
        ['id' => 'work_permit.ams_shortage_occupations', 'url' => 'https://www.ams.at/organisation/arbeitsmarktdaten/mangelberufe', 'tag' => 'AMS Kriterien'],
    ],
    'tax' => [
        ['id' => 'tax.employee_tax_assessment', 'url' => 'https://www.bmf.gv.at/themen/steuern/arbeitnehmerveranlagung.html', 'tag' => 'EStG 1988'],
        ['id' => 'tax.finanzonline', 'url' => 'https://finanzonline.bmf.gv.at/', 'tag' => 'FinanzOnline'],
        ['id' => 'tax.family_bonus_plus', 'url' => 'https://www.bmf.gv.at/themen/steuern/arbeitnehmerveranlagung/familienbonus-plus.html', 'tag' => '§ 33 EStG'],
    ],
    'health' => [
        ['id' => 'health.e_card_portal', 'url' => 'https://www.chipkarte.at/', 'tag' => 'ASVG § 31a'],
        ['id' => 'health.health_insurance_fund', 'url' => 'https://www.gesundheitskasse.at/selbstversicherung', 'tag' => 'ÖGK Tarif'],
        ['id' => 'health.prescription_fee_exemption', 'url' => 'https://www.gesundheitskasse.at/rezeptgebuehr', 'tag' => 'MeineSV'],
    ],
    'driving_vehicle' => [
        ['id' => 'driving_vehicle.driving_license_services', 'url' => 'https://www.oesterreich.gv.at/themen/dokumente_und_recht/fuehrerschein.html', 'tag' => 'FSG 1997'],
        ['id' => 'driving_vehicle.motor_vehicle_registration', 'url' => 'https://www.oesterreich.gv.at/themen/mobilitaet/kfz_an__abmeldung.html', 'tag' => 'KFG 1967'],
        ['id' => 'driving_vehicle.asfinag_motorway_toll', 'url' => 'https://shop.asfinag.at/', 'tag' => 'BStMG'],
    ],
    'family' => [
        ['id' => 'family.family_allowance_familienbeihilfe', 'url' => 'https://www.bmf.gv.at/themen/familien/familienbeihilfe.html', 'tag' => 'FLAG 1967'],
        ['id' => 'family.childcare_allowance_kinderbetreuungsgeld', 'url' => 'https://www.bundeskanzleramt.gv.at/agenda/familie/kinderbetreuungsgeld.html', 'tag' => 'KBGG'],
        ['id' => 'family.civil_registry_marriage', 'url' => 'https://www.oesterreich.gv.at/themen/familie_und_partnerschaft/heirat.html', 'tag' => 'PStG 2013'],
    ],
    'employment' => [
        ['id' => 'employment.public_employment_service', 'url' => 'https://www.ams.at/arbeitsuchende/arbeitslos-was-tun', 'tag' => 'AlVG 1977'],
        ['id' => 'employment.chamber_labor_arbeiterkammer', 'url' => 'https://www.arbeiterkammer.at/', 'tag' => 'Arbeitsrecht'],
    ],
    'citizenship' => [
        ['id' => 'citizenship.citizenship_naturalization', 'url' => 'https://www.oesterreich.gv.at/themen/leben_in_oesterreich/staatsbuergerschaft.html', 'tag' => 'StbG 1985'],
    ],
];
