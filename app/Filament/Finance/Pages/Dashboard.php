<?php

namespace App\Filament\Finance\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Finance\Widgets\FinanceStats;
use App\Filament\Finance\Widgets\Cashflow30DaysChart;
use App\Filament\Finance\Widgets\MonthlyProfitChart;

class Dashboard extends BaseDashboard
    
{
    public function getColumns(): int | array
    {
        return 2; // 🔥 ini kunci
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            FinanceStats::class, // 🔥 paling atas
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            Cashflow30DaysChart::class, // 🔥 ini dulu (atas)
            MonthlyProfitChart::class,  // 🔥 ini terakhir (bawah)
        ];
    }
}