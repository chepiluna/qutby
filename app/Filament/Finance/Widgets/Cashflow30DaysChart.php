<?php

namespace App\Filament\Finance\Widgets;

use Carbon\CarbonPeriod;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class Cashflow30DaysChart extends ChartWidget
{
    protected ?string $heading = 'Cashflow Overview (Last 30 Days)';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 1;

    // 🔥 kecilin tinggi chart

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
{
  responsive: true,
  maintainAspectRatio: false,

  plugins: {
    legend: {
      position: 'top'
    },
    tooltip: {
      callbacks: {
        label: function(context) {
          let value = context.raw || 0;
          return context.dataset.label + ': Rp ' + value.toLocaleString('id-ID');
        }
      }
    }
  },

  scales: {
    x: {
      stacked: false,
      grid: {
        display: false
      }
    },
    y: {
      beginAtZero: true,
      ticks: {
        callback: function(value) {
          if (value >= 1000000000) return (value / 1000000000) + 'B';
          if (value >= 1000000) return (value / 1000000) + 'M';
          if (value >= 1000) return (value / 1000) + 'K';
          return value;
        }
      },
      grid: {
        color: 'rgba(0,0,0,0.05)'
      }
    }
  }
}
JS);
    }

    protected function getData(): array
    {
        $start = now()->subDays(29)->startOfDay();
        $end   = now()->endOfDay();

        // 🔥 CASH IN (REAL)
        $incomeRows = DB::table('pembayaran')
            ->selectRaw('DATE(tanggal_bayar) as d, SUM(jumlah_bayar - COALESCE(diskon_termin,0)) as total')
            ->whereBetween('tanggal_bayar', [$start, $end])
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('total', 'd');

        // 🔥 CASH OUT
        $expenseRows = DB::table('pengeluaran')
            ->selectRaw('DATE(tanggal_pengeluaran) as d, SUM(jumlah) as total')
            ->whereBetween('tanggal_pengeluaran', [$start, $end])
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('total', 'd');

        $period = CarbonPeriod::create($start->toDateString(), $end->toDateString());

        $labels  = [];
        $income  = [];
        $expense = [];

        foreach ($period as $date) {
            $d = $date->toDateString();

            $labels[]  = $date->format('d M');
            $income[]  = (float) ($incomeRows[$d] ?? 0);
            $expense[] = (float) ($expenseRows[$d] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Cash In',
                    'data' => $income,
                    'backgroundColor' => '#22c55e',
                    'borderRadius' => 6,
                    'barThickness' => 18,
                ],
                [
                    'label' => 'Cash Out',
                    'data' => $expense,
                    'backgroundColor' => '#ef4444',
                    'borderRadius' => 6,
                    'barThickness' => 18,
                ],
            ],
            'labels' => $labels,
        ];
    }
}