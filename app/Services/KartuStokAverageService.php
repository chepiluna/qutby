<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\KartuStokAverage;
use Carbon\Carbon;

class KartuStokAverageService
{
    public function getSaldoSaatIni(int $barangId): array
    {
        $last = KartuStokAverage::query()
            ->where('barang_id', $barangId)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->first();

        return [
            'sisa_unit' => (int) ($last?->sisa_unit ?? 0),
            'harga_rata_rata' => (float) ($last?->harga_rata_rata ?? 0),
            'nilai_persediaan' => (float) ($last?->nilai_persediaan ?? 0),
        ];
    }

    public function getHargaJual(
        int $barangId,
        float $gpm = 55
    ): float {
        $saldo = $this->getSaldoSaatIni($barangId);

        $hpp = (float) $saldo['harga_rata_rata'];

        return round(
            $hpp + ($hpp * $gpm / 100),
            2
        );
    }

    public function hitungRataRata(
        int $barangId,
        int $qtyBeli,
        float $hargaBeli
    ): float {
        $saldo = $this->getSaldoSaatIni($barangId);

        $nilaiLama = (float) $saldo['nilai_persediaan'];

        $unitLama = (int) $saldo['sisa_unit'];

        $nilaiMasuk = $qtyBeli * $hargaBeli;

        $unitBaru = $unitLama + $qtyBeli;

        return $unitBaru > 0
            ? round(
                ($nilaiLama + $nilaiMasuk) / $unitBaru,
                2
            )
            : 0;
    }

    public function tambahPembelian(
        int $barangId,
        string $tanggal,
        int $qty,
        float $hargaBeli,
        ?string $keterangan = null
    ): KartuStokAverage {

        $saldo = $this->getSaldoSaatIni($barangId);

        $hargaRataRata = $this->hitungRataRata(
            $barangId,
            $qty,
            $hargaBeli
        );

        $sisaUnit = (int) $saldo['sisa_unit'] + $qty;

        $nilaiPersediaan = round(
            $sisaUnit * $hargaRataRata,
            2
        );

        return KartuStokAverage::create([
            'barang_id' => $barangId,
            'tanggal' => $tanggal,
            'keterangan' => $keterangan ?: 'Pembelian',
            'jenis' => 'beli',

            'qty' => $qty,

            'harga_beli' => $hargaBeli,

            'hpp_per_unit' => 0,
            'hpp_total' => 0,

            'sisa_unit' => $sisaUnit,

            'harga_rata_rata' => $hargaRataRata,

            'nilai_persediaan' => $nilaiPersediaan,
        ]);
    }

    public function tambahPenjualan(
        int $barangId,
        string $tanggal,
        int $qty,
        ?string $keterangan = null
    ): KartuStokAverage {

        $saldo = $this->getSaldoSaatIni($barangId);

        if ($qty > (int) $saldo['sisa_unit']) {

            throw new \InvalidArgumentException(
                'Qty melebihi stok tersedia'
            );
        }

        $hppPerUnit = (float) $saldo['harga_rata_rata'];

        $sisaUnit = (int) $saldo['sisa_unit'] - $qty;

        return KartuStokAverage::create([
            'barang_id' => $barangId,
            'tanggal' => $tanggal,
            'keterangan' => $keterangan ?: 'Penjualan',
            'jenis' => 'jual',

            'qty' => $qty,

            'harga_beli' => 0,

            'hpp_per_unit' => $hppPerUnit,

            'hpp_total' => round(
                $qty * $hppPerUnit,
                2
            ),

            'sisa_unit' => $sisaUnit,

            'harga_rata_rata' => $hppPerUnit,

            'nilai_persediaan' => round(
                $sisaUnit * $hppPerUnit,
                2
            ),
        ]);
    }

    public function previewPenjualan(
        int $barangId,
        int $qty
    ): array {

        $saldo = $this->getSaldoSaatIni($barangId);

        $stokSaatIni = (int) $saldo['sisa_unit'];

        $hppPerUnit = (float) $saldo['harga_rata_rata'];

        if ($qty > $stokSaatIni) {

            return [
                'valid' => false,

                'message' => 'Qty melebihi stok tersedia',

                'hpp_per_unit' => $hppPerUnit,

                'total_hpp' => 0,

                'stok_setelah' => $stokSaatIni,
            ];
        }

        return [
            'valid' => true,

            'hpp_per_unit' => $hppPerUnit,

            'total_hpp' => round(
                $qty * $hppPerUnit,
                2
            ),

            'stok_setelah' => $stokSaatIni - $qty,
        ];
    }

