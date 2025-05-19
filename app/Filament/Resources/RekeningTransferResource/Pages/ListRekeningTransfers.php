<?php

namespace App\Filament\Resources\RekeningTransferResource\Pages;

use App\Filament\Resources\RekeningTransferResource;
use App\Imports\RekeningTransfer;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListRekeningTransfers extends ListRecords
{
    protected static string $resource = RekeningTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus')
                ->label('Add')
                ->color('success'),

            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->color("primary"),

            // Actions\Action::make('Clear Data')
            //     ->action(function () {
            //         $this->truncateTable();
            //     })
            //     ->icon('heroicon-o-trash')
            //     ->requiresConfirmation()
            //     ->color('danger'),
        ];
    }

    // protected function getRedirectUrl(): string
    // {
    //     return $this->previousUrl ?? $this->getResource()::getUrl('index');
    // }

    // protected function getRedirectUrl(): string
    // {
    //     return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    // }

    protected function truncateTable()
    {
        $tableName = 'rekening_transfers';

        DB::table($tableName)->truncate();

        session()->flash('message', "Table '{$tableName}' has been Deleted successfully.");
    }
}
