<?php

namespace App\Observers;

use App\Models\Penjualan;
use App\Services\KartuStokService;

class PenjualanObserver
{
    public function saved(Penjualan $penjualan): void
    {
        // Pastikan relasi detail sesuai milikmu: detail/items/rincian
        $items = $penjualan->detail ?? collect();

        app(KartuStokService::class)->syncPenjualan($penjualan, $items);
    }

    public function deleted(Penjualan $penjualan): void
    {
        app(KartuStokService::class)->rollbackPenjualan($penjualan->id);
    }
}
