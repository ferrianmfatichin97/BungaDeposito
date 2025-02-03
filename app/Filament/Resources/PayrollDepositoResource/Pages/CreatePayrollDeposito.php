<?php

namespace App\Filament\Resources\PayrollDepositoResource\Pages;

use App\Events\UserActivityLogged;
use App\Filament\Resources\PayrollDepositoResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CreatePayrollDeposito extends CreateRecord
{
    protected static string $resource = PayrollDepositoResource::class;

    protected function afterCreate(): void
    {
        parent::afterCreate();

        // Trigger event after creating a new record
        Event::dispatch(new UserActivityLogged('create', auth::id(), 'PayrollDeposito'));
        log::info('function after create');
    }
}
