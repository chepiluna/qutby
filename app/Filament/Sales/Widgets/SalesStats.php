<?php

namespace App\Filament\Sales\Widgets;

use App\Models\Penjualan;
use App\Models\Piutang;
use App\Models\PenjualanDetail;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $bulan = now()->month;
        $tahun = now()->year;

        $totalPenjualanBulanIni = Penjualan::query()
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->sum('total_netto');

        $totalPiutangBulanIni = Piutang::query()
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->where('status', 'belum_lunas')
            ->sum('sisa_piutang');

        $totalBarangTerjualBulanIni = PenjualanDetail::query()
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->sum('qty');

        return [
            Stat::make('Total Penjualan Bulan Ini', 'Rp ' . number_format($totalPenjualanBulanIni, 0, ',', '.'))
                ->description('Omzet bulan berjalan')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->extraAttributes(['class' => 'stat-card stat-card--success']),

            Stat::make('Piutang Belum Lunas Bulan Ini', 'Rp ' . number_format($totalPiutangBulanIni, 0, ',', '.'))
                ->description('Belum lunas')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning')
                ->extraAttributes(['class' => 'stat-card stat-card--warning']),

            Stat::make('Barang Terjual Bulan Ini', number_format($totalBarangTerjualBulanIni, 0, ',', '.'))
                ->description('Total qty')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info')
                ->extraAttributes(['class' => 'stat-card stat-card--info']),
        ];
    }
}
