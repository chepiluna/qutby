<?php

namespace App\Filament\Finance\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonthlyProfitChart extends ChartWidget
{
    protected ?string $heading = 'Tren Profit Bulanan';

    protected static ?int $sort = 3; // 🔥 WAJIB

    // 🔥 BIKIN FULL LEBAR (ini penting)
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        // 💰 INCOME dari pembayaran
        $income = DB::table('pembayaran')
            ->selectRaw('DATE_FORMAT(tanggal_bayar, "%Y-%m") as month, SUM(jumlah_bayar) as total_income')
            ->groupBy('month');

        // 💸 EXPENSE dari pengeluaran
        $expense = DB::table('pengeluaran')
            ->selectRaw('DATE_FORMAT(tanggal_pengeluaran, "%Y-%m") as month, SUM(jumlah) as total_expense')
            ->groupBy('month');

        // 🔗 JOIN income & expense
        $data = DB::table(DB::raw("({$income->toSql()}) as i"))
            ->mergeBindings($income)
            ->leftJoinSub($expense, 'e', 'i.month', '=', 'e.month')
            ->selectRaw('
                i.month,
                total_income,
                COALESCE(total_expense, 0) as total_expense,
                (total_income - COALESCE(total_expense, 0)) as profit
            ')
            ->orderBy('i.month')
            ->get();

        // 🎯 Format label bulan
        $labels = $data->pluck('month')->map(function ($m) {
            return Carbon::createFromFormat('Y-m', $m)->format('M Y');
        });

        return [
            'datasets' => [
                [
                    'label' => 'Income',
                    'data' => $data->pluck('total_income'),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59,130,246,0.2)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Expense',
                    'data' => $data->pluck('total_expense'),
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239,68,68,0.2)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Profit',
                    'data' => $data->pluck('profit'),
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34,197,94,0.2)',
                    'tension' => 0.4,
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