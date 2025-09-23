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

class SendDepositoReminders extends Command
{
    protected $signature = 'reminder:deposito';
    protected $description = 'Kirim reminder deposito via Email & WhatsApp';

    public function handle()
    {
        // Ambil hanya reminder yang aktif
        $reminders = DepositoReminder::where('aktif', 1)->get();
        Log::info('Memulai proses pengiriman reminder deposito', ['count' => $reminders->count()]);

        foreach ($reminders as $reminder) {
            // Validasi interval hari
            if (empty($reminder->hari_sebelum_jt)) {
                Log::warning("Reminder ID {$reminder->id} dilewati karena hari_sebelum_jt kosong");
                continue;
            }

            // Debug: log detail reminder yang sedang diproses
            Log::info('Proses reminder', [
                'id' => $reminder->id,
                'kode_cabang' => $reminder->kode_cabang ?? null,
                'email_tujuan' => $reminder->email_tujuan,
                'wa_tujuan' => $reminder->wa_tujuan,
                'hari_sebelum_jt' => $reminder->hari_sebelum_jt,
            ]);

            $depositos = collect($this->ambilDeposito($reminder));

            if ($depositos->isEmpty()) {
                Log::info("Tidak ada deposito untuk reminder ID {$reminder->id} (H-{$reminder->hari_sebelum_jt})");
                continue;
            }

            Log::info("Ditemukan {$depositos->count()} deposito untuk reminder ID {$reminder->id}");

            // --- Kirim Email (satu email berisi daftar) ---
            if ($reminder->email_tujuan) {
                try {
                    Mail::to($reminder->email_tujuan)
                        ->send(new DepositoReminderMail($depositos, $reminder));

                    $this->info("Email terkirim ke {$reminder->email_tujuan}");
                    Log::info("Email sukses dikirim ke {$reminder->email_tujuan}", [
                        'reminder_id' => $reminder->id,
                        'count' => $depositos->count(),
                    ]);

                    foreach ($depositos as $deposito) {
                        Log::info('EMAIL Reminder Deposito', [
                            'email_tujuan' => $reminder->email_tujuan,
                            'rekening'     => $deposito->no_rekening,
                            'nama'         => $deposito->nama_nasabah,
                            'nominal'      => $deposito->nominal,
                            'jatuh_tempo'  => $deposito->tanggal_jatuh_tempo,
                            'jenis'        => $deposito->jenis_rollover,
                        ]);
                    }
                } catch (\Exception $e) {
                    $this->error("Error Email: " . $e->getMessage());
                    Log::error('Email gagal dikirim', [
                        'email_tujuan' => $reminder->email_tujuan,
                        'error'        => $e->getMessage(),
                    ]);
                }
            }

            // --- Kirim WA (satu pesan per rekening; bisa diubah jadi summary jika mau) ---
            if ($reminder->wa_tujuan) {
                foreach ($depositos as $deposito) {
                    $message = $reminder->message_template
                        ? $this->parseMessage($reminder->message_template, $deposito)
                        : "Reminder Deposito:\n" .
                        "Nama: {$deposito->nama_nasabah}\n" .
                        "Rekening: {$deposito->no_rekening}\n" .
                        "Nominal: " . number_format($deposito->nominal, 0, ',', '.') . "\n" .
                        "Jatuh Tempo: {$deposito->tanggal_jatuh_tempo}\n" .
                        "Jenis: {$deposito->jenis_rollover}";

                    try {
                        $response = Http::withHeaders([
                            'Authorization' => config('services.fonnte.token'),
                        ])->asMultipart()->post('https://api.fonnte.com/send', [
                            'target'      => $reminder->wa_tujuan,
                            'message'     => $message,
                            'countryCode' => '62',
                        ]);

                        if ($response->successful()) {
                            $this->info("WA terkirim ke {$reminder->wa_tujuan}");
                            Log::info('WA Reminder Deposito', [
                                'wa_tujuan'   => $reminder->wa_tujuan,
                                'rekening'    => $deposito->no_rekening,
                                'nama'        => $deposito->nama_nasabah,
                                'nominal'     => $deposito->nominal,
                                'jatuh_tempo' => $deposito->tanggal_jatuh_tempo,
                                'jenis'       => $deposito->jenis_rollover,
                                'response'    => $response->body(),
                            ]);
                        } else {
                            $this->error("Gagal kirim WA: " . $response->body());
                            Log::error('WA gagal dikirim', [
                                'wa_tujuan' => $reminder->wa_tujuan,
                                'response'  => $response->body(),
                            ]);
                        }
                    } catch (\Exception $e) {
                        $this->error("Error WA: " . $e->getMessage());
                        Log::error('WA Exception', [
                            'wa_tujuan' => $reminder->wa_tujuan,
                            'error'     => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        return SymfonyCommand::SUCCESS;
    }

    /**
     *
     *
     * @param \App\Models\DepositoReminder $reminder
     * @return array
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
            k.kantor_kode AS kode_cabang
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

    // Jika kode cabang tidak "00", tambahkan filter cabang
    if (!empty($kodeCabang) && $kodeCabang !== '00') {
        $sql .= " AND k.kantor_kode = '{$kodeCabang}'";
    }

    $sql .= " ORDER BY d.dep_tgl_jthtempo";

    return DB::connection('mysql_REMOTE')->select($sql);
}



    private function parseMessage($template, $deposito)
    {
        $replace = [
            '{nama}'     => $deposito->nama_nasabah,
            '{rekening}' => $deposito->no_rekening,
            '{nominal}'  => number_format($deposito->nominal, 0, ',', '.'),
            '{jtempo}'   => $deposito->tanggal_jatuh_tempo,
            '{jenis}'    => $deposito->jenis_rollover,
        ];

        return str_replace(array_keys($replace), array_values($replace), $template);
    }
}
