<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Remote_data_deposito_master extends Model
{
    protected $connection = 'mysql_REMOTE';
    protected $table = 'data_deposito_master';
    protected $primaryKey = 'dep_rekening';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    public function nasabah()
    {
        return $this->belongsTo(Remote_data_nasabah_master::class, 'dep_nasabah', 'nasabah_id');
    }

    public static function getDepositoData()
    {
        $today = Carbon::tomorrow();

        $dayOfMonth = $today->day;

        return DB::connection('mysql_REMOTE')->table('data_deposito_master as d')
            ->join('data_nasabah_master as n', 'd.dep_nasabah', '=', 'n.nasabah_id')
            ->select(
                'd.dep_rekening AS rek_deposito',
                'n.nasabah_nama_lengkap AS nama_nasabah',
                'd.dep_jkw AS jangka_waktu',
                'd.dep_bunga_persen AS nilai_bunga',
                'd.dep_nilai_valuta AS saldo_valuta_awal',
                DB::raw('(d.dep_nilai_valuta * d.dep_bunga_persen / 100) AS bunga'),
                DB::raw('ROUND((d.dep_nilai_valuta * d.dep_bunga_persen / 100 / 12)) AS total_bunga'),
                DB::raw('ROUND(((d.dep_nilai_valuta * d.dep_bunga_persen / 100 / 12) * 0.2)) AS total_pajak'),
                DB::raw('ROUND((d.dep_nilai_valuta * d.dep_bunga_persen / 100 / 12)) - ROUND(((d.dep_nilai_valuta * d.dep_bunga_persen / 100 / 12) * 0.2)) AS total_bayar'),
                'd.dep_tujuanpeng AS tujuan_penggunaan',
                DB::raw('LPAD(DAY(d.dep_tgl_valuta), 2, "0") AS tanggal_bayar'),
                'd.dep_status AS status'
            )
            ->where('d.dep_status', 1)
            //->where(DB::raw('DAY(d.dep_tgl_valuta)'), 27)
            ->where(DB::raw('DAY(d.dep_tgl_valuta)'), $dayOfMonth)
            ->get();
    }

    public static function createDepositoSummary()
{
    $deposits = self::getDepositoData();

    foreach ($deposits as $deposit) {
        ProyeksiDeposito::create([
            'rek_deposito' => $deposit->rek_deposito,
            'nama_nasabah' => $deposit->nama_nasabah,
            'jangka_waktu' => $deposit->jangka_waktu,
            'nilai_bunga' => $deposit->nilai_bunga,
            'saldo_valuta_awal' => $deposit->saldo_valuta_awal,
            'bunga' => $deposit->bunga,
            'total_bunga' => $deposit->total_bunga,
            'total_pajak' => $deposit->total_pajak,
            'total_bayar' => $deposit->total_bayar,
            'tujuan_penggunaan' => $deposit->tujuan_penggunaan,
            'tanggal_bayar' => $deposit->tanggal_bayar,
            'status' => $deposit->status,
        ]);
    }
}
}
