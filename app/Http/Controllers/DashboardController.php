<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $deposits = DB::connection('mysql_REMOTE')->table('data_deposito_master as d')
            ->join('data_nasabah_master as n', 'd.dep_nasabah', '=', 'n.nasabah_id')
            ->join('data_deposito_pelengkap as p', 'd.dep_rekening', '=', 'p.pelengkap_rekening')
            ->select(
                'd.dep_nilai_valuta',
                DB::raw('(d.dep_nilai_valuta * d.dep_bunga_persen / 100) AS bunga'),
                DB::raw('ROUND((d.dep_nilai_valuta * d.dep_bunga_persen / 100 / 12)) AS total_bunga'),
                DB::raw('ROUND(((d.dep_nilai_valuta * d.dep_bunga_persen / 100 / 12) * 0.2)) AS total_pajak'),
                DB::raw('ROUND((d.dep_nilai_valuta * d.dep_bunga_persen / 100 / 12)) - ROUND(((d.dep_nilai_valuta * d.dep_bunga_persen / 100 / 12) * 0.2)) AS total_bayar'),
                'd.dep_tgl_jthtempo'
            )
            ->where('d.dep_status', 1)
            ->where('d.dep_tabungan', '')
            ->get();

        $topNasabah = DB::connection('mysql_REMOTE')->table('data_deposito_master as d')
            ->join('data_nasabah_master as n', 'd.dep_nasabah', '=', 'n.nasabah_id')
            ->where('d.dep_status', 1)
            ->where('d.dep_tabungan', '')
            ->select(
                'n.nasabah_nama_lengkap',
                DB::raw('SUM(d.dep_nilai_valuta) as total_valuta')
            )
            ->groupBy('n.nasabah_nama_lengkap')
            ->orderByDesc('total_valuta')
            ->limit(10)
            ->get();

        return view('dashboard', [
            'jumlahDeposan' => $deposits->count(),
            'totalSaldo' => $deposits->sum('dep_nilai_valuta'),
            'totalBunga' => $deposits->sum('bunga'),
            'totalPajak' => $deposits->sum('total_pajak'),
            'totalBayar' => $deposits->sum('total_bayar'),
            'jatuhTempoBulanIni' => $deposits->filter(function ($item) {
                $tanggal = \Carbon\Carbon::parse($item->dep_tgl_jthtempo);
                return $tanggal->isSameMonth(now()) && $tanggal->isSameYear(now());
            })->count(),
            'totalBayarHariIni' => $deposits->filter(function ($item) {
                $tanggal = \Carbon\Carbon::parse($item->dep_tgl_jthtempo);
                return $tanggal->day === now()->day && $tanggal->between(now(), now()->addYear());
            })->sum('total_bayar'),

            'totalBayarHariEsok' => $deposits->filter(function ($item) {
                $tanggal = \Carbon\Carbon::parse($item->dep_tgl_jthtempo);
                return $tanggal->day === now()->addDay()->day && $tanggal->between(now(), now()->addYear());
            })->sum('total_bayar'),

            'tanggal' => now()->format('d-M-Y'),
            'topNasabah' => $topNasabah,
        ]);
    }
}
