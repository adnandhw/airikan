<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/transactions/{transaction}/print', function (App\Models\Transaction $transaction) {
    return view('transactions.print', compact('transaction'));
})->name('transactions.print');
