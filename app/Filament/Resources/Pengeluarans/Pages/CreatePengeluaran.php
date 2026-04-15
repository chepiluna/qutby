<?php

namespace App\Filament\Resources\Pengeluarans\Pages;

use App\Filament\Resources\Pengeluarans\PengeluaranResource;
use App\Models\DaftarAkun;
use App\Models\JurnalUmum;
use App\Models\JurnalUmumDetail;
use App\Models\KategoriPengeluaran;
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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = 'dibayar';
        $data['paid_at'] = now();

        return $data;
    }

    protected function afterCreate(): void
    {
        DB::transaction(function () {
            $record = $this->record;

            $last = JurnalUmum::latest('id')->first();
            $nextNumber = ($last?->id ?? 0) + 1;
            $kodeJurnal = 'JU-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            $jurnal = JurnalUmum::create([
                'tanggal'     => $record->tanggal_pengeluaran,
                'kode_jurnal' => $kodeJurnal,
                'deskripsi'   => 'Pengeluaran: ' . $record->deskripsi,
            ]);

            $kategori = KategoriPengeluaran::with('akun')->find($record->kategori_pengeluaran_id);
            $akunBeban = $kategori?->akun;

            $akunKas = DaftarAkun::where('kode_akun', '111')->first();

            if (! $akunKas) {
                throw new \RuntimeException('Akun kas kode_akun=111 tidak ditemukan.');
            }

            if (! $akunBeban) {
                throw new \RuntimeException('Kategori pengeluaran belum punya akun (daftar_akun_id masih kosong).');
            }

            JurnalUmumDetail::create([
                'jurnal_umum_id' => $jurnal->id,
                'daftar_akun_id' => $akunBeban->id,
                'posisi'         => 'debit',
                'nominal'        => $record->jumlah,
            ]);

            JurnalUmumDetail::create([
                'jurnal_umum_id' => $jurnal->id,
                'daftar_akun_id' => $akunKas->id,
                'posisi'         => 'kredit',
                'nominal'        => $record->jumlah,
            ]);

            $record->update(['jurnal_umum_id' => $jurnal->id]);
        });
    }
}
