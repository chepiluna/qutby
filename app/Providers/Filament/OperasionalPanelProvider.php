<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\CustomLogin;
use App\Filament\Operasional\Widgets\SalesStats;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Operasional\Pages\Dashboard;
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

class OperasionalPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('operasional')
            ->path('operasional')
            ->authGuard('web')
            ->login(CustomLogin::class)
            ->globalSearch(false)
            ->databaseNotifications()
            ->navigationGroups([
                'Master Data',
                'Transaksi',
                'Laporan',
                'Pengaturan', 
            ])
            
            ->brandName('QUTRIX')
            ->brandLogo(asset('images/logoqutby.png'))
            ->brandLogoHeight('2.6rem')
            ->viteTheme('resources/css/filament/operasional/theme.css')
            ->colors([
                'primary' => Color::Amber,
            ]) // ← TUTUP colors di sini
            ->renderHook(
                PanelsRenderHook::TOPBAR_LOGO_BEFORE,
                fn () => view('filament.operasional.partials.qutrix-brand'),
            )

            // Ambil resource/page/widget default (admin)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')

            // Khusus Operasional
            ->discoverResources(in: app_path('Filament/Operasional/Resources'), for: 'App\\Filament\\Operasional\\Resources')
            ->discoverPages(in: app_path('Filament/Operasional/Pages'), for: 'App\\Filament\\Operasional\\Pages')
            ->discoverWidgets(in: app_path('Filament/Operasional/Widgets'), for: 'App\\Filament\\Operasional\\Widgets')

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
