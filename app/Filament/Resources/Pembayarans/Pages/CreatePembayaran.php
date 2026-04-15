<?php

namespace App\Filament\Resources\Pembayarans\Pages;

use App\Filament\Resources\Pembayarans\PembayaranResource;
use App\Models\DaftarAkun;
use App\Models\JurnalUmum;
use App\Models\JurnalUmumDetail;
use App\Models\Piutang;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreatePembayaran extends CreateRecord
{
    protected static string $resource = PembayaranResource::class;

    protected static ?string $title = 'Tambah Pembayaran';

    protected ?string $heading = 'Tambah Pembayaran';

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

    protected function getAkun(string $kode): ?DaftarAkun
    {
        return DaftarAkun::where('kode_akun', $kode)->first();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['keterangan'] = 'lunas';
        return $data;
    }

    protected function afterCreate(): void
    {
        $pembayaran = $this->record;

        DB::transaction(function () use ($pembayaran) {

            /*
             * =========================
             * 🔥 VALIDASI WAJIB
             * =========================
             */

            if (! $pembayaran->piutang_id) {
                throw new \Exception('Pembayaran hanya untuk transaksi kredit (piutang).');
            }

            /*
             * =========================
             * AMBIL DATA PIUTANG
             * =========================
             */

            $piutang = Piutang::query()
                ->lockForUpdate()
                ->find($pembayaran->piutang_id);

            if (! $piutang) {
                throw new \Exception('Data piutang tidak ditemukan.');
            }

            /*
             * =========================
             * UPDATE PIUTANG
             * =========================
             */

            $jumlahBayar = (float) ($pembayaran->jumlah_bayar ?? 0);
            $sisaLama    = (float) ($piutang->sisa_piutang ?? 0);

            $sisaBaru = max(0, $sisaLama - $jumlahBayar);

            $piutang->sisa_piutang = $sisaBaru;
            $piutang->status = ($sisaBaru <= 0) ? 'lunas' : 'belum_lunas';

            if ($sisaBaru <= 0) {
                $piutang->tanggal_lunas = $pembayaran->tanggal_bayar;
            }

            $piutang->save();

            /*
             * =========================
             * BUAT JURNAL
             * =========================
             */

            $nominalBayar      = (float) ($pembayaran->jumlah_bayar ?? 0);
            $diskonTermin      = (float) ($pembayaran->diskon_termin ?? 0);
            $nominalBayarTerms = (float) ($pembayaran->total_setelah_diskon ?? $nominalBayar);

            $totalTagihan = $diskonTermin > 0
                ? ($nominalBayarTerms + $diskonTermin)
                : $nominalBayar;

            $last = JurnalUmum::query()->latest('id')->first();
            $nextNumber = ($last?->id ?? 0) + 1;
            $kodeJurnal = 'JU-' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);

            $deskripsi = 'Pembayaran ' . $piutang->no_faktur;

            $jurnal = JurnalUmum::create([
                'tanggal'     => $pembayaran->tanggal_bayar,
                'kode_jurnal' => $kodeJurnal,
                'deskripsi'   => $deskripsi,
            ]);

            $pembayaran->update([
                'jurnal_umum_id' => $jurnal->id,
            ]);

            /*
             * =========================
             * AMBIL AKUN
             * =========================
             */

            $akunKas          = $this->getAkun('111'); // Kas
            $akunPiutang      = $this->getAkun('116'); // Piutang
            $akunPotonganJual = $this->getAkun('412'); // Diskon

            if (! $akunPiutang) {
                throw new \Exception('Akun piutang tidak ditemukan.');
            }

            /*
             * =========================
             * 🔥 PILIH AKUN KAS / BANK
             * =========================
             */

            if ($pembayaran->metode_bayar === 'transfer') {
                $akunDebit = $pembayaran->akun_bank_id
                    ? DaftarAkun::find($pembayaran->akun_bank_id)
                    : $akunKas;
            } else {
                $akunDebit = $akunKas;
            }

            if (! $akunDebit) {
                throw new \Exception('Akun kas/bank tidak valid.');
            }

            /*
             * =========================
             * JURNAL DENGAN DISKON
             * =========================
             */

            if ($diskonTermin > 0 && $akunPotonganJual) {

                JurnalUmumDetail::create([
                    'jurnal_umum_id' => $jurnal->id,
                    'daftar_akun_id' => $akunDebit->id,
                    'posisi'         => 'debit',
                    'nominal'        => $nominalBayarTerms,
                ]);

                JurnalUmumDetail::create([
                    'jurnal_umum_id' => $jurnal->id,
                    'daftar_akun_id' => $akunPotonganJual->id,
                    'posisi'         => 'debit',
                    'nominal'        => $diskonTermin,
                ]);

                JurnalUmumDetail::create([
                    'jurnal_umum_id' => $jurnal->id,
                    'daftar_akun_id' => $akunPiutang->id,
                    'posisi'         => 'kredit',
                    'nominal'        => $totalTagihan,
                ]);

                return;
            }

            /*
             * =========================
             * JURNAL NORMAL
             * =========================
             */

            JurnalUmumDetail::create([
                'jurnal_umum_id' => $jurnal->id,
                'daftar_akun_id' => $akunDebit->id,
                'posisi'         => 'debit',
                'nominal'        => $nominalBayar,
            ]);

            JurnalUmumDetail::create([
                'jurnal_umum_id' => $jurnal->id,
                'daftar_akun_id' => $akunPiutang->id,
                'posisi'         => 'kredit',
                'nominal'        => $nominalBayar,
            ]);
        });
    }
}