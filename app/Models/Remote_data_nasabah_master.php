<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Remote_data_nasabah_master extends Model
{
    protected $connection = 'mysql_REMOTE';
    protected $table = 'data_nasabah_master';
    protected $primaryKey = 'nasabah_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [] ;

    // public function bungadeposito()
    // {
    //     return $this->belongsTo(PayrollDeposito::class, 'dep_rekening','norek_deposito');
    // }
}
