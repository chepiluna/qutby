<?php

namespace App\Filament\Finance\Widgets;

use Carbon\CarbonPeriod;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class Cashflow30DaysChart extends ChartWidget
{
    protected ?string $heading = 'Cashflow 30 Hari Terakhir';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'line';
    }

    // Ini untuk background putih di dalam kotak chart (canvas) jadi warna palette kamu
    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
{
  plugins: [
    {
      id: 'canvasBackground',
      beforeDraw: (chart, args, options) => {
        const { ctx } = chart;
        ctx.save();
        ctx.globalCompositeOperation = 'destination-over';
        ctx.fillStyle = '#3c0000';
        ctx.fillRect(0, 0, chart.width, chart.height);
        ctx.restore();
      },
    },
  ],
  plugins: {
    legend: {
      labels: {
        color: '#ff8478',
      },
    },
  },
  scales: {
    x: {
      ticks: { color: '#e71300ff' },
      grid: { color: 'rgba(0, 0, 0, 0.12)' },
    },
    y: {
      ticks: { color: '#ff1500ff' },
      grid: { color: 'rgba(0, 0, 0, 0.12)' },
    },
  },
}
JS);
    }

    protected function getData(): array
    {
        $start = now()->subDays(29)->startOfDay();
        $end   = now()->endOfDay();

        $incomeRows = DB::table('penjualan')
            ->selectRaw('DATE(tanggal_faktur) as d, SUM(total_netto) as total')
            ->whereBetween('tanggal_faktur', [$start, $end])
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('total', 'd');

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
                    'label' => 'Pemasukan',
                    'data' => $income,
                    'borderColor' => '#e41a08ff',
                    'backgroundColor' => 'rgba(0, 0, 0, 0.18)',
                    'pointBackgroundColor' => '#ff1500ff',
                    'fill' => true,
                    'tension' => 0,
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $expense,
                    'borderColor' => '#8f0801ff',
                    'backgroundColor' => 'rgba(0, 0, 0, 0.18)',
                    'pointBackgroundColor' => '#ff0d00ff',
                    'fill' => true,
                    'tension' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
