<?php

namespace App\Filament\Finance\Widgets;

use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use App\Models\Penjualan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB; // ⬅️ tambahin di atas

class FinanceStats extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 2; // full atas

    protected function getStats(): array
    {
        $bulan = now()->month;
        $tahun = now()->year;

        $totalPembayaran = Pembayaran::query()
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->select(DB::raw('SUM(jumlah_bayar - COALESCE(diskon_termin,0)) as total'))
            ->value('total');

        $totalPengeluaran = Pengeluaran::query()
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->sum('jumlah');

        $totalPenjualan = Penjualan::query()
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->sum('total_netto');

        $totalHpp = Penjualan::query()
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->sum('total_hpp');

        $labaKotor = $totalPenjualan - $totalHpp;

        return [
            Stat::make('Total Pembayaran Bulan Ini', 'Rp ' . number_format($totalPembayaran, 0, ',', '.'))
                ->description('Uang masuk')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('success')
                ->extraAttributes([
                    'class' => 'finance-stat finance-stat--success',
                ]),

            Stat::make('Total Pengeluaran Bulan Ini', 'Rp ' . number_format($totalPengeluaran, 0, ',', '.'))
                ->description('Biaya keluar')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('danger')
                ->extraAttributes([
                    'class' => 'finance-stat finance-stat--danger',
                ]),

            Stat::make('Laba Kotor Bulan Ini', 'Rp ' . number_format($labaKotor, 0, ',', '.'))
                ->description($labaKotor >= 0 ? 'Profit' : 'Rugi')
                ->descriptionIcon(
                    $labaKotor >= 0
                        ? 'heroicon-m-arrow-trending-up'
                        : 'heroicon-m-arrow-trending-down'
                )
                ->color($labaKotor >= 0 ? 'success' : 'danger')
                ->extraAttributes([
                    'class' => $labaKotor >= 0
                        ? 'finance-stat finance-stat--success'
                        : 'finance-stat finance-stat--danger',
                ]),
        ];
    }
}
