<?php

namespace App\Providers\Filament;

use App\Http\Middleware\EnsurePlatformAdmin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PlatformAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('platform-admin')
            ->path('platform-admin')
            ->login()
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->brandName('MasomoPlus · Platform Admin')
            ->discoverResources(
                in: app_path('Filament/PlatformAdmin/Resources'),
                for: 'App\\Filament\\PlatformAdmin\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/PlatformAdmin/Pages'),
                for: 'App\\Filament\\PlatformAdmin\\Pages'
            )
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Filament/PlatformAdmin/Widgets'),
                for: 'App\\Filament\\PlatformAdmin\\Widgets'
            )
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\PlatformAdmin\Widgets\PlatformStatsOverview::class,
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
                EnsurePlatformAdmin::class,
            ]);
    }
}
