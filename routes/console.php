<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::command('reminder:deposito')->dailyAt('11:24');
// Schedule::command('wa:deposito-rekap --hari=7 --kodeCabang=00')->dailyAt('16::22');
