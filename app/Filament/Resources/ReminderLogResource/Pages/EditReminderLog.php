<?php

namespace App\Filament\Resources\ReminderLogResource\Pages;

use App\Filament\Resources\ReminderLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReminderLog extends EditRecord
{
    protected static string $resource = ReminderLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
