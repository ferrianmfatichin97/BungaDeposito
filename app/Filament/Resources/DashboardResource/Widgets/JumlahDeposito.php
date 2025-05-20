<?php

namespace App\Filament\Resources\DashboardResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Filament\Support\Enums\IconPosition;

class JumlahDeposito extends BaseWidget
{
    protected function getDeposits()
    {
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
                DB::raw('DATE_FORMAT(d.dep_tgl_jthtempo, "%d-%m-%Y") AS jatuh_tempo'),
                'd.dep_tgl_jthtempo AS jatuh_tempo',
                'd.dep_status AS status',
                'p.pelengkap_pajak_bebas AS pelengkap_pajak_bebas',
                'd.dep_abp AS dep_abp'
            )
            ->where('d.dep_status', 1)
            ->where('d.dep_tabungan', '')
            ->get();
    }

    protected function getStats(): array
    {
        $deposits = $this->getDeposits();
        $totalNominal = $deposits->sum('saldo_valuta_awal');
        $date = now()->format('d-M-Y');
        $jatuhTempoHariIni = $deposits->filter(function ($item) {
            return \Carbon\Carbon::parse($item->jatuh_tempo)->isToday();
        })->count();

        return [
            Stat::make('Jumlah Deposan', $deposits->count())
                ->description('Jumlah Deposan Aktif per : ' . $date),
               // ->descriptionIcon('heroicon-m-arrow-trending-up', IconPosition::Before),
            Stat::make('Total Saldo Deposito', 'Rp. ' . number_format($totalNominal, 0, ',', '.'))
                ->description('Total Saldo Deposito')
                ->descriptionIcon('heroicon-m-arrow-trending-up'),
            Stat::make('Total Bunga Deposito', 'Rp. ' . number_format($deposits->sum('bunga'), 0, ',', '.'))
                ->description('Total Bunga Deposito')
                ->descriptionIcon('heroicon-m-arrow-trending-up'),
            Stat::make('Total Pajak Deposito', 'Rp. ' . number_format($deposits->sum('total_pajak'), 0, ',', '.'))
                ->description('Total Pajak Deposito')
                ->descriptionIcon('heroicon-m-arrow-trending-up'),
            Stat::make('Total Bayar Deposito', 'Rp. ' . number_format($deposits->sum('total_bayar'), 0, ',', '.'))
                ->description('Total Bayar Deposito')
                ->descriptionIcon('heroicon-m-arrow-trending-up'),
            Stat::make('Total Jatuh Tempo Bulan Ini', $deposits->where('jatuh_tempo', now()->format('d-m-Y'))->count())
                ->description('Total Jatuh Tempo Bulan Ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up'),
        ];
    }
}
