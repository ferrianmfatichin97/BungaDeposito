<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'resource',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function payroll_deposito()
    {
        return $this->belongsTo(PayrollDeposito::class, 'resource', 'id');
    }

    //Log::info('log model:', $fillable->toArray());

}
