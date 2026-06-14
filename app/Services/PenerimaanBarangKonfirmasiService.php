<?php

namespace App\Services;

use App\Models\PenerimaanBarang;
use App\Models\PenerimaanBarangDetail;
use Illuminate\Support\Facades\DB;

class PenerimaanBarangKonfirmasiService
{
    /**
     * Update stok average & status penerimaan.
     * TANPA create jurnal.
     */
    public function konfirmasi(
        PenerimaanBarang $PenerimaanBarang,
        int $userId
    ): void {
        DB::transaction(function () use (
            $PenerimaanBarang,
            $userId
        ) {

            if ($PenerimaanBarang->status === 'dikonfirmasi') {
                app(KartuStokAverageService::class)
                    ->syncPenerimaanBarang($PenerimaanBarang);

                return;
            }

            $PenerimaanBarang->load([
                'details.barang',
                'details.pembelianDetail.penerimaanBarangDetails.penerimaanBarang',
                'pembelian'
            ]);

            $hasIssue = false;

            foreach ($PenerimaanBarang->details as $detail) {

                /** @var PenerimaanBarangDetail $detail */

                $qtyDiterima = (int) $detail->qty_diterima;

                $qtyRusak = (int) ($detail->qty_rusak ?? 0);

                $kondisi = (string) ($detail->getAttribute('kondisi') ?? 'baik');

                $qtyRusak = in_array($kondisi, ['rusak', 'rusak_sebagian', 'rusak_semua'], true) && $qtyRusak === 0
                    ? $qtyDiterima
                    : min($qtyRusak, $qtyDiterima);

                $qtyMasuk = max(0, $qtyDiterima - $qtyRusak);

                $barang = $detail->barang;

                if ($qtyMasuk <= 0 || ! $barang) {
                    continue;
                }

                $qtyOutstandingSebelum = $detail->pembelianDetail
                    ? max(
                        0,
                        (int) $detail->pembelianDetail->qty
                        - (int) $detail->pembelianDetail->qty_diterima
                    )
                    : 0;

                $qtyKurang = max(
                    0,
                    $qtyOutstandingSebelum - $qtyDiterima
                );

                $hasIssue = $hasIssue
                    || $qtyRusak > 0
                    || $qtyKurang > 0;
            }

            /**
             * =========================
             * UPDATE STATUS PENERIMAAN BARANG
             * =========================
             */
            $PenerimaanBarang->update([
                'status' => 'dikonfirmasi',

                'status_penerimaan' => $hasIssue
                    ? 'ada_selisih'
                    : (
                        $PenerimaanBarang->hasSelisihQty()
                            ? 'sebagian'
                            : 'lengkap'
                    ),

                'dikonfirmasi_oleh' => $userId,
                'dikonfirmasi_at' => now(),
            ]);

            app(KartuStokAverageService::class)
                ->syncPenerimaanBarang($PenerimaanBarang);

            /**
             * =========================
             * UPDATE STATUS PEMBELIAN
             * =========================
             */
            $PenerimaanBarang->pembelian()
                ->update([
                    'status_pengiriman' => 'dalam_kirim'
                ]);

            $PenerimaanBarang->pembelian?->refreshStatusPenerimaan();
        });
    }
}
