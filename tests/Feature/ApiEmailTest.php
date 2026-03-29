<?php

use App\Models\ApiKey;
use App\Models\Email;

beforeEach(function () {
    $result = ApiKey::generate('Test Key');
    $this->apiKey = $result['raw_key'];
});

test('send email via api returns id', function () {
    $response = $this->postJson('/api/emails', [
        'from' => 'Test <test@example.com>',
        'to' => 'recipient@example.com',
        'subject' => 'Test Subject',
        'html' => '<h1>Test</h1>',
    ], ['Authorization' => "Bearer {$this->apiKey}"]);

    $response->assertOk()->assertJsonStructure(['id']);
    expect(Email::count())->toBe(1);
});

test('send email requires auth', function () {
    $this->postJson('/api/emails', [
        'from' => 'test@example.com',
        'to' => 'recipient@example.com',
        'subject' => 'Test',
    ])->assertUnauthorized();
});

test('send email validates required fields', function () {
    $this->postJson('/api/emails', [], [
        'Authorization' => "Bearer {$this->apiKey}",
    ])->assertUnprocessable();
});

test('invalid api key returns 403', function () {
    $this->postJson('/api/emails', [
        'from' => 'test@example.com',
        'to' => 'recipient@example.com',
        'subject' => 'Test',
    ], ['Authorization' => 'Bearer invalid_key'])->assertForbidden();
});

test('list emails via api', function () {
    Email::factory()->count(3)->create(['direction' => 'outbound']);

    $this->getJson('/api/emails', [
        'Authorization' => "Bearer {$this->apiKey}",
    ])->assertOk();
});

test('get single email via api', function () {
    $email = Email::factory()->create();

    $this->getJson("/api/emails/{$email->id}", [
        'Authorization' => "Bearer {$this->apiKey}",
    ])->assertOk()->assertJsonFragment(['subject' => $email->subject]);
});

test('idempotency key prevents duplicate', function () {
    $payload = [
        'from' => 'test@example.com',
        'to' => 'recipient@example.com',
        'subject' => 'Idempotent',
        'html' => '<p>Test</p>',
        'idempotency_key' => 'unique-key-123',
    ];

    $this->postJson('/api/emails', $payload, ['Authorization' => "Bearer {$this->apiKey}"])->assertOk();
    $this->postJson('/api/emails', $payload, ['Authorization' => "Bearer {$this->apiKey}"])->assertOk();

    expect(Email::count())->toBe(1);
});
