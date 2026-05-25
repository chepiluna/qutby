<?php

namespace App\Filament\Widgets;

use App\Models\Pembelian;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class PembelianStats extends BaseWidget
{
    protected function getStats(): array
    {
        $bulanIni = Carbon::now()->month;
        $tahunIni = Carbon::now()->year;

        // Total nilai pembelian bulan ini
        $totalNilai = Pembelian::whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->sum('total_akhir');

        // Jumlah transaksi bulan ini
        $jumlahTransaksi = Pembelian::whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->count();

        // Menunggu penerimaan GRN
        $statusProses = Pembelian::where('status', 'menunggu')->count();

        // Sudah diterima lengkap
        $statusDiterima = Pembelian::where('status', 'selesai')->count();

        return [
            Stat::make('🛒 Total Pembelian Bulan Ini', 'Rp ' . number_format($totalNilai, 0, ',', '.'))
            ->description('Nilai transaksi ' . Carbon::now()->translatedFormat('F Y'))
            ->color('primary'),

            Stat::make('📦 Jumlah Transaksi', $jumlahTransaksi . ' Transaksi')
            ->description('Transaksi di bulan ' . Carbon::now()->translatedFormat('F Y'))
            ->color('info'),

            Stat::make('⏳ Menunggu Proses', $statusProses . ' Transaksi')
            ->description('Status belum diproses')
            ->color('warning'),

            Stat::make('✅ Sudah Diterima', $statusDiterima . ' Transaksi')
            ->description('Status barang diterima')
            ->color('success'),
        ];
    }
}
