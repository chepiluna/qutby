<?php

namespace App\Filament\Sales\Widgets;

use App\Models\Barang;
use App\Models\Penjualan;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class TopBarangTerjual extends Widget
{
    protected static ?int $sort = 2;

    protected string $view = 'filament.sales.widgets.top-barang-terjual';

    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $bulan = now()->month;
        $tahun = now()->year;

        $topBarang = Barang::query()
            ->leftJoin('penjualan_detail', 'penjualan_detail.barang_id', '=', 'barang.id')
            ->select([
                'barang.id',
                'barang.nama_barang',
                DB::raw('COALESCE(SUM(penjualan_detail.qty),0) as qty'),
                DB::raw('COALESCE(SUM(penjualan_detail.subtotal),0) as omzet'),
            ])
            ->groupBy('barang.id', 'barang.nama_barang')
            ->orderByDesc('qty')
            ->limit(3)
            ->get();

        $tunai = (float) Penjualan::query()
            ->whereMonth('tanggal_faktur', $bulan)
            ->whereYear('tanggal_faktur', $tahun)
            ->where('cara_bayar', 'tunai')
            ->sum('total_netto');

        $kredit = (float) Penjualan::query()
            ->whereMonth('tanggal_faktur', $bulan)
            ->whereYear('tanggal_faktur', $tahun)
            ->where('cara_bayar', 'kredit')
            ->sum('total_netto');

        $total = $tunai + $kredit;
        $kreditPercentage = $total > 0 ? (int) round(($kredit / $total) * 100) : 0;
        $tunaiPercentage = max(0, 100 - $kreditPercentage);

        return [
            'topBarang' => $topBarang,
            'tunai' => $tunai,
            'kredit' => $kredit,
            'total' => $total,
            'kreditPercentage' => $kreditPercentage,
            'tunaiPercentage' => $tunaiPercentage,
        ];
    }
}
