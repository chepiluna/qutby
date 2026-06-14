<?php

namespace App\Filament\Operasional\Pages;

use App\Models\Barang;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Dashboard extends Page
{
    protected static ?string $navigationLabel = 'Dashboard';

    protected static string|\BackedEnum|null $navigationIcon = 'tabler-layout-dashboard';

    protected static ?int $navigationSort = 0;

    protected static ?string $title = 'Dashboard Penjualan & Pembelian';

    protected static ?string $slug = '/';

    protected string $view = 'filament.operasional.pages.dashboard';

    public function getDashboardData(): array
    {
        return Cache::remember('operasional-dashboard-data', now()->addMinutes(5), function (): array {
            $now = now();
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfMonth();

            $sales = $this->getSalesMetrics($start, $end);
            $purchases = $this->getPurchaseMetrics($start, $end);
            $grossProfit = $sales['total'] - $purchases['total'];

            return [
                'period' => $this->formatIndonesianMonth($now),
                'sales' => $sales,
                'purchases' => $purchases,
                'grossProfit' => [
                    'amount' => $grossProfit,
                    'margin' => $sales['total'] > 0 ? ($grossProfit / $sales['total']) * 100 : 0,
                ],
                'topSold' => $this->getTopSold($start, $end),
                'topBought' => $this->getTopBought($start, $end),
                'lowStock' => $this->getLowStock(),
                'dueDebts' => $this->getDueDebts($end),
                'trend' => $this->getTrendData(),
            ];
        });
    }

    protected function getSalesMetrics(Carbon $start, Carbon $end): array
    {
        $base = Penjualan::query()->whereBetween('tanggal_faktur', [$start, $end]);

        $total = (float) (clone $base)->sum('total_netto');
        $cash = (float) (clone $base)->where('cara_bayar', 'tunai')->sum('total_netto');
        $credit = (float) (clone $base)->where('cara_bayar', 'kredit')->sum('total_netto');
        $receivables = (float) DB::table('piutang')->where('status', 'belum_lunas')->sum('sisa_piutang');
        $qty = (int) PenjualanDetail::query()
            ->whereHas('penjualan', fn ($query) => $query->whereBetween('tanggal_faktur', [$start, $end]))
            ->sum('qty');

        return [
            'total' => $total,
            'receivables' => $receivables,
            'qty' => $qty,
            'cash' => $cash,
            'credit' => $credit,
            'cashPercent' => $this->percentage($cash, $total),
            'creditPercent' => $this->percentage($credit, $total),
        ];
    }

    protected function getPurchaseMetrics(Carbon $start, Carbon $end): array
    {
        $base = Pembelian::query()->whereBetween('tanggal', [$start, $end]);

        $total = (float) (clone $base)->sum('total_akhir');
        $cash = (float) (clone $base)->where('syarat_pembayaran', 'tunai')->sum('total_akhir');
        $credit = (float) (clone $base)->where('syarat_pembayaran', 'kredit')->sum('total_akhir');
        $debt = (float) (clone $base)
            ->where('syarat_pembayaran', 'kredit')
            ->where(fn ($query) => $query->whereNull('status')->orWhere('status', '!=', 'lunas'))
            ->sum('total_akhir');
        $receivedQty = (int) DB::table('penerimaan_barang_details')
            ->join('penerimaan_barangs', 'penerimaan_barangs.id', '=', 'penerimaan_barang_details.grn_id')
            ->where('penerimaan_barangs.status', 'dikonfirmasi')
            ->whereBetween('penerimaan_barangs.tanggal_terima', [$start, $end])
            ->sum('penerimaan_barang_details.qty_diterima');

        return [
            'total' => $total,
            'debt' => $debt,
            'receivedQty' => $receivedQty,
            'cash' => $cash,
            'credit' => $credit,
            'cashPercent' => $this->percentage($cash, $total),
            'creditPercent' => $this->percentage($credit, $total),
        ];
    }

    protected function getTopSold(Carbon $start, Carbon $end): Collection
    {
        return PenjualanDetail::query()
            ->join('penjualan', 'penjualan.id', '=', 'penjualan_detail.penjualan_id')
            ->leftJoin('barang', 'barang.id', '=', 'penjualan_detail.barang_id')
            ->whereBetween('penjualan.tanggal_faktur', [$start, $end])
            ->select([
                DB::raw('COALESCE(barang.nama_barang, "-") as name'),
                DB::raw('COALESCE(SUM(penjualan_detail.qty), 0) as qty'),
                DB::raw('COALESCE(SUM(penjualan_detail.subtotal), 0) as amount'),
            ])
            ->groupBy('barang.id', 'barang.nama_barang')
            ->orderByDesc('qty')
            ->limit(3)
            ->get();
    }

    protected function getTopBought(Carbon $start, Carbon $end): Collection
    {
        return PembelianDetail::query()
            ->join('pembelians', 'pembelians.id', '=', 'pembelian_details.pembelian_id')
            ->leftJoin('barang', 'barang.id', '=', 'pembelian_details.barang_id')
            ->whereBetween('pembelians.tanggal', [$start, $end])
            ->select([
                DB::raw('COALESCE(barang.nama_barang, "-") as name'),
                DB::raw('COALESCE(SUM(pembelian_details.qty), 0) as qty'),
                DB::raw('COALESCE(SUM(pembelian_details.subtotal), 0) as amount'),
            ])
            ->groupBy('barang.id', 'barang.nama_barang')
            ->orderByDesc('qty')
            ->limit(3)
            ->get();
    }

    protected function getLowStock(): Collection
    {
        $latestStockIds = DB::table('kartu_stok_average')
            ->select('barang_id', DB::raw('MAX(id) as id'))
            ->whereNotNull('barang_id')
            ->groupBy('barang_id');

        return Barang::query()
            ->leftJoinSub($latestStockIds, 'latest_stock', fn ($join) => $join->on('latest_stock.barang_id', '=', 'barang.id'))
            ->leftJoin('kartu_stok_average as stok_average', 'stok_average.id', '=', 'latest_stock.id')
            ->select([
                'barang.nama_barang as name',
                DB::raw('COALESCE(stok_average.sisa_unit, 0) as stock'),
            ])
            ->whereRaw('COALESCE(stok_average.sisa_unit, 0) < 10')
            ->orderBy('stock')
            ->orderBy('barang.nama_barang')
            ->limit(5)
            ->get();
    }

    protected function getDueDebts(Carbon $end): Collection
    {
        return DB::table('pembelians')
            ->leftJoin('vendors', 'vendors.id', '=', 'pembelians.vendor_id')
            ->where('pembelians.syarat_pembayaran', 'kredit')
            ->where(fn ($query) => $query->whereNull('pembelians.status')->orWhere('pembelians.status', '!=', 'lunas'))
            ->select([
                'pembelians.tanggal',
                'pembelians.total_akhir',
                'vendors.id as vendor_id',
                'vendors.periode_pembayaran',
                DB::raw('COALESCE(vendors.nama_vendor, "-") as supplier'),
            ])
            ->get()
            ->map(function ($row): object {
                $dueDate = Carbon::parse($row->tanggal)
                    ->addMonthsNoOverflow((int) ($row->periode_pembayaran ?: 1));

                return (object) [
                    'supplier' => $row->supplier,
                    'amount' => (float) ($row->total_akhir ?? 0),
                    'due_date' => $dueDate->toDateString(),
                ];
            })
            ->filter(fn (object $row): bool => Carbon::parse($row->due_date)->lte($end))
            ->groupBy('supplier')
            ->map(fn (Collection $rows, string $supplier): object => (object) [
                'supplier' => $supplier,
                'amount' => $rows->sum('amount'),
                'due_date' => $rows->min('due_date'),
            ])
            ->sortBy('due_date')
            ->take(5)
            ->values();
    }

    protected function getTrendData(): array
    {
        $start = now()->copy()->subDays(29)->startOfDay();
        $end = now()->copy()->endOfDay();

        $sales = Penjualan::query()
            ->whereBetween('tanggal_faktur', [$start, $end])
            ->selectRaw('DATE(tanggal_faktur) as date, SUM(total_netto) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $purchases = Pembelian::query()
            ->whereBetween('tanggal', [$start, $end])
            ->selectRaw('DATE(tanggal) as date, SUM(total_akhir) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $labels = [];
        $salesData = [];
        $purchaseData = [];

        foreach (range(0, 29) as $offset) {
            $date = $start->copy()->addDays($offset);
            $key = $date->format('Y-m-d');

            $labels[] = $date->format('d M');
            $salesData[] = (float) ($sales[$key] ?? 0);
            $purchaseData[] = (float) ($purchases[$key] ?? 0);
        }

        return [
            'labels' => $labels,
            'sales' => $salesData,
            'purchases' => $purchaseData,
        ];
    }

    protected function percentage(float $part, float $total): int
    {
        return $total > 0 ? (int) round(($part / $total) * 100) : 0;
    }

    protected function formatIndonesianMonth(Carbon $date): string
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $months[(int) $date->format('n')] . ' ' . $date->format('Y');
    }

}
