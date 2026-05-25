<?php

namespace App\Filament\Resources\Pembelian\Widgets;

use App\Models\Vendor;
use Filament\Widgets\Widget;

class LaporanPembelianFilter extends Widget
{
    protected string $view = 'filament.widgets.laporan-pembelian-filter';

    protected int | string | array $columnSpan = 'full';

    public string $bulan = '';
    public string $tahun = '';
    public string $vendor_id = '';

    public function mount(): void
    {
        $this->bulan = now()->format('m');
        $this->tahun = (string) now()->year;
    }

    public function getVendorOptions(): array
    {
        return Vendor::orderBy('nama_vendor')
            ->pluck('nama_vendor', 'id')
            ->toArray();
    }

}
