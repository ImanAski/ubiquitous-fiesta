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

    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::post('pay', [\App\Http\Controllers\TransactionController::class, 'pay'])->name('pay');
        Route::post('charge', [\App\Http\Controllers\TransactionController::class, 'charge'])->name('charge');
        Route::post('transfer', [\App\Http\Controllers\TransactionController::class, 'transfer'])->name('transfer');
        Route::get('/', [\App\Http\Controllers\TransactionController::class, 'index'])->name('index');
        Route::get('/{transaction}', [\App\Http\Controllers\TransactionController::class, 'show'])->name('show');
    });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
