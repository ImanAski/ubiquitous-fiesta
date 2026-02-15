<?php

use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\OtpController;
use App\Http\Middleware\EnsureClientToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('client.token')->name("client.")->group(function () {
    Route::name("otp.")->prefix("otp")->group(function () {
        Route::post("create", [OtpController::class, 'create'])->name("create");
        Route::post("verify", [OtpController::class, 'verify'])->name("verify");
    });
    Route::apiResource("currencies", CurrencyController::class);
    Route::get("users/find", [CustomersController::class, 'findCustomer'])->name("users.find");
    Route::apiResource("users", CustomersController::class);
    Route::apiResource("clients", ClientController::class);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
