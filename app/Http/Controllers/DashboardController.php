<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with deposito data.
     *
     * @return \Illuminate\View\View
     */

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
                'd.dep_nominal as saldoawal',
                'd.dep_tgl_awal as v_tglbuka',
                'd.dep_tgl_jthtempo as v_jthtempo',
                'd.dep_bunga_persen as v_bunga',
                'd.dep_status as v_status'
            )
            ->where('d.dep_tgl_jthtempo', '<', DB::raw('CURDATE()'))
            ->get();

        // $dataDeposito = DB::connection('mysql_REMOTE')->table('data_deposito_master as d')
        //     ->join('data_nasabah_master as n', 'd.dep_nasabah', '=', 'n.nasabah_id')
        //     ->join('data_deposito_pelengkap as p', 'd.dep_rekening', '=', 'p.pelengkap_rekening')
        //     ->select(
        //         'd.dep_rekening AS rek_deposito',
        //         'n.nasabah_nama_lengkap AS nama_nasabah',
        //         'd.dep_jkw AS jangka_waktu',
        //         'd.dep_bunga_persen AS nilai_bunga',
        //         'd.dep_nilai_valuta AS saldo_valuta_awal',
        //         'd.dep_tabungan AS dep_tabungan',
        //         DB::raw('(d.dep_nilai_valuta * d.dep_bunga_persen / 100) AS bunga'),
        //         DB::raw('ROUND((d.dep_nilai_valuta * d.dep_bunga_persen / 100 / 12)) AS total_bunga'),
        //         DB::raw('ROUND(((d.dep_nilai_valuta * d.dep_bunga_persen / 100 / 12) * 0.2)) AS total_pajak'),
        //         DB::raw('ROUND((d.dep_nilai_valuta * d.dep_bunga_persen / 100 / 12)) - ROUND(((d.dep_nilai_valuta * d.dep_bunga_persen / 100 / 12) * 0.2)) AS total_bayar'),
        //         'd.dep_tujuanpeng AS tujuan_penggunaan',
        //         DB::raw('IFNULL(LPAD(DAY(d.dep_tgl_jthtempo), 2, "0"), "01") AS tanggal_bayar'),
        //         'd.dep_tgl_jthtempo AS jatuh_tempo',
        //         'd.dep_status AS status',
        //         'p.pelengkap_pajak_bebas AS pelengkap_pajak_bebas',
        //         'd.dep_abp AS dep_abp'
        //     )
        //     ->where('d.dep_status', 1)
        //     // ->where('d.dep_tabungan', '')
        //     // ->whereIn(DB::raw('DAY(d.dep_tgl_jthtempo)'), $daysToCheck)
        //     ->get();

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

        $kemarin = now()->subDay()->toDateString();

        $pengajuankemarin = DB::connection('mysql_REMOTE')
            ->table('view_deposito')
            ->whereDate('v_tglbuka', $kemarin)
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
            'dataDeposito',
            'totalDeposito',
            'totalDepositoKredit',
            'totalDepositoTabungan',
            'pengajuanHariIni',
            'pengajuankemarin',
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



    // public function viewDepositoNasabah(Request $request)
    // {
    //     $query = DB::connection('mysql_REMOTE')
    //         ->table('data_deposito_master as d')
    //         ->join('data_nasabah_master as n', 'd.dep_nasabah', '=', 'n.nasabah_id')
    //         ->select(
    //             'd.dep_nasabah as cif_nasabah',
    //             'n.nasabah_nama_lengkap as nama_nasabah',
    //             'd.dep_rekening as rekening_deposito',
    //             'd.dep_nominal as nominal_deposito',
    //             'd.dep_tgl_awal as tgl_buka_deposito',
    //             'd.dep_tgl_jthtempo as tgl_jatuh_tempo_deposito',
    //             'd.dep_bunga_persen as suku_bunga_deposito',
    //             'd.dep_status as status_rekening'
    //         );

    //     // Default: hanya status aktif (1)
    //     if ($request->filled('dep_status')) {
    //         $query->where('d.dep_status', $request->dep_status);
    //     } else {
    //         $query->where('d.dep_status', 1); // Default: Aktif
    //     }

    //     // Pencarian umum
    //     if ($request->filled('search')) {
    //         $search = $request->search;
    //         $query->where(function ($q) use ($search) {
    //             $q->where('d.dep_nasabah', 'like', "%$search%")
    //                 ->orWhere('n.nasabah_nama_lengkap', 'like', "%$search%")
    //                 ->orWhere('d.dep_rekening', 'like', "%$search%");
    //         });
    //     }

    //     // Filter tanggal jatuh tempo
    //     if ($request->filled('filter_date')) {
    //         $query->whereDate('d.dep_tgl_jthtempo', $request->filter_date);
    //     }

    //     $data = $query->get();
    //     //dd($data->first());

    //     return view('all-deposito', compact('data'));
    // }
    // function untuk menampilkan daftar deposito
    public function showDepositoList()
    {
        $dataDeposito = DB::connection('mysql_REMOTE')
            ->table('view_deposito')
            ->get();

        // $dataDeposito = DB::connection('mysql_REMOTE')
        //     ->table('data_deposito_master as d')
        //     ->join('data_nasabah_master as n', 'd.dep_nasabah', '=', 'n.nasabah_id')
        //     ->join('data_nasabah_orang as o', 'd.dep_nasabah', '=', 'o.nasabah_master')
        //     ->select(
        //         'd.dep_nasabah as v_cif1',
        //         'n.nasabah_nama_lengkap as v_nama1',
        //         'd.dep_rekening as v_rekening1',
        //         'o.nasabah_nomor_ktp as v_nomor_ktp',
        //         'd.dep_tgl_jthtempo as v_tgljtempo',
        //         'n.nasabah_nama_lengkap as v_nama',
        //         'n.nasabah_alamat as v_alamat',
        //         'n.nasabah_email as v_email',
        //         'd.dep_nominal as saldoawal',
        //         'd.dep_tgl_awal as v_tglbuka',
        //         'd.dep_tgl_jthtempo as v_jthtempo',
        //         'd.dep_bunga_persen as v_bunga',
        //         'd.dep_bunga_persen as v_sukubunga',
        //         'd.dep_status as v_status'
        //     )
        //     ->where('d.dep_tgl_jthtempo', '<', DB::raw('CURDATE()'))
        //     ->get();

            //dd($dataDeposito->first());

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
        $dataDeposito = DB::connection('mysql_REMOTE')
            ->table('view_deposito')
            ->whereDate('v_tglbuka', $today)
            ->get();

        $title = 'Pengajuan Deposito Hari Ini';
        $judul = 'Pengajuan Deposito : ' . \Carbon\Carbon::parse($today)->locale('id')->isoFormat('dddd, D MMMM Y');

        return view('deposito.pengajuan-hariini', compact('dataDeposito', 'title', 'judul'));
    }

    public function showPengajuanKemarin()
    {
        $today = now()->subDay()->toDateString(); // Tanggal kemarin

        $dataDeposito = DB::connection('mysql_REMOTE')
            ->table('view_deposito')
            ->whereDate('v_tglbuka', $today)
            ->get();

        $title = 'Pengajuan Deposito Kemarin';
        $judul = 'Data Pengajuan Deposito : ' . \Carbon\Carbon::parse($today)->locale('id')->isoFormat('dddd, D MMMM Y');

        return view('deposito.pengajuan-hariini', compact('dataDeposito', 'title', 'judul'));
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
