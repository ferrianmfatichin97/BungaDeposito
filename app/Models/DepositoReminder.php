<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositoReminder extends Model
{
    protected $fillable = [
        'kode_cabang',
        'email_tujuan',
        'wa_tujuan',
        'hari_sebelum_jt',
        'aktif',
        'message_template',
    ];
}
