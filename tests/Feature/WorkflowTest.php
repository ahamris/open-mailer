<?php

use App\Models\Email;
use App\Models\Workflow;
use App\Services\WorkflowEngine;

test('workflow matches trigger correctly', function () {
    $workflow = Workflow::create([
        'name' => 'Test WF',
        'triggers' => [['field' => 'from', 'operator' => 'contains', 'value' => 'overheid.nl']],
        'actions' => [['type' => 'star']],
        'active' => true,
    ]);

    $email = Email::factory()->create([
        'direction' => 'inbound',
        'from_address' => 'info@gemeente.overheid.nl',
        'is_starred' => false,
    ]);

    app(WorkflowEngine::class)->processIncoming($email);

    expect($email->fresh()->is_starred)->toBeTrue();
    expect($workflow->fresh()->times_triggered)->toBe(1);
});

test('workflow does not match when trigger fails', function () {
    Workflow::create([
        'name' => 'No Match',
        'triggers' => [['field' => 'from', 'operator' => 'equals', 'value' => 'specific@email.nl']],
        'actions' => [['type' => 'star']],
        'active' => true,
    ]);

    $email = Email::factory()->create([
        'direction' => 'inbound',
        'from_address' => 'different@email.nl',
        'is_starred' => false,
    ]);

    app(WorkflowEngine::class)->processIncoming($email);

    expect($email->fresh()->is_starred)->toBeFalse();
});

test('inactive workflow is skipped', function () {
    Workflow::create([
        'name' => 'Inactive',
        'triggers' => [['field' => 'from', 'operator' => 'contains', 'value' => '@']],
        'actions' => [['type' => 'star']],
        'active' => false,
    ]);

    $email = Email::factory()->create(['direction' => 'inbound', 'is_starred' => false]);
    app(WorkflowEngine::class)->processIncoming($email);

    expect($email->fresh()->is_starred)->toBeFalse();
});
