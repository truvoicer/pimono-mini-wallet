<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('transaction.index');
})->name('home');

require __DIR__.'/web/currency.php';
require __DIR__.'/web/settings.php';
require __DIR__.'/web/transaction.php';
