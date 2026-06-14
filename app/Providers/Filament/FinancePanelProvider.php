<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\CustomLogin;
use App\Filament\Finance\Widgets\Cashflow30DaysChart;
use App\Filament\Finance\Widgets\FinanceStats;
use App\Filament\Finance\Widgets\MonthlyProfitChart;
use App\Filament\Finance\Widgets\VendorPaymentDueWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class FinancePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('finance')
            ->path('finance')
            ->authGuard('web')
            ->login(CustomLogin::class)
            ->globalSearch(false)
            ->databaseNotifications()
            ->navigationGroups([
                'Master Data',
                'Transaksi',
                'Laporan',
            ])
            ->brandName('QUTRIX')
            ->brandLogo(asset('images/logoqutby.png'))
            ->brandLogoHeight('2.6rem')
            ->viteTheme('resources/css/filament/finance/theme.css')
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->maxContentWidth(Width::Full)
            ->renderHook(
                PanelsRenderHook::TOPBAR_LOGO_BEFORE,
                fn () => view('filament.finance.partials.qutrix-brand'),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->discoverResources(in: app_path('Filament/Finance/Resources'), for: 'App\\Filament\\Finance\\Resources')
            ->discoverPages(in: app_path('Filament/Finance/Pages'), for: 'App\\Filament\\Finance\\Pages')
            ->widgets([
                FinanceStats::class,
                VendorPaymentDueWidget::class,
                Cashflow30DaysChart::class,
                MonthlyProfitChart::class,
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
