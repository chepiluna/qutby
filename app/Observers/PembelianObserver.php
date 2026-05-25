<?php

namespace App\Observers;

use App\Models\Pembelian;

class PembelianObserver
{
    public function creating(Pembelian $pembelian): void
    {
        // Tidak ada proses stok di pesanan pembelian.
    }

    public function saved(Pembelian $pembelian): void
    {
        // Stok pembelian diproses dari Penerimaan Barang, bukan dari perubahan status PO.
    }

    public function deleted(Pembelian $pembelian): void
    {
        app(\App\Services\KartuStokService::class)->rollbackPembelian($pembelian->id);
    }
}
