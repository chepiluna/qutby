<?php

namespace App\Filament\Finance\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonthlyProfitChart extends ChartWidget
{
    protected ?string $heading = 'Tren Profit 12 Bulan Terakhir';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $labels = [];
        $incomeData = [];
        $expenseData = [];
        $profitData = [];

        // Ambil 12 bulan terakhir
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);

            $income = DB::table('pembayaran')
                ->whereNotNull('tanggal_bayar')
                ->whereYear('tanggal_bayar', $date->year)
                ->whereMonth('tanggal_bayar', $date->month)
                ->sum('jumlah_bayar');

            $expense = DB::table('pengeluaran')
                ->whereNotNull('tanggal_pengeluaran')
                ->whereYear('tanggal_pengeluaran', $date->year)
                ->whereMonth('tanggal_pengeluaran', $date->month)
                ->sum('jumlah');

            $profit = $income - $expense;

            $labels[] = $date->translatedFormat('M Y');
            $incomeData[] = (float) $income;
            $expenseData[] = (float) $expense;
            $profitData[] = (float) $profit;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Income',
                    'data' => $incomeData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59,130,246,0.15)',
                    'tension' => 0.4,
                    'fill' => false,
                ],
                [
                    'label' => 'Expense',
                    'data' => $expenseData,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239,68,68,0.15)',
                    'tension' => 0.4,
                    'fill' => false,
                ],
                [
                    'label' => 'Profit',
                    'data' => $profitData,
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34,197,94,0.15)',
                    'tension' => 0.4,
                    'fill' => false,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}