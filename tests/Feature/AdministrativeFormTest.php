<?php

use App\Models\AdministrativeForm;
use App\Models\KnowledgeNugget;
use App\Services\Citations\CitedSource;
use App\Services\Citations\Contracts\CitationResolver;
use Database\Seeders\AdministrativeFormSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(AdministrativeFormSeeder::class);
});

test('administrative forms can be retrieved by relevance score', function () {
    $forms = AdministrativeForm::findRelevantForms('application form for Red-White-Red Card', ['work_permit'], 'en', 2);

    expect($forms)->not->toBeEmpty();
    $first = $forms->first();
    expect($first)->not->toBeNull()
        ->and($first->form_code)->toBe('NAG-Erstantrag')
        ->and($first->download_url)->toBe('https://www.bmi.gv.at/312/files/Antrag_Erteilung_Aufenthaltstitel.pdf');
});

test('detects form intent in citizen queries', function () {
    expect(AdministrativeForm::containsFormIntent('where can i download the form'))->toBeTrue()
        ->and(AdministrativeForm::containsFormIntent('ich brauche den antrag als pdf'))->toBeTrue()
        ->and(AdministrativeForm::containsFormIntent('antragsformular ausfüllen'))->toBeTrue()
        ->and(AdministrativeForm::containsFormIntent('wie ist das wetter heute'))->toBeFalse();
});

test('citation resolver injects exact official form at the top when user requests a form', function () {
    $resolver = app(CitationResolver::class);
    $sources = $resolver->resolve(
        'Where can I download the application form for the Red-White-Red Card?',
        ['work_permit'],
        'en',
        collect([
            new KnowledgeNugget([
                'title' => 'RWR Info',
                'official_source_url' => 'https://www.migration.gv.at/de/formen-der-zuwanderung/dauerhafte-zuwanderung/',
            ]),
        ])
    );

    expect($sources)->not->toBeEmpty();

    $firstSource = $sources[0];
    expect($firstSource->origin)->toBe('form')
        ->and($firstSource->url)->toBe('https://www.bmi.gv.at/312/files/Antrag_Erteilung_Aufenthaltstitel.pdf')
        ->and($firstSource->number)->toBe(1)
        ->and($firstSource->title)->toContain('Application for a Residence Title');
});

test('citation resolver finds Meldezettel form for residence registration form query', function () {
    $resolver = app(CitationResolver::class);
    $sources = $resolver->resolve(
        'Wo finde ich das Meldezettel Formular zum Ausfüllen?',
        ['residence'],
        'de',
        collect()
    );

    $formSource = collect($sources)->first(fn (CitedSource $s) => $s->origin === 'form');
    expect($formSource)->not->toBeNull()
        ->and($formSource->url)->toBe('https://www.bmi.gv.at/202/files/Meldezettel_neu.pdf')
        ->and($formSource->title)->toContain('Meldezettel');
});

test('hit counter increments when form is retrieved', function () {
    $form = AdministrativeForm::where('form_code', 'NAG-Erstantrag')->firstOrFail();
    $initialHits = $form->hit_count;

    AdministrativeForm::findRelevantForms('Red-White-Red Card form', ['work_permit'], 'en', 1);

    expect($form->fresh()->hit_count)->toBe($initialHits + 1);
});
