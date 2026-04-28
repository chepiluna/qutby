<?php

namespace App\Filament\Resources\Penjualans\Pages;

use App\Filament\Resources\Penjualans\PenjualanResource;
use App\Models\DaftarAkun;
use App\Models\JurnalUmum;
use App\Models\JurnalUmumDetail;
use App\Models\Penjualan;
use App\Models\Piutang;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Pembayaran;

class CreatePenjualan extends CreateRecord
{
    protected static string $resource = PenjualanResource::class;
    protected static ?string $title = 'Tambah Penjualan';
    protected ?string $heading = 'Tambah Penjualan';

    public function getBreadcrumb(): string
    {
        return 'Tambah';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Tambah');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()->label('Tambah & tambah lainnya');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Batal');
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
        $penjualan = $this->record;

        // 🔥 ambil tipe pembayaran (WAJIB ADA DI DB)
        $tipe = $penjualan->cara_bayar ?? 'kredit'; // default aman

        // Kurangi stok
        foreach ($penjualan->detail as $detail) {
            if ($detail->barang) {
                $detail->barang->kurangiStok($detail->qty);
            }
        }

        $totalBruto = $penjualan->detail()->sum('subtotal');

        $diskonPersen = (float) ($this->data['diskon_persen'] ?? 0);
        $pajakPersen  = (float) ($this->data['pajak_persen'] ?? 0);

        $diskonRp = $totalBruto * $diskonPersen / 100;
        $dpp      = $totalBruto - $diskonRp;
        $pajakRp  = $dpp * $pajakPersen / 100;
        $totalNetto = $dpp + $pajakRp;

        // HPP
        $totalHpp = 0;
        foreach ($penjualan->detail as $detail) {
            $hpp = $detail->barang->hpp_satuan ?? 0;
            $totalHpp += $detail->qty * $hpp;
        }

        $penjualan->update([
            'total_bruto'  => $totalBruto,
            'diskon_rp'    => $diskonRp,
            'pajak_persen' => $pajakPersen,
            'total_netto'  => $totalNetto,
            'total_hpp'    => $totalHpp,
        ]);

        // Buat jurnal
        $last = JurnalUmum::where('kode_jurnal', 'like', 'JU-%')
            ->orderByDesc('id')
            ->value('kode_jurnal');

        $lastNumber = $last ? (int) preg_replace('/\D+/', '', $last) : 0;
        $kodeJurnal = 'JU-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        $jurnal = JurnalUmum::create([
            'tanggal'     => $penjualan->tanggal_faktur,
            'kode_jurnal' => $kodeJurnal,
            'deskripsi'   => 'Penjualan ' . $penjualan->no_faktur,
        ]);

        $penjualan->update(['jurnal_umum_id' => $jurnal->id]);

        // Ambil akun
        $akunKas          = $this->getAkun('111'); // Kas
        $akunPiutang      = $this->getAkun('116');
        $akunPenjualan    = $this->getAkun('411');
        $akunPpnKeluar    = $this->getAkun('212');
        $akunHpp          = $this->getAkun('511');
        $akunPersediaan   = $this->getAkun('115');
        $akunPotonganJual = $this->getAkun('412');

        // 🔥 BAGIAN PENTING: PEMBEDA TUNAI vs KREDIT
        if ($tipe === 'tunai') {

            // 🔥 tentukan akun kas / bank
            if ($penjualan->metode_bayar === 'transfer') {
                $akunKas = $penjualan->akun_kas_id
                    ? DaftarAkun::find($penjualan->akun_kas_id)
                    : $this->getAkun('111'); // fallback
            } else {
                $akunKas = $this->getAkun('111'); // cash
            }

            // Debit Kas / Bank
            JurnalUmumDetail::create([
                'jurnal_umum_id' => $jurnal->id,
                'daftar_akun_id' => $akunKas->id,
                'posisi' => 'debit',
                'nominal' => $totalNetto,
            ]);

        } else {
            // Debit Piutang
            JurnalUmumDetail::create([
                'jurnal_umum_id' => $jurnal->id,
                'daftar_akun_id' => $akunPiutang->id,
                'posisi' => 'debit',
                'nominal' => $totalNetto,
            ]);
        }

        // Diskon
        if ($diskonRp > 0) {
            JurnalUmumDetail::create([
                'jurnal_umum_id' => $jurnal->id,
                'daftar_akun_id' => $akunPotonganJual->id,
                'posisi' => 'debit',
                'nominal' => $diskonRp,
            ]);
        }

        // Penjualan
        JurnalUmumDetail::create([
            'jurnal_umum_id' => $jurnal->id,
            'daftar_akun_id' => $akunPenjualan->id,
            'posisi' => 'kredit',
            'nominal' => $totalBruto,
        ]);

        // PPN
        JurnalUmumDetail::create([
            'jurnal_umum_id' => $jurnal->id,
            'daftar_akun_id' => $akunPpnKeluar->id,
            'posisi' => 'kredit',
            'nominal' => $pajakRp,
        ]);

        // HPP
        if ($totalHpp > 0) {
            JurnalUmumDetail::create([
                'jurnal_umum_id' => $jurnal->id,
                'daftar_akun_id' => $akunHpp->id,
                'posisi' => 'debit',
                'nominal' => $totalHpp,
            ]);

            JurnalUmumDetail::create([
                'jurnal_umum_id' => $jurnal->id,
                'daftar_akun_id' => $akunPersediaan->id,
                'posisi' => 'kredit',
                'nominal' => $totalHpp,
            ]);
        }

        // 🔥 PIUTANG HANYA UNTUK KREDIT
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

        $status = $tipe === 'tunai' ? 'lunas' : 'belum_lunas';

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
    }
}