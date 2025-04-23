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

        // $angka = $payroll->dep_abp;
        // $saldo = "7500000"; 
        // $saldo_awal = $payroll->saldo_valuta_awal;
        
        // $total_dibayarkan = $payroll->nominal; 
        
        // if ($angka == 2 || $saldo_awal == $saldo) {
        //     $total_dibayarkan = $payroll->total_bunga; 
        // }

        // dd([
        //     'dep_apb' => $angka,
        //     'total_dibayarkan' => $total_dibayarkan,
        //     'payroll' => $payroll,
        // ]);

        //dd($payroll);

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
        // $totalNominalByBank = DB::table('payroll_depositos')
        //     ->select('bank_tujuan', DB::raw('SUM(nominal) AS total_nominal'))
        //     ->groupBy('bank_tujuan')
        //     ->get();

        $totalNominalByBank = DB::table('payroll_depositos')
            ->select(DB::raw("
                CASE
                    WHEN bank_tujuan IN ('MANDIRI', 'BRI') THEN bank_tujuan
                    ELSE 'BI-FAST'
                END AS bank_group,
                SUM(nominal) AS total_nominal
            "))
            ->groupBy('bank_group')
            ->get();

        return [
            AfterSheet::class => function (AfterSheet $event) use ($totalNominalByBank) {
                $rowCount = count($this->collection()) + 2;

                $event->sheet->setCellValue('B' . ($rowCount + 1), 'TV VIA');
                $event->sheet->setCellValue('C' . ($rowCount + 1), 'TOTAL NOMINAL');

                $row = $rowCount + 2;
                foreach ($totalNominalByBank as $item) {
                    $event->sheet->setCellValue('B' . $row, $item->bank_group);
                    $event->sheet->setCellValue('C' . $row, number_format($item->total_nominal, 2, '.', ''));
                    $row++;
                }

                $event->sheet->setCellValue('B' . $row, '');
                $event->sheet->setCellValue('C' . $row, '');
            },
        ];
    }
}
