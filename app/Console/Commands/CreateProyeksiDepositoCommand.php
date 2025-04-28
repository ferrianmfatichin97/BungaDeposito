<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProyeksiDeposito;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateProyeksiDepositoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:proyeksideposito';
    protected $description = 'Create Proyeksi Deposito from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deposits = $this->getDepositoData();

        foreach ($deposits as $deposit) {
            $total_bayar = $deposit->total_bayar;
            $total_bunga = $deposit->total_bunga;

            if ($deposit->pelengkap_pajak_bebas == 1) {
                $total_bayar = $total_bunga;
                $total_bunga = 0;
            }

            ProyeksiDeposito::create([
                'rek_deposito' => $deposit->rek_deposito,
                'nama_nasabah' => $deposit->nama_nasabah,
                'jangka_waktu' => $deposit->jangka_waktu,
                'nilai_bunga' => $deposit->nilai_bunga,
                'saldo_valuta_awal' => $deposit->saldo_valuta_awal,
                'bunga' => $deposit->bunga,
                'total_bunga' => $total_bunga,
                'total_pajak' => $deposit->total_pajak,
                'total_bayar' => $total_bayar,
                'tujuan_penggunaan' => $deposit->tujuan_penggunaan,
                'tanggal_bayar' => $deposit->tanggal_bayar,
                'jatuh_tempo' => $deposit->jatuh_tempo,
                'status' => $deposit->status,
                'dep_abp' => $deposit->dep_abp,
            ]);
        }

        $this->info('Deposit summary created successfully.');
    }

    private function getDepositoData()
    {
        $today = Carbon::tomorrow();
        $dayOfMonth = $today->day;

        if ($today->isSaturday()) {
            $daysToCheck = [
                $today->day,
                $today->copy()->addDay()->day,
                $today->copy()->addDays(2)->day
            ];
        } else {
            $daysToCheck = [$today->day];
        }
 
        //$daysToCheck = [26,27,28];


        return DB::connection('mysql_REMOTE')->table('data_deposito_master as d')
            ->join('data_nasabah_master as n', 'd.dep_nasabah', '=', 'n.nasabah_id')
            ->join('data_deposito_pelengkap as p', 'd.dep_rekening', '=', 'p.pelengkap_rekening')
            ->select(
                'd.dep_rekening AS rek_deposito',
                'n.nasabah_nama_lengkap AS nama_nasabah',
                'd.dep_jkw AS jangka_waktu',
                'd.dep_bunga_persen AS nilai_bunga',
                'd.dep_nilai_valuta AS saldo_valuta_awal',
                'd.dep_tabungan AS dep_tabungan',
                DB::raw('(d.dep_nilai_valuta * d.dep_bunga_persen / 100) AS bunga'),
                DB::raw('ROUND((d.dep_nilai_valuta * d.dep_bunga_persen / 100 / 12)) AS total_bunga'),
                DB::raw('ROUND(((d.dep_nilai_valuta * d.dep_bunga_persen / 100 / 12) * 0.2)) AS total_pajak'),
                DB::raw('ROUND((d.dep_nilai_valuta * d.dep_bunga_persen / 100 / 12)) - ROUND(((d.dep_nilai_valuta * d.dep_bunga_persen / 100 / 12) * 0.2)) AS total_bayar'),
                'd.dep_tujuanpeng AS tujuan_penggunaan',
                DB::raw('IFNULL(LPAD(DAY(d.dep_tgl_jthtempo), 2, "0"), "01") AS tanggal_bayar'),
                'd.dep_tgl_jthtempo AS jatuh_tempo',
                'd.dep_status AS status',
                'p.pelengkap_pajak_bebas AS pelengkap_pajak_bebas',
                'd.dep_abp AS dep_abp'
            )
            ->where('d.dep_status', 1)
            ->where('d.dep_tabungan', '')
            ->whereIn(DB::raw('DAY(d.dep_tgl_jthtempo)'), $daysToCheck)
            ->get();
    }
}
