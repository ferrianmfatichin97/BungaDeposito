<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardKreditController;
use App\Http\Controllers\DepositoController;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return redirect('/admin');
});

Route::prefix('deposito')->name('deposito.')->group(function () {
    Route::get('/list', [DashboardController::class, 'showDepositoList'])->name('list');
    Route::get('/kredit', [DashboardController::class, 'showDepositoKredit'])->name('kredit');
    Route::get('/tabungan', [DashboardController::class, 'showDepositoTabungan'])->name('tabungan');
});

Route::prefix('pengajuan')->name('pengajuan.')->group(function () {
    Route::get('/kemarin', [DashboardController::class, 'showPengajuanKemarin'])->name('kemarin');
    Route::get('/hariini', [DashboardController::class, 'showPengajuanHariIni'])->name('hariini');
    Route::get('/bulanini', [DashboardController::class, 'showPengajuanBulanIni'])->name('bulanini');
});

Route::get('/dashboard', [DashboardController::class, 'deposito'])->name('custom.deposito');
Route::get('/dashboard-deposito', [DashboardController::class, 'index'])->name('custom.dashboard');

Route::get('/dashboard/kredit', [DashboardKreditController::class, 'index'])->name('dashboard.kredit');


