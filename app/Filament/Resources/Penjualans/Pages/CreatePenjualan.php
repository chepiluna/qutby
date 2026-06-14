<?php

namespace App\Filament\Resources\Penjualans\Pages;

use App\Filament\Resources\Penjualans\PenjualanResource;
use App\Models\DaftarAkun;
use App\Models\JurnalUmum;
use App\Models\JurnalUmumDetail;
use App\Models\KartuStok;
use App\Models\Pembayaran;
use App\Models\Penjualan;
use App\Models\Piutang;
use App\Services\KartuStokAverageService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\DB;

class CreatePenjualan extends CreateRecord
{
    protected static string $resource = PenjualanResource::class;

    protected static ?string $title = 'Tambah Penjualan';

    public function getBreadcrumb(): string
    {
        return 'Tambah';
    }

    public function getHeading(): HtmlString
    {
        $url = e($this->getResource()::getUrl('index'));

        return new HtmlString(<<<HTML
            <span style="display:inline-flex; align-items:center; gap:12px;">
                <a href="{$url}" aria-label="Kembali ke daftar penjualan" style="display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:9999px; background:#000000; color:#ffffff; text-decoration:none; line-height:1;">
                    <span style="font-size:30px; font-weight:800; transform:translate(-1px, -1px);">&lsaquo;</span>
                </a>
                <span>Tambah Penjualan</span>
            </span>
        HTML);
    }

    protected function getHeaderActions(): array
    {
        return [];
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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Data penjualan telah disimpan');
    }

    protected function afterFill(): void
    {
        $this->data['no_faktur'] = $this->getNextNoFaktur();
    }

    protected function getAkun(string $kode): ?DaftarAkun
    {
        return DaftarAkun::where('kode_akun', $kode)->first();
    }

    private function getNextNoFaktur(): string
    {
        $last = Penjualan::query()
            ->where('no_faktur', 'like', 'FKT-%')
            ->orderByDesc('id')
            ->value('no_faktur');

        $lastNumber = $last ? (int) substr($last, 4) : 0;

        return 'FKT-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['no_faktur'] ??= $this->getNextNoFaktur();

        $totalBruto = 0;

        foreach ($data['detail'] ?? [] as $row) {
            $totalBruto += (float) ($row['subtotal'] ?? 0);
        }

        $diskonPersen = (float) ($data['diskon_persen'] ?? 0);
        $pajakPersen  = (float) ($data['pajak_persen'] ?? 0);

        $diskonRp   = $totalBruto * $diskonPersen / 100;
        $dpp        = $totalBruto - $diskonRp;
        $pajakRp    = $dpp * $pajakPersen / 100;
        $totalNetto = $dpp + $pajakRp;

        $data['total_bruto'] = $totalBruto;
        $data['diskon_rp']   = $diskonRp;
        $data['total_netto'] = $totalNetto;

        return $data;
    }

