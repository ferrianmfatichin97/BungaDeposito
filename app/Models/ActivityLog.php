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

    //Log::info('log model:', $fillable->toArray());
}
