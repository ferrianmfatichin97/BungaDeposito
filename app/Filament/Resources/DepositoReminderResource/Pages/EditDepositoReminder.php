<?php

namespace App\Filament\Resources\DepositoReminderResource\Pages;

use App\Filament\Resources\DepositoReminderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDepositoReminder extends EditRecord
{
    protected static string $resource = DepositoReminderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
