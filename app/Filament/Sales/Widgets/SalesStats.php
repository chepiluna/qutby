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

    protected int | array | null $columns = [
        'default' => 1,
        'sm' => 2,
        'xl' => 5,
    ];

    protected function getStats(): array
    {
        $bulan = now()->month;
        $tahun = now()->year;

        $totalPenjualanBulanIni = Penjualan::query()
            ->whereMonth('tanggal_faktur', $bulan)
            ->whereYear('tanggal_faktur', $tahun)
            ->sum('total_netto');

        $totalPiutangBulanIni = Piutang::query()
            ->whereMonth('tanggal_faktur', $bulan)
            ->whereYear('tanggal_faktur', $tahun)
            ->where('status', 'belum_lunas')
            ->sum('sisa_piutang');

        $totalBarangTerjualBulanIni = PenjualanDetail::query()
            ->whereHas('penjualan', fn ($query) => $query
                ->whereMonth('tanggal_faktur', $bulan)
                ->whereYear('tanggal_faktur', $tahun))
            ->sum('qty');

        $totalPenjualanTunaiBulanIni = Penjualan::query()
            ->whereMonth('tanggal_faktur', $bulan)
            ->whereYear('tanggal_faktur', $tahun)
            ->where('cara_bayar', 'tunai')
            ->sum('total_netto');

        $totalPenjualanKreditBulanIni = Penjualan::query()
            ->whereMonth('tanggal_faktur', $bulan)
            ->whereYear('tanggal_faktur', $tahun)
            ->where('cara_bayar', 'kredit')
            ->sum('total_netto');

        $persentaseTunai = $this->percentage($totalPenjualanTunaiBulanIni, $totalPenjualanBulanIni);
        $persentaseKredit = $this->percentage($totalPenjualanKreditBulanIni, $totalPenjualanBulanIni);

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

            Stat::make('Penjualan Tunai Bulan Ini', 'Rp ' . number_format($totalPenjualanTunaiBulanIni, 0, ',', '.'))
                ->description($persentaseTunai . '% dari total')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->extraAttributes(['class' => 'stat-card stat-card--cash']),

            Stat::make('Penjualan Kredit Bulan Ini', 'Rp ' . number_format($totalPenjualanKreditBulanIni, 0, ',', '.'))
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
