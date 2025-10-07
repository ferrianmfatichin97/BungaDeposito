<?php

namespace App\Console\Commands;

use App\Mail\DepositoReminderMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\DepositoReminder;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Carbon\Carbon;
use App\Models\ReminderLog;

class SendDepositoReminders extends Command
{
    protected $signature = 'reminder:deposito';
    protected $description = 'Kirim reminder deposito via Email & WhatsApp';

    public function handle()
    {

        $reminders = DepositoReminder::where('aktif', 1)->get();
        Log::info('Memulai proses pengiriman reminder deposito', ['count' => $reminders->count()]);

        foreach ($reminders as $reminder) {
            if (empty($reminder->hari_sebelum_jt)) {
                Log::warning("Reminder ID {$reminder->id} dilewati karena hari_sebelum_jt kosong");
                continue;
            }

            $depositos = collect($this->ambilDeposito($reminder));

            if ($depositos->isEmpty()) {
                Log::info("Tidak ada deposito untuk reminder ID {$reminder->id} (H-{$reminder->hari_sebelum_jt})");
                continue;
            }

            Log::info("Ditemukan {$depositos->count()} deposito untuk reminder ID {$reminder->id}");

            // --- Kirim Email ---
            if ($reminder->email_tujuan) {
                try {
                    Mail::to($reminder->email_tujuan)
                        ->send(new DepositoReminderMail($depositos, $reminder));

                    ReminderLog::create([
                        'reminder_id' => $reminder->id,
                        'kode_cabang' => $reminder->kode_cabang,
                        'channel'     => 'email',
                        'tujuan'      => $reminder->email_tujuan,
                        'status'      => 'success',
                        'count'       => $depositos->count(),
                        'message'     => 'Email reminder deposito',
                        'response'    => 'Email sent successfully',
                    ]);


                    $this->info("Email terkirim ke {$reminder->email_tujuan}");
                    Log::info("Email sukses dikirim ke {$reminder->email_tujuan}", [
                        'reminder_id' => $reminder->id,
                        'count'       => $depositos->count(),
                    ]);
                } catch (\Exception $e) {
                    ReminderLog::create([
                        'reminder_id' => $reminder->id,
                        'channel'     => 'email',
                        'tujuan'      => $reminder->email_tujuan,
                        'status'      => 'failed',
                        'message'     => 'Email gagal dikirim',
                        'response'    => $e->getMessage(),
                    ]);

                    $this->error("Error Email: " . $e->getMessage());
                    Log::error('Email gagal dikirim', [
                        'email_tujuan' => $reminder->email_tujuan,
                        'error'        => $e->getMessage(),
                    ]);
                }
            }

            // --- Kirim WhatsApp ---
            if ($reminder->wa_tujuan) {
                $message = $this->formatMessageSummaryWA($depositos, $reminder);

                try {
                    $response = Http::withHeaders([
                        'Authorization' => config('services.fonnte.token'),
                    ])->asMultipart()->post('https://api.fonnte.com/send', [
                        'target'      => $reminder->wa_tujuan,
                        'message'     => $message,
                        'countryCode' => '62',
                    ]);

                    $status = $response->successful() ? 'success' : 'failed';

                    ReminderLog::create([
                        'reminder_id' => $reminder->id,
                        'kode_cabang' => $reminder->kode_cabang,
                        'channel'     => 'wa',
                        'tujuan'      => $reminder->wa_tujuan,
                        'status'      => $response->successful() ? 'success' : 'failed',
                        'count'       => $depositos->count(),
                        'message'     => 'WA Reminder Deposito',
                        'response'    => $response->body(),
                    ]);


                    if ($response->successful()) {
                        $this->info("WA terkirim ke {$reminder->wa_tujuan}");
                        Log::info('WA Reminder Deposito (summary)', [
                            'wa_tujuan' => $reminder->wa_tujuan,
                            'count'     => $depositos->count(),
                            'response'  => $response->body(),
                        ]);
                    } else {
                        $this->error("Gagal kirim WA: " . $response->body());
                        Log::error('WA gagal dikirim (summary)', [
                            'wa_tujuan' => $reminder->wa_tujuan,
                            'response'  => $response->body(),
                        ]);
                    }
                } catch (\Exception $e) {
                    ReminderLog::create([
                        'reminder_id' => $reminder->id,
                        'channel'     => 'wa',
                        'tujuan'      => $reminder->wa_tujuan,
                        'status'      => 'failed',
                        'message'     => 'WA Exception',
                        'response'    => $e->getMessage(),
                    ]);

                    $this->error("Error WA: " . $e->getMessage());
                    Log::error('WA Exception (summary)', [
                        'wa_tujuan' => $reminder->wa_tujuan,
                        'error'     => $e->getMessage(),
                    ]);
                }
            }
        }

        return SymfonyCommand::SUCCESS;
    }

