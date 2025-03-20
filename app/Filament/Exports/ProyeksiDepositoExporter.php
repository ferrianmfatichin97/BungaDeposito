<?php

namespace App\Filament\Exports;

use App\Models\ProyeksiDeposito;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ProyeksiDepositoExporter extends Exporter
{
    protected static ?string $model = ProyeksiDeposito::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('rek_deposito')
                ->label('No.Rekening'),
            ExportColumn::make('nama_nasabah')
                ->label('Nama Nasabah'),
            ExportColumn::make('tanggal_bayar')
                ->label('Tgl.Bg'),
            ExportColumn::make('nilai_bunga')
                ->label('Bunga'),
            ExportColumn::make('saldo_valuta_awal')
                ->label('Nilai Valuta'),
            ExportColumn::make('total_bunga')
                ->label('Bunga'),
            ExportColumn::make('total_pajak')
                ->label('Pajak'),
            ExportColumn::make('total_bayar')
                ->label('Total Bayar'),
            ExportColumn::make('status')
                ->label('Status'),
            // ExportColumn::make('jangka_waktu'),
            // ExportColumn::make('bunga'),
            // ExportColumn::make('tujuan_penggunaan'),
            // ExportColumn::make('jatuh_tempo'),
            // ExportColumn::make('created_at'),
            // ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your proyeksi deposito export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
