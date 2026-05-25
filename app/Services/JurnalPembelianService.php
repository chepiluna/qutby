<?php

namespace App\Services;

use App\Models\DaftarAkun;
use App\Models\Jurnal;
use App\Models\Pembelian;
use App\Models\PembayaranPembelian;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class JurnalPembelianService
{
    /**
     * Preview: ambil semua transaksi dalam periode dan cek status jurnalnya.
     */
    public function preview(string $tanggalDari, string $tanggalSampai): array
    {
        $dari    = Carbon::parse($tanggalDari)->startOfDay();
        $sampai  = Carbon::parse($tanggalSampai)->endOfDay();

        $pembelians = Pembelian::with(['vendor', 'details'])
            ->whereBetween('tanggal', [$dari, $sampai])
            ->orderBy('tanggal')
            ->get();

        $pembayarans = PembayaranPembelian::with(['vendor', 'fakturPembelian'])
            ->whereBetween('tanggal_pembayaran', [$dari, $sampai])
            ->orderBy('tanggal_pembayaran')
            ->get();

        $rows = [];

        foreach ($pembelians as $pb) {
            $ref       = $pb->nomor ?? ('PB-' . $pb->id);
            $sudahAda  = Jurnal::where('referensi', $ref)->exists();
            $rows[] = [
                'jenis'       => 'Pesanan Pembelian',
                'tanggal'     => $pb->tanggal,
                'referensi'   => $ref,
                'vendor'      => $pb->vendor->nama_vendor ?? '-',
                'nominal'     => (float) ($pb->total_akhir ?? $pb->total ?? 0),
                'ppn'         => (bool)  $pb->ppn,
                'sudah_ada'   => $sudahAda,
                'source_id'   => $pb->id,
                'source_type' => 'pembelian',
            ];
        }

        foreach ($pembayarans as $pay) {
            $ref       = 'PAY-PB-' . $pay->id;
            $sudahAda  = Jurnal::where('referensi', $ref)->exists();
            $rows[] = [
                'jenis'       => 'Pembayaran',
                'tanggal'     => $pay->tanggal_pembayaran,
                'referensi'   => $ref,
                'vendor'      => $pay->vendor->nama_vendor ?? '-',
                'nominal'     => (float) ($pay->nilai_pembayaran ?? 0),
                'ppn'         => false,
                'sudah_ada'   => $sudahAda,
                'bank'        => $pay->bank ?? 'Kas',
                'source_id'   => $pay->id,
                'source_type' => 'pembayaran',
            ];
        }

        return $rows;
    }

    /**
     * Generate jurnal untuk semua transaksi dalam periode (skip yang sudah ada).
     * Mengembalikan ['dibuat' => int, 'dilewati' => int]
     */
    public function generate(string $tanggalDari, string $tanggalSampai): array
    {
        $dari   = Carbon::parse($tanggalDari)->startOfDay();
        $sampai = Carbon::parse($tanggalSampai)->endOfDay();

        $dibuat   = 0;
        $dilewati = 0;

        DB::transaction(function () use ($dari, $sampai, &$dibuat, &$dilewati) {

            // ── 1. Akun-akun standar ──────────────────────────────────────
            $akunPersediaan = DaftarAkun::firstOrCreate(
                ['kode_akun' => '114'],
                ['nama_akun' => 'Persediaan Barang Dagang', 'saldo_normal' => 'debit']
            );
            $akunUtang = DaftarAkun::firstOrCreate(
                ['kode_akun' => '211'],
                ['nama_akun' => 'Utang Usaha', 'saldo_normal' => 'kredit']
            );
            $akunPpn = DaftarAkun::firstOrCreate(
                ['kode_akun' => '115'],
                ['nama_akun' => 'PPN Masukan', 'saldo_normal' => 'debit']
            );

            // ── 2. Jurnal Pesanan Pembelian (Perpetual) ───────────────────
            $pembelians = Pembelian::with(['vendor', 'details'])
                ->whereBetween('tanggal', [$dari, $sampai])
                ->orderBy('tanggal')
                ->get();

            foreach ($pembelians as $pb) {
                $ref = $pb->nomor ?? ('PB-' . $pb->id);

                if (Jurnal::where('referensi', $ref)->exists()) {
                    $dilewati++;
                    continue;
                }

                $total        = (float) ($pb->total ?? 0);
                $diskonNom    = $total * ((float) ($pb->diskon ?? 0) / 100);
                $dpp          = max($total - $diskonNom, 0);
                $ppnNominal   = $pb->ppn ? round($dpp * 0.11, 2) : 0;
                $totalAkhir   = (float) ($pb->total_akhir ?? 0);
                if ($totalAkhir <= 0) {
                    $totalAkhir = $dpp + $ppnNominal;
                }

                // Header Jurnal
                $jurnal = Jurnal::create([
                    'tanggal'    => $pb->tanggal,
                    'referensi'  => $ref,
                    'keterangan' => 'Pembelian barang dagang secara kredit',
                ]);

                // [D] Persediaan Barang Dagang ← metode perpetual
                $jurnal->details()->create([
                    'daftar_akun_id' => $akunPersediaan->id,
                    'keterangan'     => 'Persediaan masuk - ' . $ref,
                    'debit'          => $dpp,
                    'kredit'         => 0,
                ]);

                // [D] PPN Masukan (jika ada PPN)
                if ($pb->ppn && $ppnNominal > 0) {
                    $jurnal->details()->create([
                        'daftar_akun_id' => $akunPpn->id,
                        'keterangan'     => 'PPN Masukan - ' . $ref,
                        'debit'          => $ppnNominal,
                        'kredit'         => 0,
                    ]);
                }

                // [K] Utang Usaha
                $jurnal->details()->create([
                    'daftar_akun_id' => $akunUtang->id,
                    'keterangan'     => 'Utang ke ' . ($pb->vendor->nama_vendor ?? '-') . ' - ' . $ref,
                    'debit'          => 0,
                    'kredit'         => $totalAkhir,
                ]);

                $dibuat++;
            }

            // ── 3. Jurnal Pembayaran Pembelian ────────────────────────────
            $pembayarans = PembayaranPembelian::with(['vendor'])
                ->whereBetween('tanggal_pembayaran', [$dari, $sampai])
                ->orderBy('tanggal_pembayaran')
                ->get();

            foreach ($pembayarans as $pay) {
                $ref = 'PAY-PB-' . $pay->id;

                if (Jurnal::where('referensi', $ref)->exists()) {
                    $dilewati++;
                    continue;
                }

                $namaBank  = $pay->bank ? ucwords(strtolower($pay->bank)) : 'Kas';
                $labelBank = $pay->bank ? 'Bank ' . $namaBank : 'Kas';

                // Ambil/buat akun Kas atau Bank
                $akunKas = DaftarAkun::firstOrCreate(
                    ['nama_akun' => $labelBank],
                    [
                        'kode_akun'    => 'KAS-' . strtoupper(substr($namaBank, 0, 3)),
                        'saldo_normal' => 'debit',
                    ]
                );

                $nominal = (float) ($pay->nilai_pembayaran ?? 0);

                // Header Jurnal
                $jurnal = Jurnal::create([
                    'tanggal'    => $pay->tanggal_pembayaran,
                    'referensi'  => $ref,
                    'keterangan' => 'Pembayaran ke ' . ($pay->vendor->nama_vendor ?? '-') . ' via ' . $labelBank,
                ]);

                // [D] Utang Usaha (berkurang)
                $jurnal->details()->create([
                    'daftar_akun_id' => $akunUtang->id,
                    'keterangan'     => 'Pelunasan utang - ' . $ref,
                    'debit'          => $nominal,
                    'kredit'         => 0,
                ]);

                // [K] Kas / Bank (keluar)
                $jurnal->details()->create([
                    'daftar_akun_id' => $akunKas->id,
                    'keterangan'     => 'Keluar via ' . $labelBank . ' - ' . $ref,
                    'debit'          => 0,
                    'kredit'         => $nominal,
                ]);

                $dibuat++;
            }
        });

        return ['dibuat' => $dibuat, 'dilewati' => $dilewati];
    }
}
