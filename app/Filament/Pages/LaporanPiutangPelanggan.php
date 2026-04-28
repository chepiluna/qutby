<?php

namespace App\Filament\Pages;

use App\Models\Pelanggan;
use App\Models\Piutang;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
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

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'pelanggan_id' => null,
            'bulan' => now()->month,
            'tahun' => now()->year,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('pelanggan_id')
                    ->label('Nama Pelanggan')
                    ->options(
                        Pelanggan::query()->pluck('nama_pelanggan', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->live(),

                Select::make('bulan')
                    ->label('Bulan')
                    ->options([
                        1 => 'Januari',
                        2 => 'Februari',
                        3 => 'Maret',
                        4 => 'April',
                        5 => 'Mei',
                        6 => 'Juni',
                        7 => 'Juli',
                        8 => 'Agustus',
                        9 => 'September',
                        10 => 'Oktober',
                        11 => 'November',
                        12 => 'Desember',
                    ])
                    ->required()
                    ->live(),

                Select::make('tahun')
                    ->label('Tahun')
                    ->options(
                        collect(range(date('Y') - 5, date('Y') + 1))
                            ->mapWithKeys(fn ($year) => [$year => $year])
                            ->toArray()
                    )
                    ->required()
                    ->live(),
            ])
            ->columns(3)
            ->statePath('data');
    }

    public function getPiutangsProperty(): Collection
    {
        $pelangganId = data_get($this->data, 'pelanggan_id');
        $bulan = data_get($this->data, 'bulan');
        $tahun = data_get($this->data, 'tahun');

        return Piutang::query()
            ->with('pelanggan')
            ->when($pelangganId, function ($query) use ($pelangganId) {
                $query->where('pelanggan_id', $pelangganId);
            })
            ->when($bulan, function ($query) use ($bulan) {
                $query->whereMonth('tanggal_faktur', $bulan);
            })
            ->when($tahun, function ($query) use ($tahun) {
                $query->whereYear('tanggal_faktur', $tahun);
            })
            ->orderBy('tanggal_faktur', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getTotalPiutangProperty(): float
    {
        return (float) $this->piutangs->sum('sisa_piutang');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'sales';
    }
}