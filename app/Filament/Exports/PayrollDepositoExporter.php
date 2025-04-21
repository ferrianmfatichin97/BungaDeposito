<?php

namespace App\Filament\Exports;

use App\Models\PayrollDeposito;
use App\Models\ProyeksiDeposito;
use Illuminate\Support\Facades\Log;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Facades\DB;

class PayrollDepositoExporter extends Exporter
{
    protected static ?string $model = PayrollDeposito::class;
    public function getFileName(Export $export): string
{
    $currentDate = new \DateTime();
    $month = $currentDate->format('m');
    $year = $currentDate->format('Y');

    $tanggalBayarGrouped = ProyeksiDeposito::select('tanggal_bayar')
    ->groupBy('tanggal_bayar')
    ->get();

    $tanggalString = implode('_', $tanggalBayarGrouped->pluck('tanggal_bayar')->toArray());

    //dd($tanggalString);

    return "Rekening Tujuan Transfer Pembayaran Bunga Deposito_{$tanggalString}_{$month}_{$year}.csv";
}

    public static function getColumns(): array
    {
        return [
            // ExportColumn::make('row_number')
            // ->label('NO')
            // ->format(fn($record, $index) => $index + 1),
            ExportColumn::make('nama_nasabah')
                ->label('NAMA'),
            ExportColumn::make('norek_deposito')
                ->label('NOREK DEPOSITO'),
            ExportColumn::make('norek_tujuan')
                ->label('NO REKENING TUJUAN'),
            ExportColumn::make('bank_tujuan')
                ->label('BANK TUJUAN'),
            ExportColumn::make('nominal')
                ->label('NOMINAL'),
            ExportColumn::make('tanggal_bayar')
                ->label('TGL Bayar'),

        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your payroll deposito export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';
        log::info('Berhasil Export Payroll Deposito');
        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
