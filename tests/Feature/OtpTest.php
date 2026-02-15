<?php

use App\Models\Otp;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->client = Client::create(['name' => 'Auth Client']);
    $this->token = $this->client->token;
});

test('it can generate an OTP for an identifier', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->postJson('/api/otp/create', [
        'identifier' => 'user@example.com'
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['token', 'expires_in']);

    $otp = Otp::where('identifier', 'user@example.com')->first();
    expect($otp)->not->toBeNull()
        ->and($otp->valid)->toBeTrue();
});

test('it can verify a valid OTP', function () {
    // Manually create a known OTP
    $code = '123456';
    Otp::create([
        'identifier' => 'user@example.com',
        'token' => Hash::make($code),
        'valid' => true,
        'expires_at' => Carbon::now()->addMinutes(5)
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->postJson('/api/otp/verify', [
        'otp_code' => $code
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'message' => 'OTP verified successfully',
            'identifier' => 'user@example.com'
        ]);

    $otp = Otp::where('identifier', 'user@example.com')->first();
    expect($otp->valid)->toBeFalse();
});

test('it rejects an invalid OTP', function () {
    Otp::create([
        'identifier' => 'user@example.com',
        'token' => Hash::make('123456'),
        'valid' => true,
        'expires_at' => Carbon::now()->addMinutes(5)
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->postJson('/api/otp/verify', [
        'otp_code' => '654321'
    ]);

    $response->assertStatus(422)
        ->assertJson(['message' => 'Invalid or expired OTP']);
});

test('it rejects an expired OTP', function () {
    $code = '123456';
    Otp::create([
        'identifier' => 'user@example.com',
        'token' => Hash::make($code),
        'valid' => true,
        'expires_at' => Carbon::now()->subMinute()
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->postJson('/api/otp/verify', [
        'otp_code' => $code
    ]);

    $response->assertStatus(422);
});

test('it invalidates old OTPs when a new one is generated', function () {
    Otp::create([
        'identifier' => 'user@example.com',
        'token' => Hash::make('111111'),
        'valid' => true,
        'expires_at' => Carbon::now()->addMinutes(5)
    ]);

    $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->postJson('/api/otp/create', [
        'identifier' => 'user@example.com'
    ]);

    $oldOtp = Otp::where('identifier', 'user@example.com')->where('token', 'like', Hash::make('111111'))->first();
    // Hash::make is random, so we simple count valid otps
    expect(Otp::where('identifier', 'user@example.com')->where('valid', true)->count())->toBe(1);
});
