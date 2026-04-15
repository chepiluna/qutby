<?php

namespace App\Filament\Sales\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PenjualanChart extends ChartWidget
{
    protected ?string $heading = 'Trend Penjualan (30 hari)';
    protected ?string $pollingInterval = null;
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $start = now()->subDays(29)->startOfDay();

        $rows = DB::table('penjualan')
            ->selectRaw('DATE(tanggal_faktur) as tgl, SUM(total_netto) as nilai')
            ->whereDate('tanggal_faktur', '>=', $start)
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->get();

        $labels = $rows->pluck('tgl')->map(fn ($d) => Carbon::parse($d)->format('d M'))->toArray();
        $data   = $rows->pluck('nilai')->map(fn ($v) => (float) $v)->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Penjualan',
                    'data' => $data,
                    'borderColor' => '#920004',
                    'backgroundColor' => 'rgba(146,0,4,0.15)',
                    'fill' => true,
                    'tension' => 0.35,
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
