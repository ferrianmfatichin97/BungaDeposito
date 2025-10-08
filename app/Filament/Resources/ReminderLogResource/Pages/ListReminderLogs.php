<?php

namespace App\Filament\Resources\ReminderLogResource\Pages;

use App\Filament\Resources\ReminderLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReminderLogs extends ListRecords
{
    protected static string $resource = ReminderLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
