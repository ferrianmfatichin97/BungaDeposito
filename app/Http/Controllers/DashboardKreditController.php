<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DashboardKreditController extends Controller
{
    public function index()
    {
        $sql = "
            SELECT
                data_kantor_master.kantor_sandi AS v_kantor1,
                data_kantor_master.kantor_kode AS v_kantor2,
                data_nasabah_master.nasabah_id AS v_cif1,
                data_nasabah_master.nasabah_alternatif AS v_cif2,
                data_nasabah_master.nasabah_nama_lengkap AS v_nama1,
                data_nasabah_master.nasabah_alamat AS v_alamat1,
                data_nasabah_master.nasabah_kelurahan AS v_kelurahan,
                data_nasabah_master.nasabah_kecamatan AS v_kecamatan,
                CONCAT(data_nasabah_master.nasabah_alamat, ', ', data_nasabah_master.nasabah_kelurahan, ', ', data_nasabah_master.nasabah_kecamatan) AS v_alamat,
                data_nasabah_master.nasabah_dati2 AS v_kota,
                data_nasabah_master.nasabah_kodepos AS v_kodepos,
                data_nasabah_master.nasabah_telepon AS v_telepon,
                data_nasabah_master.nasabah_email AS v_email,
                data_nasabah_orang.nasabah_lahir_kota AS v_lahir_kota,
                data_nasabah_orang.nasabah_lahir_tanggal AS v_lahir_tanggal,
                IF(data_nasabah_orang.nasabah_kelamin = '1', 'L', 'P') AS v_kelamin,
                data_nasabah_orang.nasabah_namaibu AS v_namaibu,
                data_nasabah_orang.nasabah_pasangan AS v_pasangan,
                data_nasabah_orang.nasabah_nomor_ktp AS v_nomor_ktp,
                data_nasabah_orang.nasabah_nomor_paspor AS v_nomor_paspor,
                data_nasabah_orang.nasabah_nomor_npwp AS v_nomor_npwp,
                data_nasabah_orang.nasabah_expire_ktp AS v_expire_ktp,
                data_nasabah_orang.nasabah_expire_paspor AS v_expire_paspor,
                data_kredit_master.kre_rekening AS v_rekening1,
                data_kredit_master.kre_alternatif AS v_rekening2,
                data_kredit_master.kre_spk_awal AS v_spk_awal,
                data_kredit_master.kre_spk_akhir AS v_spk_akhir,
                data_kredit_master.kre_tgl_akadawal AS v_tgl_akadawal,
                data_kredit_master.kre_tgl_akadakhir AS v_tgl_spkakhir,
                data_kredit_master.kre_plafon1 AS v_plafon,
                data_kredit_master.kre_tgl_realisasi AS v_tgl_realisasi,
                data_kredit_master.kre_jkw AS v_jkw,
                data_kredit_master.kre_tgl_jthtempo AS v_tgl_jthtempo,
                data_kredit_master.kre_status,
                IF(data_kredit_master.kre_status = 2, '', IF(data_kredit_master.kre_status = '3', 'LUNAS', IF(data_kredit_master.kre_status = '4', 'HAPUSBUKU', data_kredit_master.kre_status))) AS v_kondisi,
                IF(data_kredit_master.kre_status <> 2, data_kredit_master.kre_close_date, '') AS v_tgl_kondisi,
                data_kredit_pelengkap.pelengkap_iddokumen AS v_iddokumen,
                data_kredit_pelengkap.pelengkap_sid_jenisfasilitas AS v_sid_jenisfasilitas,
                data_kredit_pelengkap.pelengkap_sid_golkredit AS v_sid_golkredit,
                data_kredit_pelengkap.pelengkap_sid_sifatkredit AS v_sid_sifatkredit,
                data_kredit_pelengkap.pelengkap_sid_jenispenggunaan AS v_sid_jenispenggunaan,
                data_kredit_pelengkap.pelengkap_sid_sektorekonomi AS v_sid_sektorekonomi,
                data_kredit_pelengkap.pelengkap_sid_proyek_lokasi AS v_sid_proyeklokasi,
                data_kredit_pelengkap.pelengkap_sid_proyek_nilai AS v_sid_proyeknilai,
                data_kredit_pelengkap.pelengkap_bi_goldebitur AS v_bi_goldebitur,
                data_kredit_pelengkap.pelengkap_bi_sifatpinjaman AS v_bi_sifatpinjaman,
                data_kredit_pelengkap.pelengkap_bi_jenispenggunaan AS v_bi_jenispenggunaan,
                data_kredit_pelengkap.pelengkap_bi_sektorekonomi AS v_bi_sektorekonomi,
                data_kredit_pelengkap.pelengkap_sumber_dana2 AS v_bi_sumberdana,
                data_kredit_pelengkap.pelengkap_kode_kolektor AS v_kolektor,
                data_kredit_pelengkap.pelengkap_kode_wilayah AS v_instansi,
                data_kredit_pelengkap.pelengkap_kode_wilayah2 AS v_wilayah,
                data_kredit_pelengkap.pelengkap_asuransi_kode AS v_asuransi,
                data_kredit_pelengkap.pelengkap_notaris_kode AS v_notaris,
                data_kredit_sidbpr.sidbpr_din AS v_din,
                data_kredit_sidbpr.sidbpr_iddebitur AS v_iddebitur,
                data_kredit_sidbpr.sidbpr_idfasilitas AS v_idfasilitas,
                data_kredit_sidbpr.sidbpr_iddebfas AS v_iddebfas
            FROM data_kantor_master
            JOIN data_kredit_master ON data_kantor_master.kantor_kode = data_kredit_master.kre_kantor
            JOIN data_kredit_pelengkap ON data_kredit_master.kre_rekening = data_kredit_pelengkap.pelengkap_rekening
            JOIN data_kredit_sidbpr ON data_kredit_master.kre_rekening = data_kredit_sidbpr.sidbpr_rekening
            JOIN data_nasabah_master ON data_nasabah_master.nasabah_id = data_kredit_sidbpr.sidbpr_nasabah
            JOIN data_nasabah_orang ON data_nasabah_orang.nasabah_master = data_kredit_sidbpr.sidbpr_nasabah
            WHERE data_kredit_master.kre_status > 1 AND data_kredit_master.kre_status <> 9
        ";

        $data = DB::connection('mysql_REMOTE')->select($sql);

        // Lanjutkan logika pengolahan data di PHP
        $totalKredit = count($data);
        $today = now()->toDateString();
        $bulan = now();

        $pengajuanHariIni = collect($data)->where('v_tgl_realisasi', $today)->count();
        $pengajuanBulanIni = collect($data)->filter(function ($row) use ($bulan) {
            return date('Y-m', strtotime($row->v_tgl_realisasi)) === $bulan->format('Y-m');
        })->count();

        $nominalHariIni = collect($data)->filter(function ($row) use ($today) {
            return $row->v_tgl_realisasi === $today;
        })->sum('v_plafon');

        $nominalBulanIni = collect($data)->filter(function ($row) use ($bulan) {
            return date('Y-m', strtotime($row->v_tgl_realisasi)) === $bulan->format('Y-m');
        })->sum('v_plafon');

        $nasabahIDs = collect($data)->pluck('v_cif1')->unique()->toArray();

        $totalNasabahDenganKredit = DB::connection('mysql_REMOTE')
            ->table('view_deposito')
            ->whereIn('v_cif1', $nasabahIDs)
            ->distinct('v_cif1')
            ->count('v_cif1');

        $totalNominalKredit = collect($data)->sum('v_plafon');

        return view('dashboard.kredit', [
            'totalKredit' => $totalKredit,
            'pengajuanHariIni' => $pengajuanHariIni,
            'pengajuanBulanIni' => $pengajuanBulanIni,
            'nominalHariIni' => $nominalHariIni,
            'nominalBulanIni' => $nominalBulanIni,
            'totalNasabahDenganKredit' => $totalNasabahDenganKredit,
            'totalNominal' => $totalNominalKredit,
            'today' => $today,
            'bulan' => $bulan,
        ]);
    }
}