    public function getCards(
        string $bulan,
        string $tahun,
        ?int $barangId = null
    ): array {

        $tglMulai = Carbon::createFromDate(
            (int) $tahun,
            (int) $bulan,
            1
        )->startOfMonth();

        $tglAkhir = $tglMulai
            ->copy()
            ->endOfMonth();

        return Barang::query()
            ->when(
                $barangId,
                fn ($query) =>
                $query->where('id', $barangId)
            )
            ->orderBy('nama_barang')
            ->get()
            ->map(fn ($barang) =>
                $this->buildCard(
                    $barang,
                    $tglMulai,
                    $tglAkhir
                )
            )
            ->filter(fn ($card) =>
                count($card['rows']) > 0
            )
            ->values()
            ->all();
    }

    public function getSummary(
        string $bulan,
        string $tahun,
        ?int $barangId = null
    ): array {

        $cards = $this->getCards(
            $bulan,
            $tahun,
            $barangId
        );

        return [
            'total_pembelian' => collect($cards)
                ->sum('total_pembelian'),

            'total_hpp' => collect($cards)
                ->sum('total_hpp'),

            'nilai_persediaan_akhir' => collect($cards)
                ->sum('persediaan_akhir'),

            'metode' => 'Average',
        ];
    }

    private function buildCard(
        Barang $barang,
        Carbon $tglMulai,
        Carbon $tglAkhir
    ): array {

        $entries = KartuStokAverage::query()
            ->where('barang_id', $barang->id)
            ->whereBetween('tanggal', [
                $tglMulai->format('Y-m-d'),
                $tglAkhir->format('Y-m-d'),
            ])
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        $saldoAwal = KartuStokAverage::query()
            ->where('barang_id', $barang->id)
            ->where('tanggal', '<', $tglMulai->format('Y-m-d'))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->first();

        $rows = [];

        if ($saldoAwal) {

            $rows[] = [
                'tanggal' => $tglMulai->format('d/m/Y'),

                'keterangan' => 'Saldo Awal',

                'jenis' => 'awal',

                'pembelian' => null,

                'hpp' => null,

                'persediaan' => [
                    'qty' => (int) $saldoAwal->sisa_unit,

                    'harga' => (float) $saldoAwal->harga_rata_rata,

                    'total' => (float) $saldoAwal->nilai_persediaan,

                    'average_changed' => false,
                ],
            ];
        }

        foreach ($entries as $entry) {

            $rows[] = $this->formatRow($entry);
        }

        $last = $entries->last();

        $totalPembelian = $entries
            ->where('jenis', 'beli')
            ->sum(function ($row) {
                return
                    (float) $row->qty *
                    (float) $row->harga_beli;
            });

        $totalHpp = $entries
            ->where('jenis', 'jual')
            ->sum('hpp_total');

        return [
            'barang' => $barang,

            'rows' => $rows,

            'saldo_awal_unit' =>
                (int) ($saldoAwal?->sisa_unit ?? 0),

            'saldo_awal_nilai' =>
                (float) ($saldoAwal?->nilai_persediaan ?? 0),

            'total_pembelian' => $totalPembelian,

            'total_hpp' => $totalHpp,

            'stok_akhir' =>
                (int) ($last?->sisa_unit ?? 0),

            'harga_rata_rata_akhir' =>
                (float) ($last?->harga_rata_rata ?? 0),

            'persediaan_akhir' =>
                (float) ($last?->nilai_persediaan ?? 0),

            'valid' => true,
        ];
    }

    private function formatRow(
        KartuStokAverage $entry
    ): array {

        $pembelian = null;

        $hpp = null;

        if ($entry->jenis === 'beli') {

            $pembelian = [
                'qty' => (int) $entry->qty,

                'harga' => (float) $entry->harga_beli,

                'total' =>
                    (float) $entry->qty *
                    (float) $entry->harga_beli,
            ];
        }

        if ($entry->jenis === 'jual') {

            $hpp = [
                'qty' => (int) $entry->qty,

                'harga' => (float) $entry->hpp_per_unit,

                'total' => (float) $entry->hpp_total,
            ];
        }

        return [
            'tanggal' =>
                Carbon::parse($entry->tanggal)
                    ->format('d/m/Y'),

            'keterangan' => $entry->keterangan,

            'jenis' => $entry->jenis,

            'pembelian' => $pembelian,

            'hpp' => $hpp,

            'persediaan' => [
                'qty' => (int) $entry->sisa_unit,

                'harga' => (float) $entry->harga_rata_rata,

                'total' => (float) $entry->nilai_persediaan,

                'average_changed' =>
                    $entry->jenis === 'beli',
            ],
        ];
    }
}