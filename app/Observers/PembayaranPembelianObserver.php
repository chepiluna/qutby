<?php

namespace App\Observers;

use App\Models\PembayaranPembelian;
use App\Models\KartuUtang;

class PembayaranPembelianObserver
{
    public function created(PembayaranPembelian $pembayaran): void
    {
        // ❗ cegah dobel posting
        if (KartuUtang::where('no_bukti', $pembayaran->nomor_pembayaran_vendor)->exists()) {
            return;
        }

        $saldoSebelumnya = KartuUtang::where('vendor_id', $pembayaran->vendor_id)
            ->orderByDesc('id')
            ->value('saldo') ?? 0;

        $debet = (float) $pembayaran->total_netto;
        $saldoBaru = $saldoSebelumnya + $debet;

        KartuUtang::create([
            'vendor_id'  => $pembayaran->vendor_id,
            'tanggal'    => $pembayaran->tanggal_pembayaran,
            'no_bukti'   => $pembayaran->nomor_pembayaran_vendor,
            'keterangan' => 'Pembayaran Utang ' . $pembayaran->nomor_pembayaran_vendor,
            'debet'      => $debet,
            'kredit'     => 0,
            'saldo'      => $saldoBaru,
        ]);
    }
}
