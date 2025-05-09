<?php

namespace App\Exports;

use App\Models\PayrollDeposito;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;

class PayrollDepositoExport implements FromCollection, WithMapping, WithEvents, WithHeadings, ShouldAutoSize
{
    private $payrolls;

    public function __construct($payrolls)
    {
       // dd($payrolls);
        $this->payrolls = $payrolls;
    }

    /**
     * @return \Illuminate\Support\Collection
     */

    public function collection()
    {
        $data = $this->payrolls->get();
        //dd($data);
        return $data;
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

        //$tomorrow = new Carbon('2025-04-30');
        $tomorrow = Carbon::tomorrow();
        $lastDayOfMonth = $tomorrow->copy()->subDays()->endOfMonth();

        if ($lastDayOfMonth->day < 31 && $tomorrow->copy()->addDays()->day === 1) {
            $payroll->tanggal_bayar = $tomorrow->day;
        }

        $tanggalBayar = Carbon::createFromFormat('d-m-Y', $payroll->tanggal_bayar);

        return [
            $index++,
            $payroll->nama_nasabah,
            $payroll->norek_deposito,
            "'" . $payroll->norek_tujuan,
            $payroll->bank_tujuan,
            $payroll->nominal,
            $tf_via,
            $tanggalBayar,
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
        $cp = clone $this->payrolls;
        $totalNominalByDateBankAndTV = $cp
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
