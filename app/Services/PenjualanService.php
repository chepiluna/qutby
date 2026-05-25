<?php

namespace App\Services;

use App\Models\Penjualan;
use Illuminate\Support\Facades\DB;
use App\Services\KartuStokAverageService;

class PenjualanService
{
    public function proses(Penjualan $penjualan): void
    {
        DB::transaction(function () use ($penjualan) {

            // ambil detail penjualan
            $penjualan->load('details');

            $totalHpp = 0;

            foreach ($penjualan->details as $detail) {

                /**
                 * =========================
                 * PREVIEW HPP AVERAGE
                 * =========================
                 */
                $preview = app(KartuStokAverageService::class)
                    ->previewPenjualan(
                        barangId: $detail->barang_id,
                        qty: $detail->qty
                    );

                $hppTotal = (float) $preview['total_hpp'];

                $totalHpp += $hppTotal;

                /**
                 * =========================
                 * INSERT KARTU STOK AVERAGE
                 * =========================
                 */
                app(KartuStokAverageService::class)
                    ->tambahPenjualan(
                        barangId: $detail->barang_id,

                        tanggal: $penjualan->tanggal_faktur,

                        qty: $detail->qty,

                        keterangan:
                            'Penjualan ' .
                            $penjualan->no_faktur
                    );
            }

            /**
             * =========================
             * UPDATE TOTAL HPP PENJUALAN
             * =========================
             */
            $penjualan->update([
                'total_hpp' => round($totalHpp, 2),
            ]);
        });
    }
}