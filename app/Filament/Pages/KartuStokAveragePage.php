<?php

namespace App\Filament\Pages;

//use App\Filament\Traits\HasRoleAccess;
use App\Models\Barang;
use App\Services\KartuStokAverageService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\Url;
use Filament\Facades\Filament;

class KartuStokAveragePage extends Page
{
    //use HasRoleAccess;

    protected static array $allowedRoles = ['admin', 'operasional'];

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calculator';
    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Kartu Stok Average';
    protected static ?string $title = 'Kartu Stok Average';
    protected static ?string $slug = 'kartu-stok-average';
    protected static ?int $navigationSort = 3;
    protected string $view = 'filament.pages.kartu-stok-average';

    public string $bulan = '';
    public string $tahun = '';

    #[Url(as: 'barang')]
    public string $barangId = '';

    public bool $showForm = false;
    public string $formBarangId = '';
    public string $formTanggal = '';
    public string $formJenis = 'beli';
    public string $formQty = '1';
    public string $formHargaBeli = '0';
    public string $formKeterangan = '';

    public function mount(): void
    {
        $this->bulan = now()->format('m');
        $this->tahun = now()->format('Y');
        $this->formTanggal = now()->format('Y-m-d');
    }

    public function getTitle(): string
    {
        return 'Kartu Stok Average';
    }

    public function getHeading(): string
    {
        return 'Laporan Kartu Stok Average';
    }

    public function getBreadcrumb(): string
    {
        return 'Kartu Stok Average';
    }

    public function getPeriodeLabel(): string
    {
        $nama = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];

        return ($nama[$this->bulan] ?? '-') . ' ' . $this->tahun;
    }

    public function getBarangOptions(): array
    {
        return Barang::query()
            ->orderBy('nama_barang')
            ->pluck('nama_barang', 'id')
            ->all();
    }

    public function getCards(): array
    {
        return app(KartuStokAverageService::class)
            ->getCards($this->bulan, $this->tahun, $this->barangId !== '' ? (int) $this->barangId : null);
    }

    public function getSummary(): array
    {
        return app(KartuStokAverageService::class)
            ->getSummary($this->bulan, $this->tahun, $this->barangId !== '' ? (int) $this->barangId : null);
    }

    public function openForm(): void
    {
        $this->showForm = false;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetValidation();
    }

    public function getPreview(): array
    {
        if ($this->formBarangId === '') {
            return [];
        }

        $service = app(KartuStokAverageService::class);
        $qty = max(0, (int) $this->formQty);
        $barangId = (int) $this->formBarangId;

        if ($this->formJenis === 'beli') {
            return $service->previewPembelian($barangId, $qty, (float) $this->formHargaBeli);
        }

        return $service->previewPenjualan($barangId, $qty);
    }

    public function getStockError(): ?string
    {
        if ($this->formBarangId === '' || $this->formJenis !== 'jual') {
            return null;
        }

        $saldo = app(KartuStokAverageService::class)->getSaldoSaatIni((int) $this->formBarangId);
        $stok = (int) $saldo['sisa_unit'];

        return (int) $this->formQty > $stok
            ? "Qty melebihi stok tersedia (maks. {$stok} unit)"
            : null;
    }

    public function getHargaWarning(): ?string
    {
        return $this->formJenis === 'beli' && (float) $this->formHargaBeli <= 0
            ? 'Harga beli tidak boleh 0'
            : null;
    }

    public function canSave(): bool
    {
        if ($this->formBarangId === '' || (int) $this->formQty <= 0) {
            return false;
        }

        if ($this->getStockError() !== null) {
            return false;
        }

        if ($this->formJenis === 'beli' && (float) $this->formHargaBeli <= 0) {
            return false;
        }

        return true;
    }

    public function saveTransaksi(): void
    {
        if (! $this->canSave()) {
            return;
        }

        $service = app(KartuStokAverageService::class);
        $barangId = (int) $this->formBarangId;
        $qty = (int) $this->formQty;
        $tanggal = $this->formTanggal ?: now()->format('Y-m-d');
        $keterangan = trim($this->formKeterangan);

        if ($this->formJenis === 'beli') {
            $service->tambahPembelian($barangId, $tanggal, $qty, (float) $this->formHargaBeli, $keterangan ?: null);
        } else {
            $service->tambahPenjualan($barangId, $tanggal, $qty, $keterangan ?: null);
        }

        Notification::make()
            ->title('Transaksi average berhasil disimpan')
            ->success()
            ->send();

        $this->showForm = false;
        $this->formQty = '1';
        $this->formHargaBeli = '0';
        $this->formKeterangan = '';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'sales';
    }
}
