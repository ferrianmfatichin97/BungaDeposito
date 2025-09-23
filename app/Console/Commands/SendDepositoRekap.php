<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendDepositoRekap extends Command
{
    protected $signature = 'wa:deposito-rekap {--hari=7} {--kodeCabang=00}';
    protected $description = 'Kirim rekap deposito jatuh tempo via WA dengan tabel data';

    public function handle()
    {
        $hari = (int) $this->option('hari');
        $kodeCabang = $this->option('kodeCabang');

        // SQL query terbaru
        $sql = "
            SELECT
                d.dep_rekening,
                n.nasabah_nama_lengkap,
                d.dep_nominal,
                d.dep_tgl_jthtempo,
                CASE d.dep_ppj_sistem
                    WHEN 1 THEN 'Non-ARO'
                    WHEN 2 THEN 'ARO'
                    ELSE 'Lainnya'
                END AS jenis_rollover,
                CASE k.kantor_kode
                    WHEN '00' THEN 'KPM'
                    WHEN '01' THEN 'KC KPO'
                    WHEN '02' THEN 'KC BOGOR'
                    WHEN '03' THEN 'KC DEPOK'
                    WHEN '04' THEN 'KC TANGERANG'
                    WHEN '05' THEN 'KC JAKARTA TIMUR'
                    WHEN '06' THEN 'KC KARAWANG'
                    WHEN '07' THEN 'KC CIKARANG'
                    WHEN '08' THEN 'KC PURWOKERTO'
                    ELSE 'Cabang Lain'
                END AS nama_cabang
            FROM data_deposito_master d
            JOIN data_nasabah_master n ON d.dep_nasabah = n.nasabah_id
            JOIN data_kantor_master k ON d.dep_kantor = k.kantor_kode
            WHERE d.dep_status = 1
              AND d.dep_close_date = '2099-12-31'
              AND DATE(d.dep_tgl_jthtempo) = DATE_ADD(CURDATE(), INTERVAL {$hari} DAY)
        ";

        if ($kodeCabang !== '00') {
            $sql .= " AND k.kantor_kode = '{$kodeCabang}'";
        }

        $sql .= " ORDER BY d.dep_tgl_jthtempo";

        $depositos = DB::connection('mysql_REMOTE')->select($sql);

        if (empty($depositos)) {
            $this->info("Tidak ada deposito jatuh tempo dalam {$hari} hari untuk cabang {$kodeCabang}.");
            return 0;
        }

                            $waNumbers = DB::table('deposito_reminders')
            ->where('aktif', 1)
            ->where('hari_sebelum_jt', $hari)
            ->whereNotNull('wa_tujuan')
            ->when($kodeCabang !== '00', function ($q) use ($kodeCabang) {
                $q->where('kode_cabang', $kodeCabang);
            })
            ->pluck('wa_tujuan');

        if ($waNumbers->isEmpty()) {
            $this->warn("Tidak ada nomor WA untuk pengiriman.");
            return 0;
        }

        // Format periode
        $startDate = Carbon::now()->format('d M Y');
        $endDate = Carbon::now()->addDays($hari)->format('d M Y');

        // Buat header pesan
        $message = "Selamat pagi, teman-teman CS,\n\n";
        $message .= "Berikut saya lampirkan link untuk data deposito yang akan jatuh tempo dalam {$hari} hari ke depan, yaitu pada periode {$startDate} s.d {$endDate}:\n\n";

        // Buat tabel isi data dengan Nama Kantor
        $message .= "No | Nama Nasabah         | Rekening     | Nominal       | Jatuh Tempo | Jenis     | Kantor\n";
        $message .= "---|--------------------|------------|---------------|------------|----------|--------\n";

        foreach ($depositos as $i => $d) {
            $message .= sprintf(
                "%-2d | %-18s | %-12s | Rp %-12s | %-10s | %-8s | %s\n",
                $i + 1,
                substr($d->nasabah_nama_lengkap, 0, 18),
                $d->dep_rekening,
                number_format($d->dep_nominal, 0, ',', '.'),
                Carbon::parse($d->dep_tgl_jthtempo)->format('d M y'),
                $d->jenis_rollover,
                $d->nama_cabang
            );
        }

        // Tambahkan instruksi
        $message .= "\nMohon untuk:\n";
        $message .= "1. Melakukan follow-up kepada nasabah terkait deposito yang bersangkutan.\n";
        $message .= "2. Mengisi status tindak lanjut pada link, apakah:\n";
        $message .= "- Akan dicairkan, atau\n";
        $message .= "- Akan diperpanjang\n";
        $message .= "- Jika ada perubahan suku bunga terbaru, mohon diupdate juga di kolom yang tersedia.\n\n";
        $message .= "Terima kasih atas kerja samanya 🙏";

        // Kirim WA ke semua nomor
        foreach ($waNumbers as $number) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => config('services.fonnte.token'),
                ])->asMultipart()->post('https://api.fonnte.com/send', [
                    'target'      => $number,
                    'message'     => $message,
                    'countryCode' => '62',
                ]);

                if ($response->successful()) {
                    $this->info("WA terkirim ke {$number}");
                } else {
                    $this->error("Gagal kirim WA ke {$number}: " . $response->body());
                    Log::error('WA gagal dikirim', ['wa_tujuan' => $number, 'response' => $response->body()]);
                }
            } catch (\Exception $e) {
                $this->error("Error WA ke {$number}: " . $e->getMessage());
                Log::error('WA Exception', ['wa_tujuan' => $number, 'error' => $e->getMessage()]);
            }
        }

        $this->info("Selesai mengirim rekap WA.");
        return 0;
    }
}
