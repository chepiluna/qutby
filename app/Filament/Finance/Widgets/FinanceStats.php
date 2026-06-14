<?php

namespace App\Filament\Finance\Widgets;

use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use App\Models\Penjualan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FinanceStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 2; // full atas

    protected function getStats(): array
    {
        $data = Cache::remember('finance-stats:' . now()->format('Y-m-d-H-i'), 60, function (): array {
            $start = now()->startOfMonth();
            $end = now()->endOfMonth();

            $totalPembayaran = Pembayaran::query()
                ->whereBetween('created_at', [$start, $end])
                ->select(DB::raw('SUM(jumlah_bayar - COALESCE(diskon_termin,0)) as total'))
                ->value('total');

            $totalPengeluaran = Pengeluaran::query()
                ->whereBetween('created_at', [$start, $end])
                ->sum('jumlah');

            $totalPenjualan = Penjualan::query()
                ->whereBetween('created_at', [$start, $end])
                ->sum('total_netto');

            $totalHpp = Penjualan::query()
                ->whereBetween('created_at', [$start, $end])
                ->sum('total_hpp');

            return [
                'totalPembayaran' => (float) $totalPembayaran,
                'totalPengeluaran' => (float) $totalPengeluaran,
                'labaKotor' => (float) ($totalPenjualan - $totalHpp),
            ];
        });

        $labaKotor = $data['labaKotor'];

        return [
            Stat::make('Total Pembayaran Bulan Ini', 'Rp ' . number_format($data['totalPembayaran'], 0, ',', '.'))
                ->description('Uang masuk')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('success')
                ->extraAttributes([
                    'class' => 'finance-stat finance-stat--success',
                ]),

            Stat::make('Total Pengeluaran Bulan Ini', 'Rp ' . number_format($data['totalPengeluaran'], 0, ',', '.'))
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
