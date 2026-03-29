<?php

use App\Models\ApiKey;

test('generate api key returns raw key', function () {
    $result = ApiKey::generate('My Key');
    expect($result['raw_key'])->toStartWith('clom_');
    expect($result['api_key'])->toBeInstanceOf(ApiKey::class);
});

test('find api key by raw key', function () {
    $result = ApiKey::generate('Test');
    $found = ApiKey::findByRawKey($result['raw_key']);
    expect($found)->not->toBeNull();
    expect($found->id)->toBe($result['api_key']->id);
});

test('invalid raw key returns null', function () {
    expect(ApiKey::findByRawKey('invalid'))->toBeNull();
});
