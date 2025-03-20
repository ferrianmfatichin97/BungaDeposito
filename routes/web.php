<?php

use App\Http\Controllers\DepositoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/deposito', [DepositoController::class, 'index']);

