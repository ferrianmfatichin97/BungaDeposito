<?php

namespace App\Filament\Resources\PayrollDepositoResource\Pages;

use App\Events\UserActivityLogged;
use App\Filament\Resources\PayrollDepositoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EditPayrollDeposito extends EditRecord
{
    protected static string $resource = PayrollDepositoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $recordId = $this->record->id;

        Event::dispatch(new UserActivityLogged('Update', Auth::id(),   $recordId));

        Log::info('User dengan ID: ' . Auth::id() . ' Telah Mengedit Data Payroll Deposito dengan ID: ' . $recordId);
    }
}
