<?php

namespace App\Providers;

use App\Events\UserActivityLogged;
use App\Events\UserLoggedIn;
use App\Listeners\LogUser;
use App\Listeners\LogUserActivity;
use App\Listeners\UserActivity;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Writer::listen(BeforeExport::class, function () {
        //     //
        // });

        // Writer::listen(BeforeWriting::class, function () {
        //     //
        // });

        // Sheet::listen(BeforeSheet::class, function () {
        //     //
        // });

        // Sheet::listen(AfterSheet::class, function () {
        //     //
        // });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            UserActivityLogged::class,
            LogUserActivity::class,
        );
    }
}
