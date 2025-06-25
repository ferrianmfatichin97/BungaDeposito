<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    // public function index()
    // {
    //     $deposits = DB::connection('mysql_REMOTE')->table('data_deposito_master as d')
    //         ->join('data_nasabah_master as n', 'd.dep_nasabah', '=', 'n.nasabah_id')
    //         ->join('data_deposito_pelengkap as p', 'd.dep_rekening', '=', 'p.pelengkap_rekening')
    //         ->select(
    //             'd.dep_nilai_valuta',
    //             DB::raw('(d.dep_nilai_valuta * d.dep_bunga_persen / 100) AS bunga'),
    //             DB::raw('ROUND((d.dep_nilai_valuta * d.dep_bunga_persen / 100 / 12)) AS total_bunga'),
    //             DB::raw('ROUND(((d.dep_nilai_valuta * d.dep_bunga_persen / 100 / 12) * 0.2)) AS total_pajak'),
    //             DB::raw('ROUND((d.dep_nilai_valuta * d.dep_bunga_persen / 100 / 12)) - ROUND(((d.dep_nilai_valuta * d.dep_bunga_persen / 100 / 12) * 0.2)) AS total_bayar'),
    //             'd.dep_tgl_jthtempo'
    //         )
    //         ->where('d.dep_status', 1)
    //         ->where('d.dep_tabungan', '')
    //         ->get();

    //     $topNasabah = DB::connection('mysql_REMOTE')->table('data_deposito_master as d')
    //         ->join('data_nasabah_master as n', 'd.dep_nasabah', '=', 'n.nasabah_id')
    //         ->where('d.dep_status', 1)
    //         ->where('d.dep_tabungan', '')
    //         ->select(
    //             'n.nasabah_nama_lengkap',
    //             DB::raw('SUM(d.dep_nilai_valuta) as total_valuta')
    //         )
    //         ->groupBy('n.nasabah_nama_lengkap')
    //         ->orderByDesc('total_valuta')
    //         ->limit(10)
    //         ->get();

    //     return view('dashboard', [
    //         'jumlahDeposan' => $deposits->count(),
    //         'totalSaldo' => $deposits->sum('dep_nilai_valuta'),
    //         'totalBunga' => $deposits->sum('bunga'),
    //         'totalPajak' => $deposits->sum('total_pajak'),
    //         'totalBayar' => $deposits->sum('total_bayar'),
    //         'jatuhTempoBulanIni' => $deposits->filter(function ($item) {
    //             $tanggal = \Carbon\Carbon::parse($item->dep_tgl_jthtempo);
    //             return $tanggal->isSameMonth(now()) && $tanggal->isSameYear(now());
    //         })->count(),
    //         'totalBayarHariIni' => $deposits->filter(function ($item) {
    //             $tanggal = \Carbon\Carbon::parse($item->dep_tgl_jthtempo);
    //             return $tanggal->day === now()->day && $tanggal->between(now(), now()->addYear());
    //         })->sum('total_bayar'),

    //         'totalBayarHariEsok' => $deposits->filter(function ($item) {
    //             $tanggal = \Carbon\Carbon::parse($item->dep_tgl_jthtempo);
    //             return $tanggal->day === now()->addDay()->day && $tanggal->between(now(), now()->addYear());
    //         })->sum('total_bayar'),

    //         'tanggal' => now()->format('d-M-Y'),
    //         'topNasabah' => $topNasabah,
    //     ]);
    // }

    public function index()
    {
        $dataDeposito = DB::connection('mysql_REMOTE')
            ->table('data_deposito_master as d')
            ->join('data_nasabah_master as n', 'd.dep_nasabah', '=', 'n.nasabah_id')
            ->select(
                'd.dep_nasabah as v_cif1',
                'n.nasabah_nama_lengkap as v_nama',
                'n.nasabah_alamat as v_alamat',
                'n.nasabah_email as v_email',
                // 'n.nasabah_ktp as v_nomor_ktp', // ganti ini sesuai hasil real
                'd.dep_nominal as saldoawal',
                'd.dep_tgl_awal as v_tglbuka',
                'd.dep_tgl_jthtempo as v_jthtempo',
                'd.dep_bunga_persen as v_bunga',
                'd.dep_status as v_status'
            )
            ->get();

        $totalDeposito = $dataDeposito->unique('v_cif1')->count();
        $totalNominal = $dataDeposito->sum('saldoawal');

        // Hitung pengajuan hari ini
        $today = now()->toDateString();
        //$today = now()->subDay()->toDateString(); // kemarin
        //$today = now()->addDay()->toDateString(); // besok

        $pengajuanHariIni = DB::connection('mysql_REMOTE')
            ->table('view_deposito')
            ->whereDate('v_tglbuka', $today)
            ->count();

        // Hitung pengajuan bulan ini
        $bulan = now();
        //$bulan = now()->subMonth(); // bulan lalu
        //$bulan = now()->addMonth(); // bulan depan

        $pengajuanBulanIni = DB::connection('mysql_REMOTE')
            ->table('view_deposito')
            ->whereMonth('v_tglbuka', $bulan->month)
            ->whereYear('v_tglbuka', $bulan->year)
            ->count();

        $nasabahIDs = $dataDeposito->pluck('v_cif1')->unique()->toArray();
        $totalDepositoKredit = DB::connection('mysql_REMOTE')
            ->table('view_kredit')
            ->whereIn('v_cif1', $nasabahIDs)
            ->distinct('v_cif1')->count('v_cif1');

        $totalDepositoTabungan = DB::connection('mysql_REMOTE')
            ->table('view_tabungan')
            ->whereIn('v_cif1', $nasabahIDs)
            ->distinct('v_cif1')->count('v_cif1');

        $allTabungan = DB::connection('mysql_REMOTE')
            ->table('view_tabungan')
            ->whereIn('v_cif1', $nasabahIDs)
            ->get()
            ->groupBy('v_cif1');

        $allKredit = DB::connection('mysql_REMOTE')
            ->table('view_kredit')
            ->whereIn('v_cif1', $nasabahIDs)
            ->get()
            ->groupBy('v_cif1');

        //dd( $allKredit->first());

        // Gabungkan ke dataDeposito
        foreach ($dataDeposito as $d) {
            $d->tabungan = $allTabungan[$d->v_cif1] ?? collect();
            $d->kredit = $allKredit[$d->v_cif1] ?? collect();
        }

        return view('dashboard-deposito', compact(
            // return view('deposito.dashboard', compact(
            'dataDeposito',
            'totalDeposito',
            'totalDepositoKredit',
            'totalDepositoTabungan',
            'pengajuanHariIni',
            'pengajuanBulanIni',
            'totalNominal',
            'today',
            'bulan'
        ));
    }

    public function deposito()
    {
        return view('dashboard-deposito');
    }



    public function viewDepositoNasabah(Request $request)
    {
        $query = DB::connection('mysql_REMOTE')
            ->table('data_deposito_master as d')
            ->join('data_nasabah_master as n', 'd.dep_nasabah', '=', 'n.nasabah_id')
            ->select(
                'd.dep_nasabah as cif_nasabah',
                'n.nasabah_nama_lengkap as nama_nasabah',
                'd.dep_rekening as rekening_deposito',
                'd.dep_nominal as nominal_deposito',
                'd.dep_tgl_awal as tgl_buka_deposito',
                'd.dep_tgl_jthtempo as tgl_jatuh_tempo_deposito',
                'd.dep_bunga_persen as suku_bunga_deposito',
                'd.dep_status as status_rekening'
            );

        // Default: hanya status aktif (1)
        if ($request->filled('dep_status')) {
            $query->where('d.dep_status', $request->dep_status);
        } else {
            $query->where('d.dep_status', 1); // Default: Aktif
        }

        // Pencarian umum
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('d.dep_nasabah', 'like', "%$search%")
                    ->orWhere('n.nasabah_nama_lengkap', 'like', "%$search%")
                    ->orWhere('d.dep_rekening', 'like', "%$search%");
            });
        }

        // Filter tanggal jatuh tempo
        if ($request->filled('filter_date')) {
            $query->whereDate('d.dep_tgl_jthtempo', $request->filter_date);
        }

        $data = $query->get();
        //dd($data->first());

        return view('all-deposito', compact('data'));
    }
    // function untuk menampilkan daftar deposito
    public function showDepositoList()
    {
        $dataDeposito = DB::connection('mysql_REMOTE')
            ->table('view_deposito')
            ->get();

        $nasabahIDs = $dataDeposito->pluck('v_cif1')->unique()->toArray();

        // Ambil tabungan dan kredit dari remote juga
        $tabungan = DB::connection('mysql_REMOTE')
            ->table('view_tabungan')
            ->whereIn('v_cif1', $nasabahIDs)
            ->get()
            ->groupBy('v_cif1');

        $kredit = DB::connection('mysql_REMOTE')
            ->table('view_kredit')
            ->whereIn('v_cif1', $nasabahIDs)
            ->get()
            ->groupBy('v_cif1');


        $dataDeposito = collect($dataDeposito)->map(function ($item) use ($tabungan, $kredit) {
            $item = (array) $item;
            $item['tabungan'] = $tabungan[$item['v_cif1']] ?? [];
            $item['kredit'] = $kredit[$item['v_cif1']] ?? [];
            return (object) $item;
        });

        //dd($dataDeposito->first());

        return view('deposito.list', compact('dataDeposito'));
    }
    public function showDepositoKredit()
    {
        $dataDeposito = DB::connection('mysql_REMOTE')
            ->table('view_deposito')
            ->get();

        $nasabahIDs = $dataDeposito->pluck('v_cif1')->unique()->toArray();

        $kredit = DB::connection('mysql_REMOTE')
            ->table('view_kredit')
            ->whereIn('v_cif1', $nasabahIDs)
            ->get()
            ->groupBy('v_cif1');

        // Filter hanya nasabah yg punya kredit
        $filtered = $dataDeposito->filter(function ($item) use ($kredit) {
            return isset($kredit[$item->v_cif1]);
        });

        // Sisipkan data kredit
        $filtered->transform(function ($item) use ($kredit) {
            $item->kredit = $kredit[$item->v_cif1] ?? [];
            return $item;
        });
        //dd($filtered->first());

        return view('deposito.kredit', ['dataDeposito' => $filtered]);
    }

    public function showDepositoTabungan()
    {
        $dataDeposito = DB::connection('mysql_REMOTE')
            ->table('view_deposito')
            ->get();

        $nasabahIDs = $dataDeposito->pluck('v_cif1')->unique()->toArray();

        $tabungan = DB::connection('mysql_REMOTE')
            ->table('view_tabungan')
            ->whereIn('v_cif1', $nasabahIDs)
            ->get()
            ->groupBy('v_cif1');

        //dd($tabungan->first());

        // Ambil semua nomor rekening tabungan
        $rekeningTabungan = $tabungan->flatten()->pluck('v_rekening1')->unique()->toArray();
        //dd($rekeningTabungan);
        // Ambil data dari data_tabungan_master TANPA fungsi
        $tabunganMaster = DB::connection('mysql_REMOTE')
            ->table('data_tabungan_master')
            ->select([
                'tab_rekening as myRekening',
                'tab_nasabah',
                // Tambahkan kolom lain jika perlu
            ])
            ->whereIn('tab_rekening', $rekeningTabungan)
            ->where('tab_status', 1)
            ->get()
            ->keyBy('myRekening');

        //dd($tabunganMaster->first());

        // Gabungkan
        $filtered = $dataDeposito->filter(function ($item) use ($tabungan) {
            return isset($tabungan[$item->v_cif1]);
        });

        $filtered->transform(function ($item) use ($tabungan, $tabunganMaster) {
            $rekList = $tabungan[$item->v_cif1] ?? collect();
            //dd($rekList->first());
            $item->tabungan = $rekList->map(function ($rek) use ($tabunganMaster) {
                $rek->detail = $tabunganMaster[$rek->v_rekening1] ?? null;
                return $rek;
            });
            return $item;
        });


        //dd($filtered->first());
        return view('deposito.tabungan', ['dataDeposito' => $filtered]);
    }

    public function showPengajuanHariIni()
    {
        $today = now()->toDateString();
        //$today = now()->subDay()->toDateString(); // kemarin
        //$today = now()->addDay()->toDateString(); // besok
        $dataDeposito = DB::connection('mysql_REMOTE')
            ->table('view_deposito')
            ->whereDate('v_tglbuka', $today)
            ->get();

        //dd($dataDeposito->first());

        return view('deposito.pengajuan-hariini', compact('dataDeposito'));
    }
    public function showPengajuanBulanIni()
    {
        $now = now();
        //$now = now()->subMonth(); // bulan lalu
        //$now = now()->addMonth(); // bulan depan
        $dataDeposito = DB::connection('mysql_REMOTE')
            ->table('view_deposito')
            ->whereMonth('v_tglbuka', $now->month)
            ->whereYear('v_tglbuka', $now->year)
            ->get();

        return view('deposito.pengajuan-bulanini', compact('dataDeposito'));
    }
}
