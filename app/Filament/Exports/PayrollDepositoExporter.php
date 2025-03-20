<?php

namespace App\Filament\Exports;

use App\Models\PayrollDeposito;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PayrollDepositoExporter extends Exporter
{
    protected static ?string $model = PayrollDeposito::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('norek_deposito'),
            ExportColumn::make('nama_nasabah'),
            ExportColumn::make('norek_tujuan'),
            ExportColumn::make('kode_bank'),
            ExportColumn::make('bank_tujuan'),
            ExportColumn::make('nama_rekening'),
            ExportColumn::make('nominal'),
            ExportColumn::make('jatuh_tempo'),
            ExportColumn::make('status'),
            ExportColumn::make('tanggal_bayar'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your payroll deposito export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
