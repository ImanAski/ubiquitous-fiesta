<?php

use App\Models\Customers;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->client = Client::create(['name' => 'Auth Client']);
    $this->token = $this->client->token;
});

test('it can find a customer by exact metadata', function () {
    Customers::create([
        'metadata' => ['external_id' => 'cust_123', 'email' => 'test@example.com']
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->getJson('/api/users/find?key=external_id&value=cust_123');

    $response->assertStatus(200)
        ->assertJsonPath('data.metadata.external_id', 'cust_123');
});

test('it returns 404 when customer is not found', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->getJson('/api/users/find?key=external_id&value=non_existent');

    $response->assertStatus(404);
});

test('it can find a customer using partial match', function () {
    Customers::create([
        'metadata' => ['name' => 'John Doe']
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->getJson('/api/users/find?key=name&value=John&exact=0');

    $response->assertStatus(200)
        ->assertJsonPath('data.metadata.name', 'John Doe');
});

test('it validates required search parameters', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->getJson('/api/users/find?key=name');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['value']);
});
