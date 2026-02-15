<?php

use App\Models\Client;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it auto generates a ULID token on creation', function () {
    $client = Client::create(['name' => 'Test Client']);

    expect($client->token)->not->toBeNull()
        ->and(Str::isUlid($client->token))->toBeTrue();
});

test('it rejects requests without a token', function () {
    $response = $this->getJson('/api/clients');

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthorized Client']);
});

test('it rejects requests with an invalid token', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer invalid-token',
    ])->getJson('/api/clients');

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthorized Client']);
});

test('it accepts requests with a valid bearer token', function () {
    $client = Client::create(['name' => 'Authorized Client']);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $client->token,
    ])->getJson('/api/clients');

    $response->assertStatus(200);
});

test('it accepts requests with a valid X-Client-Token header', function () {
    $client = Client::create(['name' => 'Authorized Client Header']);

    $response = $this->withHeaders([
        'X-Client-Token' => $client->token,
    ])->getJson('/api/clients');

    $response->assertStatus(200);
});
