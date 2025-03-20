<?php

namespace App\Exports;

use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Auth;
use App\Events\UserActivityLogged;
use Maatwebsite\Excel\Concerns\WithEvents;


class RekeningPelengkap implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize, WithEvents
{
    use Exportable, RegistersEventListeners;


    public function __construct(public Collection $records) {}

    public function collection()
    {
        return $this->records;
    }

    public function map($payroll): array
    {
        //dd($payroll);
        return [

            $payroll->rek_deposito,
            $payroll->nama_nasabah,
            '',
            '',
            '',
            $payroll->total_bayar,
            $payroll->tanggal_bayar,
            'AKTIF',
        ];
    }

    public function headings(): array
    {
        return [
            [
                'norek_deposito',
                'Nama Deposan',
                'norek_tujuan',
                'Bank Tujuan',
                'Nama Sesuai Rekening',
                'Nominal',
                'Tgl Bayar',
                'Status',
            ],
        ];
    }

    public function getOptions(): array
    {
        return [
            'title' => 'payrolls Export',
        ];
    }
}
