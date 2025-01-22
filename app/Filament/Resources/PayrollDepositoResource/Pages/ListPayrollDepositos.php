<?php

namespace App\Filament\Resources\PayrollDepositoResource\Pages;

use App\Filament\Imports\PayrollDepositoImporter;
use App\Filament\Resources\PayrollDepositoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayrollDepositos extends ListRecords
{
    protected static string $resource = PayrollDepositoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
            Actions\ImportAction::make('Import Data Payroll Deposito')
            ->importer(PayrollDepositoImporter::class)
            ->maxRows(100000)
            ->chunkSize(1000)
            ->icon('heroicon-s-arrow-up-tray')
            ->Button()
            ->options([
                'updateExisting' => true,
            ]),
        ];
    }
}
