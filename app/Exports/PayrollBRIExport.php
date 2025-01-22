<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\BeforeWriting;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;


class PayrollBRIExport implements FromCollection, WithMapping, WithHeadings, WithColumnFormatting, WithEvents, ShouldAutoSize , WithCustomCsvSettings
{
    use Exportable, RegistersEventListeners;

    protected int $totalCount;
    protected string $norek_tujuan_string;
    protected array $norek_tujuan_array;
    protected array $empat_digit_terakhir;
    protected int $totalnominal = 0;

    public function __construct(public Collection $records)
    {
        $this->totalCount = $this->records->count();
        $this->norek_tujuan_array = $this->records->pluck('norek_tujuan')->toArray();
        $this->empat_digit_terakhir = $this->ambilEmpatDigitTerakhir($this->norek_tujuan_array);
        $this->norek_tujuan_string = implode(', ', $this->norek_tujuan_array);
        $this->totalnominal = $this->records->sum('nominal');
    }

    public function collection()
    {
        return $this->records;
    }

    public function columnFormats(): array
    {
        return [
           //'D' => '#,##0.00',
        ];
    }

    protected function formatnominal($nominal): string
    {
        return (string)$nominal . '.';
    }

    public function map($payroll): array
    {
        static $index = 1;
        //dd($payroll);
        return [
            [
            $index++,
            $payroll->nama_nasabah,
            $payroll->norek_tujuan,
            number_format($payroll->nominal, 2, '', ''),
            ],

        ];
    }

    public function headings(): array
    {
        return [
            'NO',
            'NAMA',
            'ACCOUNT',
            'AMOUNT',
            'Email',
        ];
    }

    public function registerEvents(): array
{
    $total = $this->totalCount;
    $totalnominal = $this->totalnominal;
    $adjustcell = '';

    $totalEmpatDigit = array_sum(array_map('intval', $this->empat_digit_terakhir));
    $md5 = md5("$total.$totalnominal.$totalEmpatDigit");

    return [
        AfterSheet::class => function(AfterSheet $event) use ($total, $totalnominal, $md5, $adjustcell) {

            $rowCount = count($this->collection()) + 1;
            $event->sheet->setCellValue('A' . ($rowCount + 1), 'COUNT');
            $event->sheet->setCellValue('D' . ($rowCount + 1), $total);

            $rowCount2 = count($this->collection()) + 2;
            $event->sheet->setCellValue('A' . ($rowCount2 + 1), 'TOTAL');
            $event->sheet->setCellValue('D' . ($rowCount2 + 1), number_format($totalnominal, 2, '', ''));

            $rowCount2 = count($this->collection()) + 3;
            $event->sheet->setCellValue('A' . ($rowCount2 + 1), 'CHECK');
            $event->sheet->setCellValue('D' . ($rowCount2 + 1), $md5);

            $rowCount2 = count($this->collection()) + 4;
            $event->sheet->setCellValue('A' . ($rowCount2 + 1),  $adjustcell);

            $rowCount2 = count($this->collection()) + 5;
            $event->sheet->setCellValue('A' . ($rowCount2 + 1),  $adjustcell);

            $rowCount2 = count($this->collection()) + 6;
            $event->sheet->setCellValue('A' . ($rowCount2 + 1),  $adjustcell);

            $rowCount2 = count($this->collection()) + 7;
            $event->sheet->setCellValue('A' . ($rowCount2 + 1),  $adjustcell);

            $rowCount2 = count($this->collection()) + 8;
            $event->sheet->setCellValue('A' . ($rowCount2 + 1),  $adjustcell);

            $rowCount2 = count($this->collection()) + 9;
            $event->sheet->setCellValue('A' . ($rowCount2 + 1),  $adjustcell);
        },
    ];
}

    protected function ambilEmpatDigitTerakhir(array $norek_tujuan): array
    {
        $results = [];
        foreach ($norek_tujuan as $rekening) {

            if (empty($rekening)) {
                $results[] = '';
                continue;
            }

            $lastFourDigits = substr($rekening, -4);

            // Cek apakah digit pertama dari 4 digit terakhir adalah '0'
            if ($lastFourDigits[0] === '0') {
                // Jika ya, ambil 3 digit setelahnya
                $results[] = substr($lastFourDigits, 1, 3);
            } else {
                // Jika tidak, kembalikan 4 digit terakhir
                $results[] = $lastFourDigits;
            }
        }

        return $results;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function getnorek_tujuanString(): string
    {
        return $this->norek_tujuan_string;
    }

    public function getTotalNominal(): int
    {
        return $this->totalnominal;
    }

    public function getOptions(): array
    {
        return [
            'title' => 'Payroll Export',
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