    /**
     * Ambil data deposito dari DB remote
     */
    private function ambilDeposito($reminder)
    {
        $hari = (int) $reminder->hari_sebelum_jt;
        $kodeCabang = $reminder->kode_cabang;

        $sql = "
        SELECT
            d.dep_rekening AS no_rekening,
            n.nasabah_nama_lengkap AS nama_nasabah,
            CONCAT(n.nasabah_alamat, ', ', n.nasabah_kelurahan, ', ', n.nasabah_kecamatan) AS alamat,
            n.nasabah_telepon AS telepon,
            n.nasabah_email AS email,
            d.dep_nominal AS nominal,
            d.dep_tgl_awal AS tanggal_awal,
            d.dep_tgl_jthtempo AS tanggal_jatuh_tempo,
            d.dep_ppj_sistem AS kode_rollover,
            CASE d.dep_ppj_sistem
                WHEN 1 THEN 'Non-ARO'
                WHEN 2 THEN 'ARO'
                ELSE 'Lainnya'
            END AS jenis_rollover,
            d.dep_jkw AS jangka_waktu,
            d.dep_tabungan AS norek_tabungan,
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
                ELSE k.kantor_kode
            END AS kantor
        FROM data_deposito_master d
        JOIN data_nasabah_master n
          ON d.dep_nasabah = n.nasabah_id
        JOIN data_nasabah_orang o
          ON n.nasabah_id = o.nasabah_master
        JOIN data_kantor_master k
          ON d.dep_kantor = k.kantor_kode
        WHERE d.dep_status = 1
          AND d.dep_close_date = '2099-12-31'
          AND DATE(d.dep_tgl_jthtempo) = DATE_ADD(CURDATE(), INTERVAL {$hari} DAY)
    ";

        if (!empty($kodeCabang) && $kodeCabang !== '00') {
            $sql .= " AND k.kantor_kode = '{$kodeCabang}'";
        }

        $sql .= " ORDER BY d.dep_tgl_jthtempo";

        return DB::connection('mysql_REMOTE')->select($sql);
    }





    private function formatMessageSummaryWA($depositos, $reminder)
    {
        $header = "```Selamat pagi, teman-teman Bank DP Taspen,\n\n";

        if ($depositos->isNotEmpty()) {
            // Ambil tanggal jatuh tempo (semua data pasti sama tanggalnya)
            $tanggalJatuhTempo = Carbon::parse($depositos->first()->tanggal_jatuh_tempo)
                ->locale('id')
                ->translatedFormat('d F Y');

            $header .= "Berikut data deposito yang akan jatuh tempo pada tanggal {$tanggalJatuhTempo}:\n\n";
        } else {
            $header .= "Berikut data deposito yang akan jatuh tempo:\n\n";
        }

        // Header tabel
        $tableHeader = "No | Nama Nasabah        | Rekening     | Nominal        | Jatuh Tempo | Jenis   | Kantor\n";
        $tableHeader .= "---|---------------------|--------------|----------------|-------------|---------|----------------\n";

        // Isi tabel
        $rows = "";
        $no = 1;
        foreach ($depositos as $d) {
            $rows .= sprintf(
                "%-2d | %-19s | %-12s | Rp %-14s | %-11s | %-7s | %s\n",
                $no++,

                substr($d->nama_nasabah, 0, 19),
                $d->no_rekening,
                number_format($d->nominal, 0, ',', '.'),
                Carbon::parse($d->tanggal_jatuh_tempo)->translatedFormat('d M y'),
                $d->jenis_rollover,
                $d->kantor
            );
        }

        // Footer pesan
        $footer = "\nMohon untuk:\n";
        $footer .= "Follow-up nasabah terkait deposito tersebut.\n";
        $footer .= "Terima kasih 🙏```";

        return $header . $tableHeader . $rows . $footer;
    }
}
