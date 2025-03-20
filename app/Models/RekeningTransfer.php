<?php

namespace App\Models;

use App\Models\ProyeksiDeposito;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use App\Events\UserActivityLogged;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class RekeningTransfer extends Model
{
    use LogsActivity;
    protected $fillable = [
        'norek_deposito',
        'nama_deposan',
        'norek_tujuan',
        'bank_tujuan',
        'nama_rekening',
        'kode_bank',
        'nominal',
        'tgl_bayar',
        'status',
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

    const DEFAULT_CURRENCY = 'IDR';
    const DEFAULT_EMAIL = 'bprtaspen@gmail.com';
    const DEFAULT_IBUOBU = 'IBU';
    const DEFAULT_REMARK1_PREFIX = 'Budep ';
    const DEFAULT_REMARK2 = 'transactionRemark1';
    const DEFAULT_REMARK3 = 'transactionRemark2';
    const DEFAULT_ADJUST1 = 'transactionRemark3';
    const DEFAULT_ADJUST2 = 'valuePaymentDetails';
    const DEFAULT_ADJUST3 = 'N';
    const DEFAULT_ADJUST4 = 'N';
    const DEFAULT_ADJUST5 = 'extended payment detail';
    const DEFAULT_ADJUST6 = 'OUR';
    const DEFAULT_ADJUST7 = 'EPD';
    const DEFAULT_ADJUST8 = 'Y';
    const DEFAULT_ADJUST9 = '014';
    const DEFAULT_ADJUST10 = 'BPR0101309';
    const DEFAULT_ADJUST11 = '0';
    const DEFAULT_ADJUST12 = 'BANK MANDIRI TASPEN';
    const DEFAULT_ADJUST13 = '2144213178589';

    protected static function boot()
    {
        parent::boot();

        // static::deleting(function ($record) {
        //     Log::info('After Delete Data: ' . $record->id);
        //     Event::dispatch(new UserActivityLogged('Delete', Auth::id(), $record->id));
        // });

        static::creating(function (self $model) {
            $model->currency = self::DEFAULT_CURRENCY;
            $model->emailcorporate = self::DEFAULT_EMAIL;
            $model->ibuobu = self::DEFAULT_IBUOBU;
            $model->remark1 = self::DEFAULT_REMARK1_PREFIX . Carbon::now()->format('M Y');
            $model->remark2 = self::DEFAULT_REMARK2;
            $model->remark3 = self::DEFAULT_REMARK3;
            $model->adjust1 = self::DEFAULT_ADJUST1;
            $model->adjust2 = self::DEFAULT_ADJUST2;
            $model->adjust3 = self::DEFAULT_ADJUST3;
            $model->adjust4 = self::DEFAULT_ADJUST4;
            $model->adjust5 = self::DEFAULT_ADJUST5;
            $model->adjust6 = self::DEFAULT_ADJUST6;
            $model->adjust7 = self::DEFAULT_ADJUST7;
            $model->adjust8 = self::DEFAULT_ADJUST8;
            $model->adjust9 = self::DEFAULT_ADJUST9;
            $model->adjust10 = self::DEFAULT_ADJUST10;
            $model->adjust11 = self::DEFAULT_ADJUST11;
            $model->adjust12 = self::DEFAULT_ADJUST12;
            $model->adjust13 = self::DEFAULT_ADJUST13;

            $bankCodes = [
                "MANDIRI" => "BMRIIDJA",
                "MANTAP" => "SIHBIDJ1",
                "CIMB" => "BNIAIDJA",
                "BANK JATENG" => "SYJGIDJ1",
                "BPD SULTRA" => "PDWRIDJ1",
                "BSI" => "BSMDIDJA",
                "BTN" => "BTANIDJA",
                "BTPN" => "PUBAIDJ1",
                "BWS" => "BSDRIDJA",
                "DANAMON" => "BDINIDJA",
                "DKI" => "BDKIIDJ1",
                "DKI SYARIAH" => "SYDKIDJ1",
                "MAYBANK" => "IBBKIDJA",
                "MEGA" => "MEGAIDJA",
                "OCBC" => "NISPIDJA",
                "PANIN" => "PINBIDJA",
                "PERMATA" => "BBBAIDJA",
                "SINARMAS" => "SBJKIDJA",
                "BCA" => "CENAIDJA",
            ];

            $model->kode_bank = $bankCodes[$model->bank_tujuan] ?? null;
            Log::info('log model Rekening Transfer:', $model->toArray());
        });
    }

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(ProyeksiDeposito::class, 'norek_deposito', 'rek_deposito');
    }

    public function scopeWithRekeningTransfer(Builder $query)
    {
        return $query->leftJoin('proyeksi_depositos', 'rekening_transfers.norek_deposito', '=', 'proyeksi_depositos.rek_deposito')
                     ->select('rekening_transfers.*', 'proyeksi_depositos.*');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['norek_deposito', 'nama_nasabah']);
    }
}
