<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('set-locale/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'fa'])) {
        session(['locale' => $locale]);
    }
    return back();
})->name('set-locale');
