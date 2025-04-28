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
        return PayrollDeposito::orderBy('tanggal_bayar')->get();
    }

    public function map($payroll): array
    {
        static $index = 1;

        switch ($payroll->bank_tujuan) {
            case 'BRI':
                $tf_via = 'BRI';
                break;
            case 'MANDIRI':
                $tf_via = 'MANDIRI';
                break;
            default:
                $tf_via = 'BI-FAST';
                break;
        }

        return [
            $index++,
            $payroll->nama_nasabah,
            $payroll->norek_deposito,
            "'" . $payroll->norek_tujuan,
            $payroll->bank_tujuan,
            $payroll->nominal,
            $tf_via,
            $payroll->tanggal_bayar,
            $payroll->nama_rekening,
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
            'TANGGAL BAYAR',
            'Nama Penerima',

        ];
    }

    public function registerEvents(): array
    {
        $totalNominalByDateBankAndTV = DB::table('payroll_depositos')
            ->select(
                'tanggal_bayar',
                DB::raw("
                     CASE
                         WHEN bank_tujuan = 'BRI' THEN 'BRI'
                         WHEN bank_tujuan = 'MANDIRI' THEN 'MANDIRI'
                         ELSE 'BI-FAST'
                     END AS tv_via"),
                DB::raw('SUM(nominal) as total_nominal')
            )
            ->groupBy('tanggal_bayar', 'tv_via')
            ->orderBy('tanggal_bayar')
            ->get();

        return [
            AfterSheet::class => function (AfterSheet $event) use ($totalNominalByDateBankAndTV) {
                $rowCount = count($this->collection()) + 2;

                $event->sheet->setCellValue('A' . ($rowCount + 1), 'Tanggal');
                $event->sheet->setCellValue('B' . ($rowCount + 1), 'TV VIA');
                $event->sheet->setCellValue('C' . ($rowCount + 1), 'TOTAL NOMINAL');

                $currentDate = null;
                $currentTV = null;
                $row = $rowCount + 2;

                foreach ($totalNominalByDateBankAndTV as $item) {
                    if ($currentDate !== $item->tanggal_bayar || $currentTV !== $item->tv_via) {
                        $currentDate = $item->tanggal_bayar;
                        $currentTV = $item->tv_via;

                        $event->sheet->setCellValue('A' . $row, $currentDate);
                        $event->sheet->setCellValue('B' . $row, $currentTV);
                        $event->sheet->setCellValue('C' . $row, number_format($item->total_nominal, 2, '.', ''));
                        $row++;
                    } else {
                        
                        $event->sheet->setCellValue('B' . $row, $item->bank_tujuan);
                        $event->sheet->setCellValue('C' . $row, number_format($item->total_nominal, 2, '.', ''));
                        $row++;
                    }
                }

                $event->sheet->setCellValue('A' . $row, '');
                $event->sheet->setCellValue('B' . $row, '');
                $event->sheet->setCellValue('C' . $row, '');
            },
        ];
    }
}
