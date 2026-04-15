<?php

namespace App\Providers\Filament;

use App\Filament\Sales\Widgets\SalesStats;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook; // ← TAMBAH INI
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class SalesPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('sales')
            ->path('sales')
            ->authGuard('web')
            ->login()
            ->navigationGroups([
                'Master Data',
                'Transaksi',
                'Laporan',
                'Pengaturan', 
            ])
            ->brandName('Qutby Creativindo')
            ->viteTheme('resources/css/filament/sales/theme.css')
            ->colors([
                'primary' => Color::Amber,
            ]) // ← TUTUP colors di sini
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => '<style>
                    .fi-topbar {
                        background: linear-gradient(90deg, #6b0f0f, #e0490d);
                    }
                    .fi-topbar .fi-logo,
                    .fi-topbar .fi-logo *,
                    .fi-topbar-brand-text {
                        color: white !important;
                    }
                </style>'
            )

            // Ambil resource/page/widget default (admin)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')

            // Khusus Sales
            ->discoverResources(in: app_path('Filament/Sales/Resources'), for: 'App\\Filament\\Sales\\Resources')
            ->discoverPages(in: app_path('Filament/Sales/Pages'), for: 'App\\Filament\\Sales\\Pages')
            ->discoverWidgets(in: app_path('Filament/Sales/Widgets'), for: 'App\\Filament\\Sales\\Widgets')

            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                SalesStats::class,
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
