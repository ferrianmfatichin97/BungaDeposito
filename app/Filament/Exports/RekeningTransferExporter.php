<?php

namespace App\Filament\Exports;

use App\Models\RekeningTransfer;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class RekeningTransferExporter extends Exporter
{
    protected static ?string $model = RekeningTransfer::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('norek_deposito'),
            ExportColumn::make('nama_deposan'),
            ExportColumn::make('norek_tujuan'),
            ExportColumn::make('bank_tujuan'),
            ExportColumn::make('kode_bank'),
            ExportColumn::make('nama_rekening'),
            ExportColumn::make('nominal'),
            ExportColumn::make('tgl_bayar'),
            ExportColumn::make('status'),
           
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your rekening transfer export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
