<?php

namespace Database\Seeders;

use App\Models\AdministrativeForm;
use Illuminate\Database\Seeder;

class AdministrativeFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $forms = [
            [
                'domain' => 'work_permit',
                'form_code' => 'NAG-Erstantrag',
                'title' => 'Antrag auf Erteilung eines Aufenthaltstitels (Rot-Weiß-Rot – Karte / Blaue Karte EU)',
                'title_en' => 'Application for a Residence Title (Red-White-Red Card / EU Blue Card)',
                'description' => 'Offizielles Antragsformular für den Erstantrag auf Erteilung eines Aufenthaltstitels gem. Niederlassungs- und Aufenthaltsgesetz (NAG).',
                'authority' => 'Bundesministerium für Inneres (BMI)',
                'download_url' => 'https://www.bmi.gv.at/312/files/Antrag_Erteilung_Aufenthaltstitel.pdf',
                'online_url' => 'https://www.migration.gv.at/de/service-und-links/formulare/',
                'keywords' => [
                    'rot-weiß-rot', 'red-white-red', 'rwr', 'aufenthaltstitel', 'antrag', 'formular',
                    'antragsformular', 'application', 'blaue karte', 'blue card', 'arbeitgeber',
                    'arbeitserlaubnis', 'niederlassung', 'mangelberuf', 'fachkraft', 'pdf', 'download',
                ],
                'is_verified' => true,
            ],
            [
                'domain' => 'work_permit',
                'form_code' => 'AMS-Arbeitgebererklärung',
                'title' => 'Arbeitgebererklärung für Rot-Weiß-Rot – Karte & Blaue Karte EU',
                'title_en' => 'Employer Declaration for Red-White-Red Card (AMS Official Form)',
                'description' => 'Vom österreichischen Arbeitgeber zwingend auszufüllende Bestätigung über Stellenbeschreibung, Qualifikation und kollektivvertragliche Entlohnung.',
                'authority' => 'Arbeitsmarktservice (AMS)',
                'download_url' => 'https://www.ams.at/content/dam/download/formulare/001_arbeitgebererklaerung.pdf',
                'online_url' => 'https://www.ams.at/unternehmen/service-zur-personalsuche/beschaeftigung-auslaendischer-arbeitskraefte',
                'keywords' => [
                    'arbeitgebererklärung', 'arbeitgeber', 'ams', 'rot-weiß-rot', 'red-white-red',
                    'gehalt', 'brutto', 'kollektivvertrag', 'formular', 'download', 'pdf',
                ],
                'is_verified' => true,
            ],
            [
                'domain' => 'work_permit',
                'form_code' => 'NAG-Verlängerung',
                'title' => 'Antrag auf Verlängerung eines Aufenthaltstitels (Rot-Weiß-Rot – Karte Plus)',
                'title_en' => 'Application for Renewal of Residence Title (Red-White-Red Card Plus)',
                'description' => 'Formular für die Verlängerung bestehender Aufenthaltstitel oder den Umstieg auf die RWR-Karte Plus nach 21 Monaten.',
                'authority' => 'Bundesministerium für Inneres (BMI)',
                'download_url' => 'https://www.bmi.gv.at/312/files/Antrag_Verlaengerung_Aufenthaltstitel.pdf',
                'online_url' => 'https://www.migration.gv.at/de/service-und-links/formulare/',
                'keywords' => [
                    'verlängerung', 'verlängern', 'zweitantrag', 'rwr plus', 'rot-weiß-rot plus',
                    'antrag', 'formular', 'aufenthaltstitel', 'renewal',
                ],
                'is_verified' => true,
            ],
            [
                'domain' => 'permit',
                'form_code' => 'NAG-Student',
                'title' => 'Antrag auf Aufenthaltsbewilligung Student',
                'title_en' => 'Application for Student Residence Permit (§ 64 NAG)',
                'description' => 'Offizielles Antragsformular für internationale Studierende an österreichischen Hochschulen.',
                'authority' => 'Bundesministerium für Inneres (BMI)',
                'download_url' => 'https://www.bmi.gv.at/312/files/Antrag_Erteilung_Aufenthaltstitel.pdf',
                'online_url' => 'https://oead.at/de/nach-oesterreich/einreise-und-aufenthalt',
                'keywords' => [
                    'student', 'studienvisum', 'studentenvisum', 'aufenthaltsbewilligung',
                    'studium', 'universität', 'oead', 'antrag', 'formular', 'visum',
                ],
                'is_verified' => true,
            ],
            [
                'domain' => 'residence',
                'form_code' => 'BMI-Meldezettel',
                'title' => 'Meldezettel (Anmeldung / Ummeldung des Wohnsitzes)',
                'title_en' => 'Official Residence Registration Form (Meldezettel)',
                'description' => 'Amtliches Formular zur Anmeldung, Abmeldung oder Ummeldung des Wohnsitzes gem. § 9 Meldegesetz inkl. Bestätigung des Unterkunftgebers.',
                'authority' => 'Bundesministerium für Inneres (BMI)',
                'download_url' => 'https://www.bmi.gv.at/202/files/Meldezettel_neu.pdf',
                'online_url' => 'https://www.oesterreich.gv.at/id-austria.html',
                'keywords' => [
                    'meldezettel', 'wohnsitz', 'hauptwohnsitz', 'nebenwohnsitz', 'anmeldung',
                    'umzug', 'unterkunftgeber', 'vermieter', 'meldeamt', 'formular', 'pdf',
                ],
                'is_verified' => true,
            ],
            [
                'domain' => 'tax',
                'form_code' => 'BMF-L1',
                'title' => 'Erklärung zur Arbeitnehmerinnenveranlagung (Formular L1)',
                'title_en' => 'Employee Tax Assessment Return Form (L1)',
                'description' => 'Amtlicher Vordruck des Bundesministeriums für Finanzen zur Durchführung der Arbeitnehmerveranlagung in Papierform.',
                'authority' => 'Bundesministerium für Finanzen (BMF)',
                'download_url' => 'https://service.bmf.gv.at/service/allg/formulare/show_mast.asp?gemnr=0&frmid=L1',
                'online_url' => 'https://finanzonline.bmf.gv.at/',
                'keywords' => [
                    'l1', 'steuerausgleich', 'arbeitnehmerveranlagung', 'lohnsteuerausgleich',
                    'steuererklärung', 'finanzamt', 'formular', 'tax return',
                ],
                'is_verified' => true,
            ],
            [
                'domain' => 'tax',
                'form_code' => 'BMF-L1k',
                'title' => 'Beilage L1k zur Arbeitnehmerveranlagung (Familienbonus Plus & Kinder)',
                'title_en' => 'Form L1k Annex for Family Bonus Plus and Children',
                'description' => 'Amtliche Beilage für Berücksichtigung von Kindern, Familienbonus Plus und Mehrkindzuschlag.',
                'authority' => 'Bundesministerium für Finanzen (BMF)',
                'download_url' => 'https://service.bmf.gv.at/service/allg/formulare/show_mast.asp?gemnr=0&frmid=L1k',
                'online_url' => 'https://finanzonline.bmf.gv.at/',
                'keywords' => [
                    'l1k', 'familienbonus', 'familienbonus plus', 'kinder', 'absetzbetrag',
                    'mehrkindzuschlag', 'formular', 'beilage',
                ],
                'is_verified' => true,
            ],
            [
                'domain' => 'family',
                'form_code' => 'BMF-Beih100',
                'title' => 'Antrag auf Gewährung der Familienbeihilfe (Formular Beih 100)',
                'title_en' => 'Application for Austrian Family Allowance (Form Beih 100)',
                'description' => 'Amtlicher Antrag beim Finanzamt für Familienbeihilfe nach dem Familienlastenausgleichsgesetz (FLAG).',
                'authority' => 'Bundesministerium für Finanzen (BMF)',
                'download_url' => 'https://service.bmf.gv.at/service/allg/formulare/show_mast.asp?gemnr=0&frmid=Beih100',
                'online_url' => 'https://finanzonline.bmf.gv.at/',
                'keywords' => [
                    'familienbeihilfe', 'kinderbeihilfe', 'beih 100', 'beih100', 'finanzamt',
                    'kinder', 'baby', 'geburt', 'antrag', 'formular',
                ],
                'is_verified' => true,
            ],
            [
                'domain' => 'general',
                'form_code' => 'GV-Formularservice',
                'title' => 'Zentrales Formularservice der Republik Österreich (oesterreich.gv.at)',
                'title_en' => 'Central Government Forms & Applications Service of Austria',
                'description' => 'Offizielles Verzeichnis aller bundesweiten Formulare, PDFs und Online-Verfahren österreichischer Behörden.',
                'authority' => 'Bundeskanzleramt (BKA)',
                'download_url' => 'https://www.oesterreich.gv.at/formularservice.html',
                'online_url' => 'https://www.oesterreich.gv.at/formularservice.html',
                'keywords' => [
                    'formularservice', 'formulare', 'anträge', 'alle formulare', 'download center',
                    'behördenformulare', 'amtshelfer', 'pdf download',
                ],
                'is_verified' => true,
            ],
        ];

        foreach ($forms as $data) {
            AdministrativeForm::updateOrCreate(
                ['form_code' => $data['form_code']],
                $data,
            );
        }
    }
}
