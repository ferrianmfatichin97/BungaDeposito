<?php

namespace App\Listeners;

use App\Events\UserActivityLogged;
use App\Models\ActivityLog; // Import model ActivityLog
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogUserActivity
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserActivityLogged $event): void
    {
        Log::info('User  Activity:', [
            'user_id' => $event->userId,
            'action' => $event->action,
            'resource' => $event->resource,
            'timestamp' => now(),
        ]);

        ActivityLog::create([
            'user_id' => $event->userId,
            'action' => $event->action,
            'resource' => $event->resource,
        ]);
    }
}
