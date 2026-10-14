<?php

use App\Services\AustrianGov\Workflows\PassportWorkflow;
use App\Services\AustrianGov\Workflows\ResidenceWorkflow;

test('passport workflow calculates correct fees for standard and express', function () {
    $wf = new PassportWorkflow;
    expect($wf->getFeeInfo()['amount'])->toBe(75.90);

    $wf->setUrgency('express');
    expect($wf->getFeeInfo()['amount'])->toBe(100.00);

    $wf->setUrgency('one_day');
    expect($wf->getFeeInfo()['amount'])->toBe(220.00);

    $wf->setUrgency('standard');
    $wf->setAgeGroup('child');
    expect($wf->getFeeInfo()['amount'])->toBe(30.00);
});

test('passport workflow adapts checklist for lost passport scenario', function () {
    $wf = new PassportWorkflow;
    $wf->setScenario('lost_stolen');

    $checklist = $wf->getChecklist('de');
    $itemIds = array_column($checklist, 'id');

    expect($itemIds)->toContain('loss_report')
        ->and($itemIds)->toContain('other_photo_id')
        ->and($itemIds)->not->toContain('old_passport');
});

test('residence workflow warns when 3-day deadline is exceeded', function () {
    $wf = new ResidenceWorkflow;
    $wf->setMoveStatus('already_moved', 10);

    $status = $wf->getDeadlineStatus('de');
    expect($status['is_compliant'])->toBeFalse()
        ->and($status['severity'])->toBe('urgent')
        ->and($status['warning'])->toContain('überschritten');
});

test('residence workflow mandates landlord signature for rental housing', function () {
    $wf = new ResidenceWorkflow;
    $wf->setHousingType('rented');

    $checklist = $wf->getChecklist('de');
    $itemIds = array_column($checklist, 'id');

    expect($itemIds)->toContain('landlord_signature');
});

test('residence workflow generates comprehensive civil servant dossier', function () {
    $wf = new ResidenceWorkflow;
    $wf->setResidenceType('hauptwohnsitz');
    $wf->setHousingType('rented');

    $dossier = $wf->generateDossierSummary('de');
    expect($dossier)->toContain('BÜRGER-DOSSIER: WOHNSITZMELDUNG')
        ->and($dossier)->toContain('Hauptwohnsitz')
        ->and($dossier)->toContain('Gebühr: 0,00 €');
});
