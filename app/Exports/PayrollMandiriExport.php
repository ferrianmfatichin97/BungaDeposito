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
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;


class PayrollMandiriExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize, WithCustomCsvSettings
{
    use Exportable;

    protected int $totalCount;
    protected int $norek_tujuan;
    protected int $totalnominal = 0;



    public function __construct(public Collection $records)
{
    $this->totalCount = $this->records->count();

    $this->norek_tujuan = $this->records->first()->norek_tujuan;

    $this->totalnominal = $this->records->sum('nominal');
}
    public function collection()
    {
        return $this->records;
    }

    public function map($payroll): array
    {
        static $index = 1;
        static $angka = "008";
        return [
                $payroll->norek_tujuan,
                $payroll->nama_rekening,
                '',
                '',
                '',
                'IDR',
                $payroll->nominal,
                'Budep ' .date('d M y'),
                $payroll->norek_deposito,
                'IBU',
                $angka,
                '',
                '',
                '',
                '',
                '',
                'N',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'OUR',
                '',
                'EPD'.$index++,
        ];
    }

    public function headings(): array
    {
        $total = $this->totalCount;
        $date = date('Ymd');
        $totalnominal = $this->totalnominal;

        return [

                'P',
                $date,
                '1290000168290',
                $total,
                $totalnominal,
        ];
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function norek_tujuan(): int
    {
        return $this->norek_tujuan;
    }

    public function getTotalnominal(): int
    {
        return $this->totalnominal;
    }

    public function getOptions(): array
    {
        return [
            'title' => 'payrolls Export',
        ];
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',', // Set the delimiter
            'enclosure' => '', // Set enclosure to empty to avoid quotes
            'use_bom' => true, // Use BOM for UTF-8
            'output_encoding' => 'UTF-8', // Set output encoding to UTF-8
        ];
    }
}
