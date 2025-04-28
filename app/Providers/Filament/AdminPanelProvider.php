<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\Enums\ThemeMode;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Carbon\Carbon;
use Niladam\FilamentAutoLogout\AutoLogoutPlugin;
use Rmsramos\Activitylog\ActivitylogPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->databaseNotifications()
            ->spa()
            ->unsavedChangesAlerts()
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('16rem')
            //->topNavigation()
            ->id('admin')
            ->path('admin')
            // Akses User Admin
            ->login()
            ->passwordReset()
            ->plugins([
                ActivitylogPlugin::make()
                 ->navigationGroup('Menu Admin')
                 ->authorize(
                    fn () => auth()->user()->isAdmin()
                ),
                AutoLogoutPlugin::make()
                    ->color(Color::Emerald)
                    // ->withoutWarning()
                    ->logoutAfter(Carbon::SECONDS_PER_MINUTE * 15),
                    // ->withoutTimeLeft()
                    // ->timeLeftText('')
            ])
            //->profile()
            ->profile(isSimple: false)
            ->defaultThemeMode(ThemeMode::Light)
            ->brandName('Bunga Deposito')
            ->brandLogo(asset('logo.png'))
            ->favicon(asset('dp.png'))
            ->colors([
                //'danger' => Color::Rose,
                //'gray' => Color::Gray,
                //'info' => Color::Blue,
                //'primary' => Color::Indigo,
                'success' => Color::Emerald,
                //'warning' => Color::Orange,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                //Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
