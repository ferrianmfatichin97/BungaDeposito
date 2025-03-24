<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use App\Events\UserActivityLogged;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PayrollDeposito extends Model
{
    use LogsActivity;
    protected $table = 'payroll_depositos';
    protected $fillable = [
        'norek_deposito',
        'nama_nasabah',
        'norek_tujuan',
        'bank_tujuan',
        'kode_bank',
        'nama_rekening',
        'nominal',
        'jatuh_tempo',
        'status',
        'tanggal_bayar',
        'currency',
        'emailcorporate',
        'ibuobu',
        'remark1',
        'remark2',
        'remark3',
        'adjust1',
        'adjust2',
        'adjust3',
        'adjust4',
        'adjust5',
        'adjust6',
        'adjust7',
        'adjust8',
        'adjust9',
        'adjust10',
        'adjust11',
        'adjust12',
        'adjust13',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['norek_deposito', 'nama_nasabah']);
    }
}
