<?php

use App\Http\Controllers\DepositoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;


Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/dashboard-deposito', [DashboardController::class, 'index'])->name('custom.dashboard');


