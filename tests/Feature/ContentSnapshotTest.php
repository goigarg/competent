<?php

use App\Livewire\AustrianGovAssistant;
use App\Services\AustrianGov\KnowledgeBaseService;
use App\Services\AustrianGov\Workflows\PassportWorkflow;
use App\Services\AustrianGov\Workflows\ResidenceWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

/*
 * Characterisation test: freezes the German and English factual content (fallback articles, sources,
 * workflow checklists, deadline messages, dossiers and assistant messages) so moving it into content
 * files cannot silently change or lose anything.
 *
 * Regenerate deliberately with: UPDATE_CONTENT_SNAPSHOT=1 php artisan test --filter=ContentSnapshot
 */

const CONTENT_SNAPSHOT = __DIR__.'/../Fixtures/content-snapshot.json';

const FALLBACK_QUERIES = [
    'passport' => 'Mein Reisepass läuft ab',
    'permit' => 'student visa aufenthaltsbewilligung',
    'work_permit' => 'rot-weiß-rot karte',
    'residence' => 'meldezettel wohnsitz',
    'id_austria' => 'id austria handysignatur',
    'tax' => 'arbeitnehmerveranlagung steuer',
    'health' => 'e-card krankenversicherung',
    'driving_vehicle' => 'führerschein umschreiben',
    'family' => 'familienbeihilfe kinder',
    'employment' => 'arbeitslosengeld ams',
    'citizenship' => 'staatsbürgerschaft einbürgerung',
    'human' => 'blubb',
];

/**
 * @return array<string, mixed>
 */
function buildContentSnapshot(): array
{
    Carbon::setTestNow('2026-10-14 10:00:00');
    $kb = new KnowledgeBaseService;
    $snapshot = [];

    foreach (['de', 'en'] as $locale) {
        foreach (FALLBACK_QUERIES as $topic => $query) {
            $snapshot[$locale]['fallback'][$topic] = $kb->getFallbackResponse($query, $locale);
        }

        foreach ([...array_keys(FALLBACK_QUERIES), 'general'] as $topic) {
            $snapshot[$locale]['sources'][$topic] = $kb->getOfficialSources($topic, $locale);
        }

        foreach (['expiring', 'expired_recently', 'expired_over_5_years', 'lost_stolen', 'child'] as $scenario) {
            foreach (['standard', 'express', 'one_day'] as $urgency) {
                foreach (['adult', 'child', 'child_small'] as $age) {
                    $wf = new PassportWorkflow([...PassportWorkflow::DEFAULT_STATE, 'scenario' => $scenario, 'urgency' => $urgency, 'age_group' => $age, 'step' => 3]);
                    $snapshot[$locale]['passport']["{$scenario}_{$urgency}_{$age}"] = [
                        'fee' => $wf->getFeeInfo(),
                        'checklist' => $wf->getChecklist($locale),
                        'dossier' => $wf->generateDossierSummary($locale),
                    ];
                }
            }
        }

        foreach (['already_moved' => [1, 3, 7], 'planning_to_move' => [0]] as $status => $daysList) {
            foreach ($daysList as $days) {
                foreach (['rented', 'owner'] as $housing) {
                    foreach (['id_austria', 'in_person'] as $channel) {
                        $wf = new ResidenceWorkflow([...ResidenceWorkflow::DEFAULT_STATE, 'move_status' => $status, 'days_since_move' => $days, 'housing_type' => $housing, 'channel' => $channel, 'step' => 5]);
                        $snapshot[$locale]['residence']["{$status}_{$days}_{$housing}_{$channel}"] = [
                            'deadline' => $wf->getDeadlineStatus($locale),
                            'checklist' => $wf->getChecklist($locale),
                            'dossier' => $wf->generateDossierSummary($locale),
                        ];
                    }
                }
            }
        }

        session()->flush();
        $component = Livewire::test(AustrianGovAssistant::class);
        if ($locale === 'en') {
            $component->call('setLocale', 'en');
        }

        $component->call('startWorkflow', 'passport')
            ->call('setPassportScenario', 'expiring')
            ->call('setPassportUrgency', 'express')
            ->call('startWorkflow', 'residence')
            ->call('setResidenceMoveStatus', 'already_moved', 2)
            ->call('setResidenceType', 'hauptwohnsitz')
            ->call('setResidenceHousing', 'rented')
            ->call('setResidenceChannel', 'id_austria')
            ->call('respondWithHumanSupport');

        $snapshot[$locale]['messages'] = collect($component->get('messages'))
            ->reject(fn (array $m) => $m['role'] === 'system')
            ->map(fn (array $m) => ['role' => $m['role'], 'content' => $m['content'], 'sources' => $m['sources'] ?? []])
            ->values()
            ->all();
    }

    Carbon::setTestNow();

    return $snapshot;
}

test('german and english content stays identical when it is moved', function () {
    $snapshot = buildContentSnapshot();

    if (getenv('UPDATE_CONTENT_SNAPSHOT')) {
        @mkdir(dirname(CONTENT_SNAPSHOT), 0777, true);
        file_put_contents(CONTENT_SNAPSHOT, json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
    }

    expect(file_exists(CONTENT_SNAPSHOT))->toBeTrue('Snapshot missing, run with UPDATE_CONTENT_SNAPSHOT=1');

    $expected = json_decode((string) file_get_contents(CONTENT_SNAPSHOT), true);

    expect(json_decode(json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), true))->toEqual($expected);
});
