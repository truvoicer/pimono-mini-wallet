<?php

use App\Http\Controllers\Currency\Data\CurrencyController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::get('currency', [CurrencyController::class, 'index'])->name('currency.index');
});
