<?php

use App\Models\Client;
use App\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->client = Client::create(['name' => 'Auth Client']);
    $this->token = $this->client->token;
});

test('get all currencies', function () {
    Currency::factory(10)->create();

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->getJson('/api/currencies');

    $response->assertStatus(200)
        ->assertJsonCount(10, 'data');
});

test('get all currencies with limit', function () {
    Currency::factory(10)->create();

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->getJson('/api/currencies?limit=6');

    $response->assertStatus(200)
        ->assertJsonCount(6, 'data')
        ->assertJsonPath('meta.current_page', 1);
});

test('get currencies by id', function () {
    $currency = Currency::factory()->create();

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->getJson('/api/currencies/' . $currency->id);

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $currency->id)
        ->assertJsonPath('data.name', $currency->name)
        ->assertJsonPath('data.symbol', $currency->symbol)
        ->assertJsonPath('data.code', $currency->code);
});

test('delete a currency', function () {
    $currency = Currency::factory()->create();

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->deleteJson('/api/currencies/' . $currency->id);

    $response->assertStatus(204);
});
