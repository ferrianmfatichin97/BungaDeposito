<?php

namespace App\Filament\Resources\ProyeksiDepositoResource\Pages;

use App\Filament\Exports\ProyeksiDepositoExporter;
use App\Filament\Resources\ProyeksiDepositoResource;
use App\Models\ProyeksiDeposito;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Filament\Actions\Exports\Models\Export;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProyeksiDepositos extends ListRecords
{
    protected static string $resource = ProyeksiDepositoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add')
                ->icon('heroicon-o-plus')
                ->color('primary'),

            Actions\ExportAction::make()
                ->exporter(ProyeksiDepositoExporter::class)
                ->label('Export')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->fileName(fn(Export $export): string => "Proyeksi Deposito-{$export->getKey()}.csv"),

            Actions\Action::make('createProyeksiDeposito')
                ->label('Generate Data')
                ->action(function () {
                    Artisan::call('app:proyeksideposito');
                    //$this->notify('success', 'Proyeksi Deposito created successfully.');
                })
                ->color('primary'),

            Actions\Action::make('Clear Data')
                ->action(function () {
                    $this->truncateTable();
                })
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->color('danger'),
        ];
    }

    protected function truncateTable()
    {
        $tableName = 'proyeksi_depositos';

        DB::table($tableName)->truncate();

        session()->flash('message', "Table '{$tableName}' has been Deleted successfully.");
    }
}
