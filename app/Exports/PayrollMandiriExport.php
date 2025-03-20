<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Support\Facades\Auth;
use App\Events\UserActivityLogged;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\AfterSheet;


class PayrollMandiriExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize, WithCustomCsvSettings, WithEvents
{
    use Exportable, RegistersEventListeners;

    protected int $totalCount;
    protected int $norek_tujuan;
    protected int $totalnominal = 0;
    private $isRegistered = false;


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
            'Budep ' . date('d M y'),
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
            'EPD' . $index++,
        ];
    }

    public function headings(): array
    {
        $total = $this->totalCount;
        $date = date('Ymd', strtotime('+1 day'));
        $totalnominal = $this->totalnominal;

        return [

            'P',
            $date,
            '1290000168290',
            $total,
            $totalnominal,
        ];
    }

    // public function registerEvents(): array
    // {
    //     Log::info('registerEvents function is called');

    //     if ($this->isRegistered) {
    //         Log::info('Events already registered, returning empty array.');
    //         return [];
    //     }

    //     $this->isRegistered = true;

    //     Log::info('registerEvents called from ' . debug_backtrace()[1]['function']);
    //     $processedIds = [];

    //     if (empty($this->records)) {
    //         Log::warning('No records found to process.');
    //     }

    //     foreach ($this->records as $record) {
    //         if (!in_array($record->id, $processedIds)) {
    //             Log::info('Dispatching event for record ID: ' . $record->id);
    //             Event::dispatch(new UserActivityLogged('Export Mandiri', Auth::id(), $record->id));
    //             $processedIds[] = $record->id;
    //         }
    //     }
    //     return $processedIds;
    // }

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
            'delimiter' => ',',
            'enclosure' => '',
            'use_bom' => true,
            'output_encoding' => 'UTF-8',
        ];
    }
}
