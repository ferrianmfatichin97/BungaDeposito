<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Remote_data_deposito_master;
use App\Models\PayrollDeposito;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Updatejatuhtempo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:updatejatuhtempo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status of PayrollDeposito based on Remote_data_deposito_master jatuhtempo';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $today = '2025-03-01';
        $data = PayrollDeposito::withDepositoToday($today)->get();

        print_r($data);
        //log::info('Data Jatuh Tempo',$data);

        // $remoteData = Remote_data_deposito_master::where('dep_tgl_jthtempo', '=', $today)->get();

        // if ($remoteData->isNotEmpty()) {

        //     $ids = $remoteData->pluck('id');
        //     $updatedCount = PayrollDeposito::whereIn('id', $ids)
        //         ->update(['status' => 'TUTUP']);

        //     $this->info("Updated $updatedCount records in PayrollDeposito.");
        // } else {
        //     $this->info('No records found to update in PayrollDeposito.');
        // }

        // log::info([
        //     'hari' => $today,
        //     'remoteData' => $remoteData,
        //     'Ids' => $ids,
        //     // 'UpdateCount' => $updatedCount
        // ]);
    }
}
