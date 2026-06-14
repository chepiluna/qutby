<?php

namespace App\Filament\Operasional\Widgets;

use App\Models\Pembelian;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class PembelianStats extends BaseWidget
{
    protected function getStats(): array
    {
        $now = Carbon::now();
        $start = $now->copy()->startOfMonth();
        $end = $now->copy()->endOfMonth();

        $data = Cache::remember('purchase-stats:' . $now->format('Y-m-d-H-i'), 60, function () use ($start, $end): array {
            $monthly = Pembelian::query()
                ->whereBetween('tanggal', [$start, $end])
                ->selectRaw('COALESCE(SUM(total_akhir), 0) as total, COUNT(*) as jumlah')
                ->first();

            return [
                'total' => (float) ($monthly->total ?? 0),
                'jumlah' => (int) ($monthly->jumlah ?? 0),
                'proses' => Pembelian::query()->where('status', 'menunggu')->count(),
                'diterima' => Pembelian::query()->where('status', 'selesai')->count(),
            ];
        });

        return [
            Stat::make('Total Pembelian Bulan Ini', 'Rp ' . number_format($data['total'], 0, ',', '.'))
                ->description('Nilai transaksi ' . $now->translatedFormat('F Y'))
                ->color('primary'),

            Stat::make('Jumlah Transaksi', $data['jumlah'] . ' Transaksi')
                ->description('Transaksi di bulan ' . $now->translatedFormat('F Y'))
                ->color('info'),

            Stat::make('Menunggu Proses', $data['proses'] . ' Transaksi')
                ->description('Status belum diproses')
                ->color('warning'),

            Stat::make('Sudah Diterima', $data['diterima'] . ' Transaksi')
                ->description('Status barang diterima')
                ->color('success'),
        ];
    }
}
