<?php

namespace App\Filament\Pages;

use App\Models\PembelianDetail;
use App\Models\Vendor;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class LaporanPembelian extends Page
{
    protected static array $allowedRoles = ['finance', 'operasional'];

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-document-chart-bar';
    protected static string|\UnitEnum|null   $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Laporan Pembelian';
    protected static ?string $title           = 'Laporan Pembelian';
    protected static ?string $slug            = 'laporan-pembelian';
    protected static ?int    $navigationSort  = 1;

    protected string $view = 'filament.pages.laporan-pembelian';

    public string $bulan     = '';
    public string $tahun     = '';
    public string $vendor_id = '';

    public function mount(): void
    {
        $this->bulan = now()->format('m');
        $this->tahun = now()->format('Y');
    }

    public function getTitle(): string { return 'Laporan Pembelian'; }
    public function getHeading(): string { return ''; }
    public function getBreadcrumb(): string { return 'Laporan Pembelian'; }

    protected function getHeaderActions(): array { return []; }

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(Filament::getCurrentPanel()?->getId(), ['finance', 'sales'], true)
            && in_array(Auth::user()?->role, ['admin', 'finance', 'operasional', 'sales'], true);
    }

    public static function canAccess(): bool
    {
        return in_array(Filament::getCurrentPanel()?->getId(), ['finance', 'sales'], true)
            && in_array(Auth::user()?->role, ['admin', 'finance', 'operasional', 'sales'], true);
    }

    public function getPeriodeLabel(): string
    {
        $bulanNama = [
            '01' => 'Januari',  '02' => 'Februari', '03' => 'Maret',
            '04' => 'April',    '05' => 'Mei',       '06' => 'Juni',
            '07' => 'Juli',     '08' => 'Agustus',   '09' => 'September',
            '10' => 'Oktober',  '11' => 'November',  '12' => 'Desember',
        ];
        return ($bulanNama[$this->bulan] ?? '-') . ' ' . $this->tahun;
    }

    public function getVendorOptions(): \Illuminate\Support\Collection
    {
        return Vendor::orderBy('nama_vendor')->get(['id', 'nama_vendor']);
    }

    public function getStatusOptions(): array
    {
        return [
            ''           => 'Semua Status',
            'menunggu'   => 'Menunggu',
            'partial'    => 'Partial',
            'selesai'    => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
        ];
    }

    public function getLaporanRows(): \Illuminate\Support\Collection
    {
        return PembelianDetail::with([
                'barang',
                'pembelian.vendor',
            ])
            ->whereHas('pembelian', function ($query) {
                if ($this->bulan && $this->tahun) {
                    $query->whereMonth('tanggal', $this->bulan)
                        ->whereYear('tanggal', $this->tahun);
                }

                if ($this->vendor_id) {
                    $query->where('vendor_id', $this->vendor_id);
                }

                $query->where('status', '!=', 'dibatalkan');
            })
            ->get()
            ->filter(fn (PembelianDetail $detail) => $detail->pembelian)
            ->sortBy(fn (PembelianDetail $detail) => (optional($detail->pembelian->tanggal)->format('Y-m-d') ?? '') . '-' . str_pad((string) $detail->id, 10, '0', STR_PAD_LEFT))
            ->map(function (PembelianDetail $detail) {
                $hargaSatuan = (float) ($detail->harga_satuan ?? $detail->harga ?? $detail->hpp ?? 0);
                $jumlah = (int) ($detail->qty ?? 0);

                return [
                    'tanggal' => $detail->pembelian->tanggal,
                    'nomor_po' => $detail->pembelian->nomor ?? '-',
                    'vendor' => $detail->pembelian->vendor_manual
                        ?: ($detail->pembelian->vendor->nama_vendor ?? '-'),
                    'nama_barang' => $detail->barang->nama_barang ?? '-',
                    'jumlah' => $jumlah,
                    'harga_satuan' => $hargaSatuan,
                    'total' => $jumlah * $hargaSatuan,
                ];
            })
            ->values();
    }

    public function getGrandTotal(): float
    {
        return $this->getLaporanRows()
            ->sum('total');
    }

    public function getPdfUrl(): string
    {
        return route('laporan-pembelian.pdf', [
            'bulan' => $this->bulan,
            'tahun' => $this->tahun,
            'vendor_id' => $this->vendor_id,
        ]);
    }

    public function getExcelUrl(): string
    {
        return route('laporan-pembelian.excel', [
            'bulan' => $this->bulan,
            'tahun' => $this->tahun,
            'vendor_id' => $this->vendor_id,
        ]);
    }
}
