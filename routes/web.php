<?php

use App\Http\Controllers\DebtorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('debtors', DebtorController::class)->only(['index', 'show']);
