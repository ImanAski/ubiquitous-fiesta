<?php

use App\Models\Client;
use App\Models\Customers;
use App\Models\Currency;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->client = Client::create(['name' => 'API Client']);
    $this->token = $this->client->token;

    $this->currency = Currency::create([
        'name' => 'USD',
        'symbol' => '$',
        'code' => 'USD'
    ]);

    $this->customer1 = Customers::create(['metadata' => ['name' => 'Customer 1']]);
    $this->wallet1 = Wallet::create([
        'customer_id' => $this->customer1->id,
        'currency_id' => $this->currency->id,
        'balance' => 1000
    ]);

    $this->customer2 = Customers::create(['metadata' => ['name' => 'Customer 2']]);
    $this->wallet2 = Wallet::create([
        'customer_id' => $this->customer2->id,
        'currency_id' => $this->currency->id,
        'balance' => 500
    ]);
});

test('it can pay using a wallet', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->postJson('/api/transactions/pay', [
        'wallet_id' => $this->wallet1->id,
        'amount' => 200
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Success',
            'new_balance' => 800
        ]);

    $this->assertDatabaseHas('transactions', [
        'wallet_id' => $this->wallet1->id,
        'amount' => 200,
        'from_customer_id' => $this->customer1->id
    ]);
});

test('it fails to pay with insufficient balance', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->postJson('/api/transactions/pay', [
        'wallet_id' => $this->wallet1->id,
        'amount' => 2000
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'error' => 'Insufficient balance'
        ]);
});

test('it can transfer between wallets', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->postJson('/api/transactions/transfer', [
        'from_wallet_id' => $this->wallet1->id,
        'to_wallet_id' => $this->wallet2->id,
        'amount' => 300
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Success'
        ]);

    expect($this->wallet1->fresh()->balance)->toBe(700)
        ->and($this->wallet2->fresh()->balance)->toBe(800);

    $this->assertDatabaseHas('transactions', [
        'wallet_id' => $this->wallet1->id,
        'amount' => 300,
        'from_customer_id' => $this->customer1->id,
        'to_customer_id' => $this->customer2->id
    ]);
});

test('it fails to transfer with insufficient balance', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->postJson('/api/transactions/transfer', [
        'from_wallet_id' => $this->wallet2->id,
        'to_wallet_id' => $this->wallet1->id,
        'amount' => 600
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'error' => 'Insufficient balance'
        ]);
});

test('it can generate a charge link', function () {
    $this->mock(\App\Services\Gateways\KishpayGateway::class, function ($mock) {
        $mock->shouldReceive('generateLink')->once()->andReturn('https://payment.kishpay.ir/fake-token');
    });

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->postJson('/api/transactions/charge', [
        'wallet_id' => $this->wallet1->id,
        'amount' => 5000,
        'callback_url' => 'https://example.com/callback'
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Success',
            'link' => 'https://payment.kishpay.ir/fake-token'
        ])
        ->assertJsonStructure([
            'transaction_id' => ['id', 'amount', 'status']
        ]);

    $this->assertDatabaseHas('transactions', [
        'wallet_id' => $this->wallet1->id,
        'amount' => 5000,
        'status' => \App\Enums\TransactionStatus::PENDING,
        'type' => \App\Enums\TransactionType::CREDIT
    ]);
});

test('it fails to generate charge link with invalid data', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->postJson('/api/transactions/charge', [
        'wallet_id' => 999, // Non-existent
        'amount' => 0, // Invalid min
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['wallet_id', 'amount', 'callback_url']);
});
