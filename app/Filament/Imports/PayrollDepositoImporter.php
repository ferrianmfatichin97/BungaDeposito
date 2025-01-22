<?php

namespace App\Filament\Imports;

use App\Models\PayrollDeposito;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;

class PayrollDepositoImporter extends Importer
{
    protected static ?string $model = PayrollDeposito::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('norek_deposito')->rules(['max:255']),
            ImportColumn::make('nama_nasabah')->rules(['max:255']),
            ImportColumn::make('norek_tujuan')->rules(['max:255']),
            ImportColumn::make('bank_tujuan')->rules(['max:255']),
            ImportColumn::make('nama_rekening')->rules(['max:255']),
            ImportColumn::make('nominal')->numeric()->rules(['max:255']),
            ImportColumn::make('jatuh_tempo')->rules(['max:255']), // Mengubah validasi menjadi 'date' jika jatuh_tempo adalah tanggal
            ImportColumn::make('status')->rules(['max:255']),
        ];
    }

    public function resolveRecord(): ?PayrollDeposito
{
    return new PayrollDeposito();
}

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your payroll deposito import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
