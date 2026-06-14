<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\KartuStokAverage;
use App\Models\PenerimaanBarang;
use App\Models\PenerimaanBarangDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        $entry = KartuStokAverage::create([
            'barang_id' => $barangId,
            'tanggal' => $tanggal,
            'keterangan' => $keterangan ?: 'Pembelian',
            'jenis' => 'beli',

            'qty' => $qty,

            'harga_beli' => $hargaBeli,

            'hpp_per_unit' => 0,
            'hpp_total' => 0,

            'sisa_unit' => 0,

            'harga_rata_rata' => 0,

            'nilai_persediaan' => 0,
        ]);

        $this->recalculateBarang($barangId);

        return $entry->fresh();
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

        $entry = KartuStokAverage::create([
            'barang_id' => $barangId,
            'tanggal' => $tanggal,
            'keterangan' => $keterangan ?: 'Penjualan',
            'jenis' => 'jual',

            'qty' => $qty,

            'harga_beli' => 0,

            'hpp_per_unit' => 0,

            'hpp_total' => 0,

            'sisa_unit' => 0,

            'harga_rata_rata' => 0,

            'nilai_persediaan' => 0,
        ]);

        $this->recalculateBarang($barangId);

        return $entry->fresh();
    }

    public function syncPenerimaanBarang(
        PenerimaanBarang $penerimaan
    ): void {
        DB::transaction(function () use ($penerimaan): void {
            $penerimaan->loadMissing([
                'details.barang',
                'details.pembelianDetail',
            ]);

            $affectedBarangIds = [];

            foreach ($penerimaan->details as $detail) {
                /** @var PenerimaanBarangDetail $detail */
                $barangId = (int) ($detail->barang_id ?: $detail->barang?->id);

                if ($barangId <= 0) {
                    continue;
                }

                $existing = $this->findPenerimaanEntry($detail, $penerimaan);

                if ($existing) {
                    $affectedBarangIds[$existing->barang_id] = true;
                }

                $affectedBarangIds[$barangId] = true;

                $qtyMasuk = $this->getQtyMasukPenerimaan($detail);

                if ($qtyMasuk <= 0) {
                    $existing?->delete();
                    continue;
                }

                $hargaBeli = $this->getHargaBeliPenerimaan($detail);
                $payload = [
                    'barang_id' => $barangId,
                    'tanggal' => Carbon::parse($penerimaan->tanggal_terima)->toDateString(),
                    'keterangan' => 'Penerimaan Barang ' . $penerimaan->id_penerimaan,
                    'jenis' => 'beli',
                    'qty' => $qtyMasuk,
                    'harga_beli' => $hargaBeli,
                    'hpp_per_unit' => 0,
                    'hpp_total' => 0,
                ];

                if ($this->hasTransaksiIdColumn()) {
                    $payload['transaksi_id'] = $detail->id;
                }

                if ($existing) {
                    $existing->update($payload);
                    continue;
                }

                KartuStokAverage::create([
                    ...$payload,
                    'sisa_unit' => 0,
                    'harga_rata_rata' => 0,
                    'nilai_persediaan' => 0,
                ]);
            }

            foreach (array_keys($affectedBarangIds) as $barangId) {
                $this->recalculateBarang((int) $barangId);
            }
        });
    }

    public function recalculateBarang(int $barangId): void
    {
        $entries = KartuStokAverage::query()
            ->where('barang_id', $barangId)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        $saldoUnit = 0;
        $hargaRataRata = 0.0;
        $nilaiPersediaan = 0.0;

        foreach ($entries as $entry) {
            if ($entry->jenis === 'awal') {
                $saldoUnit = (int) $entry->sisa_unit;
                $nilaiPersediaan = (float) $entry->nilai_persediaan;
                $hargaRataRata = $saldoUnit > 0
                    ? round($nilaiPersediaan / $saldoUnit, 2)
                    : (float) $entry->harga_rata_rata;

                $entry->forceFill([
                    'hpp_per_unit' => 0,
                    'hpp_total' => 0,
                    'harga_rata_rata' => $hargaRataRata,
                    'nilai_persediaan' => $nilaiPersediaan,
                ])->saveQuietly();

                continue;
            }

            if ($entry->jenis === 'beli') {
                $qty = max(0, (int) $entry->qty);
                $hargaBeli = max(0, (float) $entry->harga_beli);
                $nilaiMasuk = $qty * $hargaBeli;
                $unitBaru = $saldoUnit + $qty;

                $hargaRataRata = $unitBaru > 0
                    ? round(($nilaiPersediaan + $nilaiMasuk) / $unitBaru, 2)
                    : 0;

                $saldoUnit = $unitBaru;
                $nilaiPersediaan = round($saldoUnit * $hargaRataRata, 2);

                $entry->forceFill([
                    'hpp_per_unit' => 0,
                    'hpp_total' => 0,
                    'sisa_unit' => $saldoUnit,
                    'harga_rata_rata' => $hargaRataRata,
                    'nilai_persediaan' => $nilaiPersediaan,
                ])->saveQuietly();

                continue;
            }

            if ($entry->jenis === 'jual') {
                $qty = max(0, (int) $entry->qty);
                $hppTotal = round($qty * $hargaRataRata, 2);

                $saldoUnit = max(0, $saldoUnit - $qty);
                $nilaiPersediaan = round($saldoUnit * $hargaRataRata, 2);

                $entry->forceFill([
                    'hpp_per_unit' => $hargaRataRata,
                    'hpp_total' => $hppTotal,
                    'sisa_unit' => $saldoUnit,
                    'harga_rata_rata' => $hargaRataRata,
                    'nilai_persediaan' => $nilaiPersediaan,
                ])->saveQuietly();
            }
        }
    }

    protected function findPenerimaanEntry(
        PenerimaanBarangDetail $detail,
        PenerimaanBarang $penerimaan
    ): ?KartuStokAverage {
        if ($this->hasTransaksiIdColumn()) {
            $entry = KartuStokAverage::query()
                ->where('transaksi_id', $detail->id)
                ->where('keterangan', 'like', 'Penerimaan Barang %')
                ->first();

            if ($entry) {
                return $entry;
            }
        }

        return KartuStokAverage::query()
            ->where('barang_id', $detail->barang_id)
            ->where('keterangan', 'Penerimaan Barang ' . $penerimaan->id_penerimaan)
            ->first();
    }

    protected function getQtyMasukPenerimaan(PenerimaanBarangDetail $detail): int
    {
        $qtyDiterima = max(0, (int) $detail->qty_diterima);
        $qtyRusak = max(0, (int) ($detail->getAttribute('qty_rusak') ?? 0));
        $kondisi = (string) ($detail->getAttribute('kondisi') ?? 'baik');

        if (in_array($kondisi, ['rusak', 'rusak_sebagian', 'rusak_semua'], true)) {
            $qtyRusak = $qtyRusak === 0 ? $qtyDiterima : min($qtyRusak, $qtyDiterima);
        } else {
            $qtyRusak = min($qtyRusak, $qtyDiterima);
        }

        return max(0, $qtyDiterima - $qtyRusak);
    }

    protected function getHargaBeliPenerimaan(PenerimaanBarangDetail $detail): float
    {
        $hargaUnit = (float) ($detail->pembelianDetail?->harga ?? 0);
        $diskonPersen = (float) ($detail->pembelianDetail?->diskon_persen ?? 0);

        return round($hargaUnit * (1 - ($diskonPersen / 100)), 2);
    }

    protected function hasTransaksiIdColumn(): bool
    {
        static $hasColumn = null;

        return $hasColumn ??= Schema::hasColumn('kartu_stok_average', 'transaksi_id');
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

        $rows[] = [
            'tanggal' => $tglMulai->format('j-M'),

            'keterangan' => 'Saldo Awal',

            'jenis' => 'awal',

            'pembelian' => null,

            'hpp' => null,

            'persediaan' => [
                'qty' => (int) ($saldoAwal?->sisa_unit ?? 0),

                'harga' => (float) ($saldoAwal?->harga_rata_rata ?? 0),

                'total' => (float) ($saldoAwal?->nilai_persediaan ?? 0),

                'average_changed' => false,
            ],
        ];

        foreach ($entries as $entry) {

            $rows[] = $this->formatRow($entry);
        }

        $last = $entries->last() ?: $saldoAwal;

        $totalPembelian = $entries
            ->where('jenis', 'beli')
            ->sum(function ($row) {
                return
                    (float) $row->qty *
                    (float) $row->harga_beli;
            });

        $totalPembelianUnit = $entries
            ->where('jenis', 'beli')
            ->sum('qty');

        $totalHpp = $entries
            ->where('jenis', 'jual')
            ->sum('hpp_total');

        $totalJualUnit = $entries
            ->where('jenis', 'jual')
            ->sum('qty');

        return [
            'barang' => $barang,

            'rows' => $rows,

            'saldo_awal_unit' =>
                (int) ($saldoAwal?->sisa_unit ?? 0),

            'saldo_awal_nilai' =>
                (float) ($saldoAwal?->nilai_persediaan ?? 0),

            'total_pembelian' => $totalPembelian,

            'total_pembelian_unit' => $totalPembelianUnit,

            'total_hpp' => $totalHpp,

            'total_jual_unit' => $totalJualUnit,

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
                    ->format('j-M'),

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
