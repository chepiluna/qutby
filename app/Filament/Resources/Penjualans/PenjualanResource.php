<?php

namespace App\Filament\Resources\Penjualans;

use App\Filament\Resources\Penjualans\Pages\CreatePenjualan;
use App\Filament\Resources\Penjualans\Pages\EditPenjualan;
use App\Filament\Resources\Penjualans\Pages\ListPenjualans;
use App\Filament\Resources\Penjualans\Pages\ViewPenjualan;
use App\Models\Barang;
use App\Models\Penjualan;
use App\Models\Pajak;
use BackedEnum;
use UnitEnum; 
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Illuminate\Database\Eloquent\Builder;

class PenjualanResource extends Resource
{
    protected static ?string $model = Penjualan::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static UnitEnum|string|null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Penjualan';

    protected static ?string $pluralModelLabel = 'Penjualan';

    protected static ?string $recordTitleAttribute = 'no_faktur';

public static function form(Schema $schema): Schema
{
    return $schema
        ->columns(1)
        ->components([
            Section::make('Informasi Faktur')
                ->schema([
                    DatePicker::make('tanggal_faktur')
                        ->label('Tanggal faktur')
                        ->required()
                        ->default(now()),

                    TextInput::make('no_faktur')
                        ->label('No. Faktur')
                        ->required()
                        ->readOnly()
                        ->maxLength(50)
                        ->afterStateHydrated(function (TextInput $component, $state) {
                            if (filled($state)) {
                                return;
                            }

                            $component->state(Penjualan::generateNextNoFaktur());
                        }),

                    Select::make('pelanggan_id')
                        ->label('Pelanggan')
                        ->relationship('pelanggan', 'nama_pelanggan')
                        ->searchable()
                        ->preload()
                        ->placeholder('Kosongkan jika tunai')
                        ->nullable()
                        ->reactive()
                        ->required(fn (Get $get) => $get('cara_bayar') === 'kredit'),

                    Select::make('cara_bayar')
                        ->label('Cara bayar')
                        ->options([
                            'tunai'  => 'Tunai',
                            'kredit' => 'Kredit',
                        ])
                        ->required()
                        ->live()
                        ->default('tunai')
                        ->afterStateUpdated(function (Set $set, $state) {
                            if ($state === 'tunai') {
                                $set('pelanggan_id', null); // 🔥 reset pelanggan
                            }
                        }),

                Select::make('metode_bayar')
                        ->label('Metode Bayar')
                        ->options([
                            'cash' => 'Cash',
                            'transfer' => 'Transfer',
                        ])
                        ->visible(fn (Get $get) => $get('cara_bayar') === 'tunai')
                        ->required(fn (Get $get) => $get('cara_bayar') === 'tunai')
                        ->live(),

                Select::make('akun_kas_id')
                        ->label('Kas / Bank')
                        ->relationship(
                            name: 'akunKas',
                            titleAttribute: 'nama_akun',
                            modifyQueryUsing: fn (Builder $query) =>
                                $query->where('header_akun', 1) // hanya aktiva lancar
                        )
                        ->preload()
                        ->searchable()
                        ->visible(fn (Get $get) =>
                            $get('cara_bayar') === 'tunai' &&
                            $get('metode_bayar') === 'transfer'
                        )
                        ->required(fn (Get $get) =>
                            $get('cara_bayar') === 'tunai' &&
                            $get('metode_bayar') === 'transfer'
                        ),
            
                Select::make('termin_id')
                        ->label('Syarat pembayaran')
                        ->relationship('termin', 'nama')
                        ->visible(fn ($get) => $get('cara_bayar') === 'kredit') // hanya muncul kalau kredit
                        ->required(fn ($get) => $get('cara_bayar') === 'kredit')
                        ->searchable()
                        ->preload(),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('Detail Penjualan')
                ->schema([
                    Repeater::make('detail')
                        ->label('')
                        ->relationship('detail')
                        ->afterStateHydrated(function (Get $get, Set $set) {
                            static::updateTotals($get, $set);
                        })
                        ->live(debounce: 300)
                        ->afterStateUpdated(fn (Get $get, Set $set) => static::updateTotals($get, $set))
                        ->columns(4)
                        ->defaultItems(1)
                        ->required()
                        ->itemLabel(fn ($state) => $state['barang']['nama_barang'] ?? 'Item') // tracking label
                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data): ?array {
                            if (blank($data['barang_id'] ?? null)) {
                                return null;
                            }
                            return $data;
                        })
                        ->mutateRelationshipDataBeforeSaveUsing(function (array $data): ?array {
                            if (blank($data['barang_id'] ?? null)) {
                                return null;
                            }
                            return $data;
                        })
                        
                        ->schema([
                            Select::make('barang_id')
                                ->label('Barang')
                                ->relationship('barang', 'nama_barang')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                    $barang = $state ? Barang::find($state) : null;
                                    $harga  = (float) ($barang?->harga_barang ?? 0);

                                    $set('harga_satuan', $harga);

                                    $qty      = (int) ($get('qty') ?? 1);
                                    $subtotal = $qty * $harga;
                                    $set('subtotal', $subtotal);

                                    static::updateTotals($get, $set);
                                }),

                            TextInput::make('qty')
                                ->label('Qty')
                                ->numeric()
                                ->required()
                                ->default(1)
                                ->suffix('pcs')
                                ->live(debounce: 300)
                                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                    $harga = (float) ($get('harga_satuan') ?? 0);
                                    $qty   = (int) ($state ?? 0);

                                    // SUBTOTAL = BRUTO ITEM
                                    $set('subtotal', $qty * $harga);

                                    static::updateTotals($get, $set);
                                }),

                            TextInput::make('harga_satuan')
                                ->label('Harga jual per satuan')
                                ->numeric()
                                ->required()
                                ->prefix('Rp')
                                ->reactive()
                                ->live(debounce: 300)
                                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                    $qty   = (int) ($get('qty') ?? 0);
                                    $harga = (float) ($state ?? 0);

                                    // SUBTOTAL = BRUTO ITEM
                                    $set('subtotal', $qty * $harga);

                                    static::updateTotals($get, $set);
                                }),

                            TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->numeric()
                                ->required()
                                ->prefix('Rp')
                                ->readOnly(),
                        ]),
                ])
                ->columnSpanFull(),

            Section::make('Ringkasan')
                ->schema([
                    TextInput::make('total_bruto')
                        ->label('Total bruto')
                        ->numeric()
                        ->default(0)
                        ->prefix('Rp')
                        ->readOnly(),

                    Grid::make(['default' => 2])
                        ->schema([
                            TextInput::make('diskon_persen')
                                ->label('Diskon (%)')
                                ->numeric()
                                ->default(0)
                                ->suffix('%')
                                ->disabled()         // kunci input biar nggak bisa diketik [web:161]
                                ->dehydrated(false), // jangan masuk DB [web:72]

                            TextInput::make('diskon_rp')
                                ->label('Diskon (Rp)')
                                ->numeric()
                                ->default(0)
                                ->prefix('Rp')
                                ->readOnly(), // boleh readOnly karena ini memang mau kesave [web:107]
                        ])
                        ->columnSpanFull(),
                            TextInput::make('total_netto')
                                ->label('Total netto')
                                ->numeric()
                                ->default(0)
                                ->prefix('Rp')
                                ->readOnly(),

                            Checkbox::make('pakai_pajak')
                                ->label(function () {
                                    // Ambil pajak PPN untuk tampilkan di label
                                    $pajak = Pajak::where('kode', 'PPN')->first();
                                    return $pajak 
                                        ? "Gunakan {$pajak->kode} " . number_format($pajak->persen, 0) . "%" 
                                        : 'Gunakan PPN';
                                })
                                ->default(false)
                                ->reactive()
                                ->dehydrated(false) 
                                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                    if ($state) {
                                        // Ambil pajak dan set ke hidden fields
                                        $pajak = Pajak::where('kode', 'PPN')->first();
                                        if ($pajak) {
                                            $set('pajak_id', $pajak->id);
                                            $set('pajak_persen', $pajak->persen);
                                        }
                                    } else {
                                        // Reset kalau uncheck
                                        $set('pajak_id', null);
                                        $set('pajak_persen', 0);
                                    }
                                    
                                    static::updateTotals($get, $set);
                                }),

                            // Hidden fields untuk simpan data
                            Hidden::make('pajak_id')
                                ->dehydrated(true),

                            Hidden::make('pajak_persen')
                                ->dehydrated(true)
                                ->default(0),
                                            ])
                ->columns(2)
                ->columnSpanFull(),
        ]);
}
    public static function updateTotals(Get $get, Set $set): void
{
    $details = collect($get('detail') ?? [])
        ->filter(fn ($item) => filled($item['barang_id'] ?? null));

    // Total bruto: hitung dari qty * harga_satuan (jangan ambil dari subtotal)
    $totalBruto = $details->sum(function ($item) {
        $qty   = (int) ($item['qty'] ?? 0);
        $harga = (float) ($item['harga_satuan'] ?? 0);
        return $qty * $harga;
    });
    $set('total_bruto', $totalBruto);

    // Diskon per item
    $totalDiskonRp = $details->sum(function ($item) {
        $qty   = (int) ($item['qty'] ?? 0);
        $harga = (float) ($item['harga_satuan'] ?? 0);

        $brutoItem = $qty * $harga;
        $diskonPersenItem = $qty > 5 ? 10 : 0;

        return $brutoItem * $diskonPersenItem / 100;
    });
    $set('diskon_rp', $totalDiskonRp);

    // Diskon (%) efektif (karena per item)
    $set('diskon_persen', $totalBruto > 0 ? ($totalDiskonRp / $totalBruto) * 100 : 0);

    $pajakPersen = (float) ($get('pajak_persen') ?? 0);

    $dpp = $totalBruto - $totalDiskonRp;
    $pajakRp = $dpp * $pajakPersen / 100;

    $set('total_netto', $dpp + $pajakRp);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal_faktur')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                TextColumn::make('no_faktur')
                    ->label('No. Faktur')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pelanggan.nama_pelanggan')
                    ->label('Pelanggan') 
                    ->searchable(),
                TextColumn::make('total_netto')
                    ->label('Total')
                    ->formatStateUsing(
                        fn ($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.')
                    ),
                TextColumn::make('cara_bayar')
                    ->label('Cara bayar')
                    ->badge(),
                TextColumn::make('termin.nama')  // ← TAMBAHAN INI
                ->label('Termin')
                ->searchable()
                ->sortable()
                ->default('-'),
            ])
            ->recordActions([
                Actions\ViewAction::make(),
                //Actions\EditAction::make(),
                //Actions\DeleteAction::make(),
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
            'index'  => ListPenjualans::route('/'),
            'create' => CreatePenjualan::route('/create'),
            'view'   => ViewPenjualan::route('/{record}'),
            //'edit'   => EditPenjualan::route('/{record}/edit'),
        ];
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'sales';
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

}
