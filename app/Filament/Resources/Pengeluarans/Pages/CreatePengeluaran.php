<?php

namespace App\Filament\Resources\Pengeluarans\Pages;

use App\Filament\Resources\Pengeluarans\PengeluaranResource;
use App\Models\JurnalUmum;
use App\Models\JurnalUmumDetail;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreatePengeluaran extends CreateRecord
{
    protected static string $resource = PengeluaranResource::class;

    protected static ?string $title = 'Tambah Pengeluaran';

    protected ?string $heading = 'Tambah Pengeluaran';

    public function getBreadcrumb(): string
    {
        return 'Tambah';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Tambah');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Tambah & tambah lainnya');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }

    // ❌ HAPUS status & paid_at (ga dipakai lagi)
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    protected function afterCreate(): void
    {
        DB::transaction(function () {
            $record = $this->record;

            // 🔢 generate kode jurnal
            $last = JurnalUmum::latest('id')->first();
            $nextNumber = ($last?->id ?? 0) + 1;
            $kodeJurnal = 'JU-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            // 🧾 create jurnal
            $jurnal = JurnalUmum::create([
                'tanggal'     => $record->tanggal_pengeluaran,
                'kode_jurnal' => $kodeJurnal,
                'deskripsi'   => 'Pengeluaran: ' . $record->deskripsi,
            ]);

            // ✅ ambil langsung dari input user
            $akunBebanId = $record->daftar_akun_id;
            $akunKasId   = $record->kas_bank_id;

            if (! $akunBebanId || ! $akunKasId) {
                throw new \RuntimeException('Akun beban atau kas/bank belum dipilih.');
            }

            // 💰 Debit Beban
            JurnalUmumDetail::create([
                'jurnal_umum_id' => $jurnal->id,
                'daftar_akun_id' => $akunBebanId,
                'posisi'         => 'debit',
                'nominal'        => $record->jumlah,
            ]);

            // 💸 Kredit Kas/Bank
            JurnalUmumDetail::create([
                'jurnal_umum_id' => $jurnal->id,
                'daftar_akun_id' => $akunKasId,
                'posisi'         => 'kredit',
                'nominal'        => $record->jumlah,
            ]);

            // 🔗 simpan relasi
            $record->update([
                'jurnal_umum_id' => $jurnal->id
            ]);
        });
    }
}