<?php

namespace App\Filament\Pages;

use App\Models\Pelanggan;
use App\Models\Piutang;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use UnitEnum;

class LaporanPiutangPelanggan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;
    protected static UnitEnum|string|null $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Laporan Piutang';
    protected ?string $heading = '';

    protected string $view = 'filament.pages.laporan-piutang-pelanggan';

    // Dipakai oleh form + blade: data_get($this->data, 'from'), dst
    public ?array $data = [];

    public function mount(): void
    {
        // Biar sama kaya Buku Besar: sebelum dipilih, laporan belum muncul
        $this->form->fill([
            'pelanggan_id' => null,
            'from' => null,
            'until' => null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('pelanggan_id')
                    ->label('Pelanggan')
                    ->options(Pelanggan::query()->pluck('nama_pelanggan', 'id'))
                    ->searchable()
                    ->preload()
                    ->live(),

                DatePicker::make('from')
                    ->label('Dari tanggal')
                    ->required()
                    ->displayFormat('d/m/Y')
                    ->live(),

                DatePicker::make('until')
                    ->label('Sampai tanggal')
                    ->required()
                    ->displayFormat('d/m/Y')
                    ->live(),
            ])
            ->columns(3)
            ->statePath('data');
    }

    /**
     * True kalau filter sudah lengkap, dipakai blade untuk conditional render.
     */
    public function getIsReadyProperty(): bool
    {
        return filled(data_get($this->data, 'pelanggan_id'))
            && filled(data_get($this->data, 'from'))
            && filled(data_get($this->data, 'until'));
    }

    /**
     * Data pelanggan terpilih, buat ditampilin di heading tabel.
     */
    public function getPelangganTerpilihProperty(): ?Pelanggan
    {
        $id = data_get($this->data, 'pelanggan_id');

        return $id ? Pelanggan::find($id) : null;
    }

    // Dipakai oleh blade: $this->piutangs
    public function getPiutangsProperty(): Collection
    {
        if (! $this->isReady) {
            return collect();
        }

        $pelangganId = data_get($this->data, 'pelanggan_id');
        $from = data_get($this->data, 'from');
        $until = data_get($this->data, 'until');

        return Piutang::query()
            ->where('pelanggan_id', $pelangganId)
            ->whereDate('tanggal_faktur', '>=', $from)
            ->whereDate('tanggal_faktur', '<=', $until)
            ->orderBy('tanggal_faktur')
            ->orderBy('id')
            ->get();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'sales';
    }
}
