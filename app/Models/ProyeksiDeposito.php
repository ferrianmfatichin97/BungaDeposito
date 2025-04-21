<?php

namespace App\Models;

use App\Models\RekeningTransfer;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use App\Events\UserActivityLogged;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Builder;

class ProyeksiDeposito extends Model
{
    use LogsActivity;

    protected $table = 'proyeksi_depositos';

//    protected $primaryKey = 'rek_deposito';
//     public $incrementing = false;

    protected $fillable = [
        'rek_deposito',
        'nama_nasabah',
        'jangka_waktu',
        'nilai_bunga',
        'saldo_valuta_awal',
        'bunga',
        'total_bunga',
        'total_pajak',
        'total_bayar',
        'tujuan_penggunaan',
        'tanggal_bayar',
        'jatuh_tempo',
        'status',
        'dep_abp',
    ];

    public function rekening():BelongsTo
    {
        return $this->belongsTo(RekeningTransfer::class, 'rek_deposito', 'norek_deposito');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['rek_deposito', 'nama_nasabah']);
    }

    public function scopeWithRekeningTransfer(Builder $query) {
        return $query->leftJoin('rekening_transfers', 'proyeksi_depositos.rek_deposito', '=', 'rekening_transfers.norek_deposito')
                     ->select('proyeksi_depositos.*',
                              'rekening_transfers.currency',
                              'rekening_transfers.emailcorporate',
                              'rekening_transfers.ibuobu',
                              'rekening_transfers.remark1',
                              'rekening_transfers.remark2',
                              'rekening_transfers.remark3',
                              'rekening_transfers.adjust1',
                              'rekening_transfers.adjust2',
                              'rekening_transfers.adjust3',
                              'rekening_transfers.adjust4',
                              'rekening_transfers.adjust5',
                              'rekening_transfers.adjust6',
                              'rekening_transfers.adjust7',
                              'rekening_transfers.adjust8',
                              'rekening_transfers.adjust9',
                              'rekening_transfers.adjust10',
                              'rekening_transfers.adjust11',
                              'rekening_transfers.adjust12',
                              'rekening_transfers.adjust13',
                              'rekening_transfers.nama_deposan',
                              'rekening_transfers.norek_tujuan',
                              'rekening_transfers.bank_tujuan',
                              'rekening_transfers.nama_rekening',
                              'rekening_transfers.kode_bank');
    }
}
