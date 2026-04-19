<?php

namespace App\Filament\Resources\Pengeluarans;

use App\Filament\Resources\Pengeluarans\Pages\CreatePengeluaran;
use App\Filament\Resources\Pengeluarans\Pages\EditPengeluaran;
use App\Filament\Resources\Pengeluarans\Pages\ListPengeluarans;
use App\Filament\Resources\Pengeluarans\Pages\ViewPengeluaran;
use App\Models\Pengeluaran;
use App\Models\DaftarAkun;

use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use UnitEnum;
use Filament\Facades\Filament;

class PengeluaranResource extends Resource
{
    protected static ?string $model = Pengeluaran::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;
    protected static UnitEnum|string|null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Pengeluaran';
    protected static ?string $pluralModelLabel = 'Pengeluaran';
    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'kode_pengeluaran';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Data Pengeluaran')
                ->schema([

                    Forms\Components\TextInput::make('kode_pengeluaran')
                        ->label('Kode Pengeluaran')
                        ->required()
                        ->maxLength(255)
                        ->default(function () {
                            $lastKode = Pengeluaran::where('kode_pengeluaran', 'like', 'BB%')
                                ->orderByDesc('kode_pengeluaran')
                                ->value('kode_pengeluaran');

                            $lastNumber = $lastKode ? (int) substr($lastKode, 2) : 0;

                            return 'BB' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
                        })
                        ->readOnly(),

                    Forms\Components\DatePicker::make('tanggal_pengeluaran')
                        ->label('Tanggal')
                        ->default(now()->toDateString())
                        ->required(),

                    // ✅ AKUN BEBAN (HEADER 5 SAJA)
                    Forms\Components\Select::make('daftar_akun_id')
                        ->label('Akun Beban')
                        ->options(
                            DaftarAkun::where('header_akun', '5')
                                ->whereNull('parent_id') // ⬅️ cuma header "Beban"
                                ->get()
                                ->mapWithKeys(fn ($akun) => [
                                    $akun->id => $akun->kode_akun . ' - ' . $akun->nama_akun
                                ])
                        )
                        ->searchable()
                        ->preload()
                        ->required(),

                    // ✅ DIBAYAR DARI (HEADER 1)
                    Forms\Components\Select::make('kas_bank_id')
                        ->label('Dibayar Dari')
                        ->options(
                            DaftarAkun::where('header_akun', '1')
                                ->whereNotNull('parent_id')
                                ->where(function ($q) {
                                    $q->where('nama_akun', 'like', '%kas%')
                                    ->orWhere('nama_akun', 'like', '%bank%');
                                })
                                ->get()
                                ->mapWithKeys(fn ($akun) => [
                                    $akun->id => $akun->kode_akun . ' - ' . $akun->nama_akun
                                ])
                        )
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\TextInput::make('deskripsi')
                        ->label('Keterangan')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('jumlah')
                        ->label('Nominal')
                        ->required()
                        ->prefix('Rp')
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters([','])
                        ->numeric(),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('Lampiran')
                ->schema([
                    Forms\Components\FileUpload::make('bukti_transaksi')
                        ->label('Bukti Transaksi')
                        ->directory('bukti-transaksi')
                        ->image()
                        ->maxSize(2048),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_pengeluaran')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tanggal_pengeluaran')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                TextColumn::make('akunBeban.nama_akun')
                    ->label('Akun Beban')
                    ->sortable()
                    ->searchable(),

                // ✅ tampil kas/bank juga
                TextColumn::make('kasBank.nama_akun')
                    ->label('Dibayar Dari')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.')),
            ])
            ->filters([])
            ->recordActions([
                Actions\ViewAction::make(),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPengeluarans::route('/'),
            'create' => CreatePengeluaran::route('/create'),
            'view'   => ViewPengeluaran::route('/{record}'),
        ];
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'finance';
    }
}