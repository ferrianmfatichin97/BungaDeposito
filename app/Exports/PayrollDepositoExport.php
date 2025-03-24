<?php

namespace App\Exports;

use App\Models\PayrollDeposito;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Support\Facades\DB;

class PayrollDepositoExport implements FromCollection, WithMapping, WithEvents, WithHeadings, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public function collection()
    {
        return PayrollDeposito::all();
    }

    public function map($payroll): array
    {
        static $index = 1;
        return [
            $index++,
            $payroll->nama_nasabah,
            $payroll->norek_deposito,
            $payroll->norek_tujuan,
            $payroll->bank_tujuan,
            $payroll->nominal,
            '',
        ];
    }

    public function headings(): array
    {
        return [
            'NO',
            'NAMA',
            'NOREK DEPOSITO',
            'NOREK TUJUAN',
            'BANK TUJUAN',
            'NOMINAL',
            'TF VIA',

        ];
    }

    public function registerEvents(): array
    {
        $totalNominalByBank = DB::table('payroll_depositos')
            ->select('bank_tujuan', DB::raw('SUM(nominal) AS total_nominal'))
            ->groupBy('bank_tujuan')
            ->get();

        return [
            AfterSheet::class => function (AfterSheet $event) use ($totalNominalByBank) {
                $rowCount = count($this->collection()) + 1;

                $event->sheet->setCellValue('A' . ($rowCount + 1), 'BANK TUJUAN');
                $event->sheet->setCellValue('B' . ($rowCount + 1), 'TOTAL NOMINAL');

                $row = $rowCount + 2;
                foreach ($totalNominalByBank as $item) {
                    $event->sheet->setCellValue('A' . $row, $item->bank_tujuan);
                    $event->sheet->setCellValue('B' . $row, number_format($item->total_nominal, 2, '.', ''));
                    $row++;
                }

                $event->sheet->setCellValue('A' . $row, '');
                $event->sheet->setCellValue('B' . $row, '');
            },
        ];
    }
}
