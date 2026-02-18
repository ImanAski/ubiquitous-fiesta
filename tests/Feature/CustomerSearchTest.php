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
    ])->getJson('/api/users?filters[external_id]=cust_123');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.metadata.external_id', 'cust_123');
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
    ])->getJson('/api/users?filters[name]=John&exact=0');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.metadata.name', 'John Doe');
});

test('get all users', function () {

    Customers::factory(10)->create();

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->getJson('/api/users');

    $response->assertStatus(200)
        ->assertJsonCount(10, 'data');
});

test('get 5 users', function () {
    Customers::factory(10)->create();

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->getJson('/api/users?limit=5');

    $response->assertStatus(200)
        ->assertJsonCount(5, 'data');
});

test('get users with 7 limit and second page', function () {
    Customers::factory(10)->create();

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->getJson('/api/users?limit=7&page=2');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('meta.current_page', 2);
});


test('get a user', function () {
    $customer = Customers::factory()->create();

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->getJson('/api/users/' . $customer->id);

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $customer->id);
});

test('delete a user', function () {
    $customer = Customers::create([]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->deleteJson("/api/users/$customer->id");

    $response->assertStatus(204);
});

test('get user transactions', function () {
    $customer = Customers::create([]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->getJson("/api/users/$customer->id/transactions");

    $response->assertStatus(200);
});

test('get user wallets', function () {
    $customer = Customers::create([]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->getJson("/api/users/$customer->id/wallets");

    $response->assertStatus(200);
});
