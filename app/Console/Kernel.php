<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\SendDepositoRekap;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        SendDepositoRekap::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     */
    protected function schedule(Schedule $schedule)
    {
        $branches = ['00','01','02','03','04','05','06','07','08'];

        foreach ($branches as $cabang) {
            $schedule->command("wa:deposito-rekap --hari=7 --kodeCabang={$cabang}")
                     ->dailyAt('16:26');
        }

        Log::info('Scheduler initialized for all branches.');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
