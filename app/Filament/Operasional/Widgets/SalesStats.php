<?php

namespace App\Filament\Operasional\Widgets;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Piutang;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class SalesStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | array | null $columns = [
        'default' => 1,
        'sm' => 2,
        'xl' => 5,
    ];

    protected function getStats(): array
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $data = Cache::remember('sales-stats:' . now()->format('Y-m-d-H-i'), 60, function () use ($start, $end): array {
            $sales = Penjualan::query()
                ->whereBetween('tanggal_faktur', [$start, $end])
                ->selectRaw('
                    COALESCE(SUM(total_netto), 0) as total,
                    COALESCE(SUM(CASE WHEN cara_bayar = "tunai" THEN total_netto ELSE 0 END), 0) as tunai,
                    COALESCE(SUM(CASE WHEN cara_bayar = "kredit" THEN total_netto ELSE 0 END), 0) as kredit
                ')
                ->first();

            return [
                'total' => (float) ($sales->total ?? 0),
                'tunai' => (float) ($sales->tunai ?? 0),
                'kredit' => (float) ($sales->kredit ?? 0),
                'piutang' => (float) Piutang::query()
                    ->whereBetween('tanggal_faktur', [$start, $end])
                    ->where('status', 'belum_lunas')
                    ->sum('sisa_piutang'),
                'qty' => (int) PenjualanDetail::query()
                    ->join('penjualan', 'penjualan.id', '=', 'penjualan_detail.penjualan_id')
                    ->whereBetween('penjualan.tanggal_faktur', [$start, $end])
                    ->sum('penjualan_detail.qty'),
            ];
        });

        $persentaseTunai = $this->percentage($data['tunai'], $data['total']);
        $persentaseKredit = $this->percentage($data['kredit'], $data['total']);

        return [
            Stat::make('Total Penjualan Bulan Ini', 'Rp ' . number_format($data['total'], 0, ',', '.'))
                ->description('Omzet bulan berjalan')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->extraAttributes(['class' => 'stat-card stat-card--success']),

            Stat::make('Piutang Belum Lunas Bulan Ini', 'Rp ' . number_format($data['piutang'], 0, ',', '.'))
                ->description('Belum lunas')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning')
                ->extraAttributes(['class' => 'stat-card stat-card--warning']),

            Stat::make('Barang Terjual Bulan Ini', number_format($data['qty'], 0, ',', '.'))
                ->description('Total qty')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info')
                ->extraAttributes(['class' => 'stat-card stat-card--info']),

            Stat::make('Penjualan Tunai Bulan Ini', 'Rp ' . number_format($data['tunai'], 0, ',', '.'))
                ->description($persentaseTunai . '% dari total')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->extraAttributes(['class' => 'stat-card stat-card--cash']),

            Stat::make('Penjualan Kredit Bulan Ini', 'Rp ' . number_format($data['kredit'], 0, ',', '.'))
                ->description($persentaseKredit . '% dari total')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('info')
                ->extraAttributes(['class' => 'stat-card stat-card--credit']),
        ];
    }

    private function percentage(float | int $value, float | int $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        return (int) round(($value / $total) * 100);
    }
}