    protected function afterCreate(): void
    {
        DB::transaction(function () {

            $penjualan = $this->record;

            $averageService = app(KartuStokAverageService::class);

            $tipe = $penjualan->cara_bayar ?? 'kredit';

            $totalHpp = 0;

            // =========================
            // KARTU STOK + HPP AVERAGE
            // =========================
            foreach ($penjualan->detail()->get() as $detail) {

                if (! $detail->barang_id || ! $detail->qty) {
                    continue;
                }

                $preview = $averageService->previewPenjualan(
                    $detail->barang_id,
                    (int) $detail->qty
                );

                $hppItem = (float) $preview['total_hpp'];

                $totalHpp += $hppItem;

                $averageService->tambahPenjualan(
                    barangId: (int) $detail->barang_id,
                    tanggal: $penjualan->tanggal_faktur,
                    qty: (int) $detail->qty,
                    keterangan: 'Penjualan ' . $penjualan->no_faktur
                );

                KartuStok::create([
                    'barang_id'   => $detail->barang_id,
                    'tanggal'     => $penjualan->tanggal_faktur,
                    'masuk'       => 0,
                    'keluar'      => $detail->qty,
                    'stok_akhir'  => $preview['stok_setelah'],
                    'keterangan'  => 'Penjualan ' . $penjualan->no_faktur,
                ]);
            }

            // =========================
            // TOTAL PENJUALAN
            // =========================
            $totalBruto = $penjualan->detail()->sum('subtotal');

            $diskonPersen = (float) ($this->data['diskon_persen'] ?? 0);
            $pajakPersen  = (float) ($this->data['pajak_persen'] ?? 0);

            $diskonRp = $totalBruto * $diskonPersen / 100;
            $dpp      = $totalBruto - $diskonRp;
            $pajakRp  = $dpp * $pajakPersen / 100;

            $totalNetto = $dpp + $pajakRp;

            $penjualan->update([
                'total_bruto'  => $totalBruto,
                'diskon_rp'    => $diskonRp,
                'pajak_persen' => $pajakPersen,
                'total_netto'  => $totalNetto,
                'total_hpp'    => $totalHpp,
            ]);

            // =========================
            // JURNAL UMUM
            // =========================
            $last = JurnalUmum::where('kode_jurnal', 'like', 'JU-%')
                ->orderByDesc('id')
                ->value('kode_jurnal');

            $lastNumber = $last
                ? (int) preg_replace('/\D+/', '', $last)
                : 0;

            $kodeJurnal = 'JU-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

            $jurnal = JurnalUmum::create([
                'tanggal'     => $penjualan->tanggal_faktur,
                'kode_jurnal' => $kodeJurnal,
                'deskripsi'   => 'Penjualan ' . $penjualan->no_faktur,
            ]);

            $penjualan->update([
                'jurnal_umum_id' => $jurnal->id,
            ]);

            // =========================
            // AKUN
            // =========================
            $akunKas          = $this->getAkun('111');
            $akunPiutang      = $this->getAkun('116');
            $akunPenjualan    = $this->getAkun('411');
            $akunPpnKeluar    = $this->getAkun('212');
            $akunHpp          = $this->getAkun('511');
            $akunPersediaan   = $this->getAkun('115');
            $akunPotonganJual = $this->getAkun('412');

            // =========================
            // TUNAI / KREDIT
            // =========================
            if ($tipe === 'tunai') {

                if ($penjualan->metode_bayar === 'transfer') {

                    $akunKas = $penjualan->akun_kas_id
                        ? DaftarAkun::find($penjualan->akun_kas_id)
                        : $this->getAkun('111');

                } else {

                    $akunKas = $this->getAkun('111');
                }

                JurnalUmumDetail::create([
                    'jurnal_umum_id' => $jurnal->id,
                    'daftar_akun_id' => $akunKas->id,
                    'posisi'         => 'debit',
                    'nominal'        => $totalNetto,
                ]);

            } else {

                JurnalUmumDetail::create([
                    'jurnal_umum_id' => $jurnal->id,
                    'daftar_akun_id' => $akunPiutang->id,
                    'posisi'         => 'debit',
                    'nominal'        => $totalNetto,
                ]);
            }

            // =========================
            // DISKON
            // =========================
            if ($diskonRp > 0) {

                JurnalUmumDetail::create([
                    'jurnal_umum_id' => $jurnal->id,
                    'daftar_akun_id' => $akunPotonganJual->id,
                    'posisi'         => 'debit',
                    'nominal'        => $diskonRp,
                ]);
            }

            // =========================
            // PENJUALAN
            // =========================
            JurnalUmumDetail::create([
                'jurnal_umum_id' => $jurnal->id,
                'daftar_akun_id' => $akunPenjualan->id,
                'posisi'         => 'kredit',
                'nominal'        => $totalBruto,
            ]);

            // =========================
            // PPN
            // =========================
            if ($pajakRp > 0) {

                JurnalUmumDetail::create([
                    'jurnal_umum_id' => $jurnal->id,
                    'daftar_akun_id' => $akunPpnKeluar->id,
                    'posisi'         => 'kredit',
                    'nominal'        => $pajakRp,
                ]);
            }

            // =========================
            // HPP
            // =========================
            if ($totalHpp > 0) {

                JurnalUmumDetail::create([
                    'jurnal_umum_id' => $jurnal->id,
                    'daftar_akun_id' => $akunHpp->id,
                    'posisi'         => 'debit',
                    'nominal'        => $totalHpp,
                ]);

                JurnalUmumDetail::create([
                    'jurnal_umum_id' => $jurnal->id,
                    'daftar_akun_id' => $akunPersediaan->id,
                    'posisi'         => 'kredit',
                    'nominal'        => $totalHpp,
                ]);
            }

            // =========================
            // PIUTANG
            // =========================
            if ($tipe === 'kredit') {

                Piutang::updateOrCreate(
                    [
                        'penjualan_id' => $penjualan->id,
                    ],
                    [
                        'pelanggan_id'     => $penjualan->pelanggan_id,
                        'no_faktur'        => $penjualan->no_faktur,
                        'tanggal_faktur'   => $penjualan->tanggal_faktur,
                        'termin_id'        => $penjualan->termin_id,
                        'total_piutang'    => $totalNetto,
                        'sisa_piutang'     => $totalNetto,
                        'status'           => 'belum_lunas',
                        'diskon_persen'    => optional($penjualan->termin)->diskon_persen ?? 0,
                        'hari_diskon'      => optional($penjualan->termin)->hari_diskon ?? 0,
                        'hari_jatuh_tempo' => optional($penjualan->termin)->hari_jatuh_tempo ?? 0,

                        'tgl_jatuh_tempo'  => \Carbon\Carbon::parse($penjualan->tanggal_faktur)
                            ->addDays(optional($penjualan->termin)->hari_jatuh_tempo ?? 0),
                    ]
                );
            }

            // =========================
            // PEMBAYARAN
            // =========================
            $status = $tipe === 'tunai'
                ? 'lunas'
                : 'belum_lunas';

            $piutangId = null;

            if ($tipe === 'kredit') {

                $piutang = Piutang::where('penjualan_id', $penjualan->id)->first();

                $piutangId = $piutang?->id;
            }

            Pembayaran::create([
                'penjualan_id'   => $penjualan->id,
                'piutang_id'     => $piutangId,
                'customer_id'    => $penjualan->pelanggan_id,
                'tanggal_bayar'  => $tipe === 'tunai'
                    ? $penjualan->tanggal_faktur
                    : null,

                'jumlah_bayar'   => $totalNetto,
                'diskon_termin'  => 0,

                'metode_bayar'   => $tipe === 'tunai'
                    ? ($penjualan->metode_bayar ?? 'cash')
                    : null,

                'jenis'          => $tipe,
                'keterangan'     => $status,
            ]);
        });
    }
}
