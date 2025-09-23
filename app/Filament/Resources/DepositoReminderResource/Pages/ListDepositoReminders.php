<?php

namespace App\Filament\Resources\DepositoReminderResource\Pages;

use App\Filament\Resources\DepositoReminderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDepositoReminders extends ListRecords
{
    protected static string $resource = DepositoReminderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
