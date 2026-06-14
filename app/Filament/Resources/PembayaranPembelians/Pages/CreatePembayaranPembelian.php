<?php

namespace App\Filament\Resources\PembayaranPembelians\Pages;

use App\Filament\Resources\PembayaranPembelians\PembayaranPembelianResource;
use App\Models\DaftarAkun;
use App\Models\JurnalUmum;
use App\Models\JurnalUmumDetail;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreatePembayaranPembelian extends CreateRecord
{
    protected static string $resource = PembayaranPembelianResource::class;

    public function getTitle(): string
    {
        return 'Tambah Pembayaran Utang';
    }

    public function getBreadcrumb(): string
    {
        return 'Tambah Pembayaran Utang';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getCreateFormAction(): Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Simpan');
    }

    public function canCreateAnother(): bool
    {
        return false;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['tanggal_faktur']) && ! empty($data['grn_id'])) {
            $data['tanggal_faktur'] = PembayaranPembelianResource::resolveTanggalPembayaran((int) $data['grn_id'])
                ?? null;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        try {

            DB::transaction(function () {

                $pembayaran = $this->getRecord();

                $nominal = (float) ($pembayaran->total_netto ?? 0);

                if ($nominal <= 0) {
                    return;
                }

                /**
                 * Generate kode jurnal
                 */
                $last = JurnalUmum::where(
                    'kode_jurnal',
                    'like',
                    'JU-%'
                )
                    ->orderByDesc('id')
                    ->value('kode_jurnal');

                $lastNumber = $last
                    ? (int) preg_replace('/\D+/', '', $last)
                    : 0;

                $kodeJurnal = 'JU-' . str_pad(
                    $lastNumber + 1,
                    3,
                    '0',
                    STR_PAD_LEFT
                );

                /**
                 * Buat jurnal umum
                 */
                $jurnal = JurnalUmum::create([

                    'tanggal' => $pembayaran->tanggal_faktur ?? now(),

                    'kode_jurnal' => $kodeJurnal,

                    'deskripsi' =>
                        'Pembayaran Utang '
                        . $pembayaran->nomor_faktur_vendor,

                ]);

                /**
                 * Ambil akun
                 */
                $akunUtang = DaftarAkun::where(
                    'kode_akun',
                    '211'
                )->first();

                $akunKas = DaftarAkun::find(
                    $pembayaran->akun_kas_id
                );

                if (! $akunUtang || ! $akunKas) {
                    return;
                }

                /**
                 * Debit utang
                 */
                JurnalUmumDetail::create([

                    'jurnal_umum_id' => $jurnal->id,

                    'daftar_akun_id' => $akunUtang->id,

                    'posisi' => 'debit',

                    'nominal' => $nominal,

                ]);

                /**
                 * Kredit kas/bank
                 */
                JurnalUmumDetail::create([

                    'jurnal_umum_id' => $jurnal->id,

                    'daftar_akun_id' => $akunKas->id,

                    'posisi' => 'kredit',

                    'nominal' => $nominal,

                ]);
            });

            Notification::make()
                ->title('Pembayaran utang berhasil disimpan')
                ->success()
                ->send();

        } catch (\Throwable $e) {

            Notification::make()
                ->title('Terjadi kesalahan')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [

            $this->getCreateFormAction()
                ->label('Simpan'),

            $this->getCancelFormAction()
                ->label('Batal'),

        ];
    }
}
