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

        $latestStockIds = DB::table('kartu_stok_average')
            ->select('barang_id', DB::raw('MAX(id) as id'))
            ->whereNotNull('barang_id')
            ->groupBy('barang_id');

        $stokMenipis = Barang::query()
            ->leftJoinSub($latestStockIds, 'latest_stock', function ($join) {
                $join->on('latest_stock.barang_id', '=', 'barang.id');
            })
            ->leftJoin('kartu_stok_average as stok_average', function ($join) {
                $join->on('stok_average.id', '=', 'latest_stock.id');
            })
            ->select([
                'barang.id',
                'barang.nama_barang',
                DB::raw('COALESCE(stok_average.sisa_unit, 0) as stok'),
            ])
            ->orderBy('stok')
            ->orderBy('barang.nama_barang')
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
            'stokMenipis' => $stokMenipis,
            'tunai' => $tunai,
            'kredit' => $kredit,
            'total' => $total,
            'kreditPercentage' => $kreditPercentage,
            'tunaiPercentage' => $tunaiPercentage,
        ];
    }
}
