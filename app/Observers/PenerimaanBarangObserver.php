<?php

namespace App\Observers;

use App\Models\PenerimaanBarang;
use App\Services\KartuStokService;

class PenerimaanBarangObserver
{
    public function saved(PenerimaanBarang $penerimaan): void
    {
        $details = $penerimaan->details ?? collect();
        app(KartuStokService::class)->syncPenerimaanBarang($penerimaan, $details);
    }

    public function deleted(PenerimaanBarang $penerimaan): void
    {
        // rollback stok + hapus kartu stok dilakukan di service biar rapi (opsional)
        \App\Models\KartuStok::where('source_type', 'penerimaan_barang')
            ->where('source_id', $penerimaan->id)
            ->delete();
    }
}
