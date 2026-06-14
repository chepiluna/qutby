<?php

namespace App\Filament\Finance\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonthlyProfitChart extends ChartWidget
{
    protected ?string $heading = 'Tren Profit 12 Bulan Terakhir';

    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        return Cache::remember('finance-monthly-profit:' . now()->format('Y-m-d-H-i'), 60, function (): array {
            $labels = [];
            $incomeData = [];
            $expenseData = [];
            $profitData = [];
            $start = Carbon::now()->subMonths(11)->startOfMonth();
            $end = Carbon::now()->endOfMonth();

            $incomeRows = DB::table('pembayaran')
                ->selectRaw("DATE_FORMAT(tanggal_bayar, '%Y-%m') as month_key, SUM(jumlah_bayar) as total")
                ->whereBetween('tanggal_bayar', [$start, $end])
                ->groupBy('month_key')
                ->pluck('total', 'month_key');

            $expenseRows = DB::table('pengeluaran')
                ->selectRaw("DATE_FORMAT(tanggal_pengeluaran, '%Y-%m') as month_key, SUM(jumlah) as total")
                ->whereBetween('tanggal_pengeluaran', [$start, $end])
                ->groupBy('month_key')
                ->pluck('total', 'month_key');

            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $monthKey = $date->format('Y-m');

                $income = (float) ($incomeRows[$monthKey] ?? 0);
                $expense = (float) ($expenseRows[$monthKey] ?? 0);

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
        });
    }

    protected function getType(): string
    {
        return 'line';
    }
}
