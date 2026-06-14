<?php

namespace App\Filament\Operasional\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PenjualanChart extends ChartWidget
{
    protected ?string $heading = 'Trend Penjualan (30 hari)';
    protected ?string $pollingInterval = null;
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $start = now()->subDays(29)->startOfDay();

        $rows = DB::table('penjualan')
            ->selectRaw('DATE(tanggal_faktur) as tgl, SUM(total_netto) as nilai')
            ->whereDate('tanggal_faktur', '>=', $start)
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->pluck('nilai', 'tgl');

        $dates = collect(range(0, 29))->map(fn ($day) => $start->copy()->addDays($day));
        $data = $dates
            ->map(fn (Carbon $date) => (float) ($rows[$date->toDateString()] ?? 0))
            ->toArray();
        $labels = $dates
            ->map(fn (Carbon $date) => $date->format('j M'))
            ->toArray();
        $colors = array_fill(0, 30, '#F29A82');
        $colors[29] = '#C91F26';

        return [
            'datasets' => [
                [
                    'label' => 'Penjualan',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
                    'borderRadius' => 3,
                    'borderSkipped' => false,
                    'barPercentage' => 0.72,
                    'categoryPercentage' => 0.72,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'color' => '#111827',
                        'font' => [
                            'size' => 11,
                            'weight' => '600',
                        ],
                        'maxRotation' => 0,
                        'autoSkip' => true,
                        'maxTicksLimit' => 8,
                        'padding' => 8,
                    ],
                    'border' => [
                        'display' => false,
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'color' => 'rgba(15, 23, 42, 0.10)',
                    ],
                    'ticks' => [
                        'display' => false,
                    ],
                    'border' => [
                        'display' => false,
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
