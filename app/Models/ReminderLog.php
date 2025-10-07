<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReminderLog extends Model
{
    protected $fillable = [
        'reminder_id',
        'kode_cabang',
        'channel',
        'tujuan',
        'status',
        'count',
        'message',
        'response',
    ];
}
