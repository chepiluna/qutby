<?php

namespace App\Filament\Resources\PenerimaanBarangResource\Pages;

use App\Filament\Resources\PenerimaanBarangResource;
use App\Models\DaftarAkun;
use App\Models\JurnalUmum;
use App\Models\JurnalUmumDetail;
use App\Models\PenerimaanBarang;
use App\Models\Pembelian;
use App\Services\PenerimaanBarangKonfirmasiService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreatePenerimaanBarang extends CreateRecord
{
    protected static string $resource = PenerimaanBarangResource::class;

    public function getTitle(): string
    {
        return 'Penerimaan Barang';
    }

    public function getBreadcrumb(): string
    {
        return 'Buat Penerimaan Barang';
    }

    public function getHeading(): HtmlString
    {
        $url = e($this->getResource()::getUrl('index'));

        return new HtmlString(<<<HTML
            <span style="display:inline-flex; align-items:center; gap:12px;">
                <a href="{$url}" aria-label="Kembali ke daftar penerimaan barang" style="display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:9999px; background:#000000; color:#ffffff; text-decoration:none; line-height:1;">
                    <span style="font-size:30px; font-weight:800; transform:translate(-1px, -1px);">&lsaquo;</span>
                </a>
                <span>Penerimaan Barang</span>
            </span>
        HTML);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $po = Pembelian::with(['details.penerimaanBarangDetails.penerimaanBarang'])
            ->find($data['pembelian_id'] ?? null);

        if (! $po || ! in_array($po->status, ['menunggu', 'partial'], true)) {
            throw ValidationException::withMessages([
                'pembelian_id' => 'Pesanan Pembelian harus berstatus Aktif atau Sebagian Diterima.',
            ]);
        }

        if (Carbon::parse($data['tanggal_terima'])->lt($po->tanggal)) {
            throw ValidationException::withMessages([
                'tanggal_terima' => 'Tanggal terima tidak boleh sebelum tanggal Pesanan Pembelian.',
            ]);
        }

        $data['id_penerimaan'] = PenerimaanBarang::generateNomor((int) $po->id);

        if (empty($data['details']) && ! empty($data['pembelian_id'])) {
            $data['details'] = PenerimaanBarangResource::getOpenPenerimaanBarangItems($po);
        }

        if (empty($data['details'])) {
            throw ValidationException::withMessages([
                'pembelian_id' => 'Semua item PO ini sudah terpenuhi. Pilih PO lain yang masih memiliki outstanding.',
            ]);
        }

        foreach ($data['details'] as $index => $detail) {

            $poDetail = $po->details->firstWhere(
                'id',
                $detail['pembelian_detail_id'] ?? null
            );

            $qtyDiterima = (int) ($detail['qty_diterima'] ?? 0);

            $qtyOutstanding = (int) ($poDetail?->qty_outstanding ?? 0);

            if (
                ! $poDetail ||
                (int) ($poDetail->barang_id) !== (int) ($detail['barang_id'] ?? 0)
            ) {
                throw ValidationException::withMessages([
                    "details.{$index}.barang_id" =>
                        'Item penerimaan harus berasal dari detail Pesanan Pembelian.',
                ]);
            }

            if ($qtyDiterima > $qtyOutstanding) {
                throw ValidationException::withMessages([
                    "details.{$index}.qty_diterima" =>
                        'Qty diterima tidak boleh melebihi qty outstanding PO.',
                ]);
            }

            $data['details'][$index]['kondisi'] = 'baik';
            $data['details'][$index]['qty_rusak'] = 0;

            unset(
                $data['details'][$index]['qty_sisa'],
                $data['details'][$index]['qty_sudah_diterima'],
                $data['details'][$index]['catatan_item']
            );
        }

        $data['status_penerimaan'] =
            $this->determineStatusPenerimaan($data['details'], $po);

        return $data;
    }

    protected function determineStatusPenerimaan(
        array $details,
        Pembelian $po
    ): string {

        $adaKurang = collect($details)->contains(
            function (array $detail) use ($po): bool {

                $poDetail = $po->details->firstWhere(
                    'id',
                    $detail['pembelian_detail_id'] ?? null
                );

                $qtyOutstanding = (int) ($poDetail?->qty_outstanding ?? 0);

                return (int) ($detail['qty_diterima'] ?? 0) < $qtyOutstanding;
            }
        );

        return $adaKurang ? 'sebagian' : 'lengkap';
    }

    protected function getAkun(string $kode): ?DaftarAkun
    {
        return DaftarAkun::where('kode_akun', $kode)->first();
    }

    protected function afterCreate(): void
    {
        DB::transaction(function () {

            $record = $this->getRecord();

            // =========================
            // KONFIRMASI + UPDATE STOK
            // =========================
            app(PenerimaanBarangKonfirmasiService::class)
                ->konfirmasi($record, auth()->id() ?? 1);

            $record->load([
                'details.pembelianDetail',
                'pembelian',
            ]);

            $pembelian = $record->pembelian;

            // =========================
            // HITUNG TOTAL
            // =========================
            $totalDpp = 0;

            foreach ($record->details as $detail) {

                $qtyBaik =
                    (int) $detail->qty_diterima -
                    (int) ($detail->qty_rusak ?? 0);

                if ($qtyBaik <= 0) {
                    continue;
                }

                $harga = (float) ($detail->pembelianDetail?->harga ?? 0);

                $diskonPersen = (float) (
                    $detail->pembelianDetail?->diskon_persen ?? 0
                );

                $hargaSetelahDiskon =
                    $harga - ($harga * $diskonPersen / 100);

                $totalDpp += $qtyBaik * $hargaSetelahDiskon;
            }

            if ($totalDpp <= 0) {
                return;
            }

            $ppnNominal = $pembelian->ppn
                ? $totalDpp * 0.11
                : 0;

            $totalAkhir = $totalDpp + $ppnNominal;

            // =========================
            // GENERATE KODE JURNAL
            // =========================
            $last = JurnalUmum::where('kode_jurnal', 'like', 'JU-%')
                ->orderByDesc('id')
                ->value('kode_jurnal');

            $lastNumber = $last
                ? (int) preg_replace('/\D+/', '', $last)
                : 0;

            $kodeJurnal =
                'JU-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

            // =========================
            // BUAT JURNAL UMUM
            // =========================
            $jurnal = JurnalUmum::create([
                'tanggal'     => $record->tanggal_terima,
                'kode_jurnal' => $kodeJurnal,
                'deskripsi'   =>
                    'Penerimaan Barang ' . $record->id_penerimaan,
            ]);

            $record->update([
                'jurnal_umum_id' => $jurnal->id,
            ]);

            // =========================
            // AKUN
            // =========================
            $akunPersediaan = $this->getAkun('115');
            $akunPpnMasukan = $this->getAkun('117');
            $akunUtang      = $this->getAkun('211');
            $akunKas        = $this->getAkun('111');

            $isTunai =
                ($pembelian->syarat_pembayaran ?? 'kredit') === 'tunai';

            // =========================
            // DEBIT PERSEDIAAN
            // =========================
            JurnalUmumDetail::create([
                'jurnal_umum_id' => $jurnal->id,
                'daftar_akun_id' => $akunPersediaan?->id,
                'posisi'         => 'debit',
                'nominal'        => $totalDpp,
            ]);

            // =========================
            // DEBIT PPN MASUKAN
            // =========================
            if ($ppnNominal > 0) {

                JurnalUmumDetail::create([
                    'jurnal_umum_id' => $jurnal->id,
                    'daftar_akun_id' => $akunPpnMasukan?->id,
                    'posisi'         => 'debit',
                    'nominal'        => $ppnNominal,
                ]);
            }

            // =========================
            // KREDIT KAS / UTANG
            // =========================
            JurnalUmumDetail::create([
                'jurnal_umum_id' => $jurnal->id,
                'daftar_akun_id' => $isTunai
                    ? $akunKas?->id
                    : $akunUtang?->id,

                'posisi'         => 'kredit',
                'nominal'        => $totalAkhir,
            ]);
        });

        Notification::make()
            ->title('Penerimaan barang berhasil disimpan')
            ->body('Stok dan jurnal berhasil diperbarui.')
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return PenerimaanBarangResource::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan')
                ->submit('create'),

            Action::make('cancel')
                ->label('Batal')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
}
