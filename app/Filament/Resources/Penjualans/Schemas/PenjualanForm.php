<?php

namespace App\Filament\Resources\Penjualans\Schemas;

use App\Models\Barang;
use App\Models\Pajak;
use App\Models\Penjualan;
use App\Models\KartuStokAverage;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Builder;

class PenjualanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([

                Section::make('Informasi Faktur')
                    ->schema([

                        DatePicker::make('tanggal_faktur')
                            ->label('Tanggal faktur')
                            ->required()
                            ->default(now())
                            ->minDate(now()->startOfMonth())
                            ->maxDate(now()),

                        TextInput::make('no_faktur')
                            ->label('No. Faktur')
                            ->required()
                            ->readOnly()
                            ->maxLength(50)
                            ->afterStateHydrated(function (TextInput $component, $state) {

                                if (filled($state)) {
                                    return;
                                }

                                $component->state(
                                    Penjualan::generateNextNoFaktur()
                                );
                            }),

                        Select::make('pelanggan_id')
                            ->label('Pelanggan')
                            ->placeholder('Pilih pelanggan')
                            ->relationship('pelanggan', 'nama_pelanggan')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->live()
                            ->required(
                                fn(Get $get) =>
                                $get('cara_bayar') === 'kredit'
                            ),

                        Select::make('cara_bayar')
                            ->label('Cara bayar')
                            ->placeholder('Pilih cara bayar')
                            ->options([
                                'tunai'  => 'Tunai',
                                'kredit' => 'Kredit',
                            ])
                            ->required()
                            ->live()
                            ->default('tunai')
                            ->afterStateUpdated(function (Set $set, $state) {

                                if ($state === 'tunai') {
                                    $set('pelanggan_id', null);
                                }
                            }),

                        Select::make('metode_bayar')
                            ->label('Metode Bayar')
                            ->placeholder('Pilih metode bayar')
                            ->options([
                                'cash' => 'Cash',
                                'transfer' => 'Transfer',
                            ])
                            ->visible(
                                fn(Get $get) =>
                                $get('cara_bayar') === 'tunai'
                            )
                            ->required(
                                fn(Get $get) =>
                                $get('cara_bayar') === 'tunai'
                            )
                            ->live(),

                        Select::make('akun_kas_id')
                            ->label('Kas / Bank')
                            ->placeholder('Pilih kas / bank')
                            ->relationship(
                                name: 'akunKas',
                                titleAttribute: 'nama_akun',
                                modifyQueryUsing: fn(Builder $query) =>
                                $query->where('header_akun', 1)
                            )
                            ->preload()
                            ->searchable()
                            ->visible(
                                fn(Get $get) =>
                                $get('cara_bayar') === 'tunai'
                                &&
                                $get('metode_bayar') === 'transfer'
                            )
                            ->required(
                                fn(Get $get) =>
                                $get('cara_bayar') === 'tunai'
                                &&
                                $get('metode_bayar') === 'transfer'
                            ),

                        Select::make('termin_id')
                            ->label('Syarat pembayaran')
                            ->placeholder('Pilih syarat pembayaran')
                            ->relationship('termin', 'nama')
                            ->visible(
                                fn($get) =>
                                $get('cara_bayar') === 'kredit'
                            )
                            ->required(
                                fn($get) =>
                                $get('cara_bayar') === 'kredit'
                            )
                            ->searchable()
                            ->preload(),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Rincian Penjualan Barang')
                    ->schema([

                        Repeater::make('detail')
                            ->label('Rincian Barang')
                            ->relationship('detail')
                            ->addActionLabel('Tambah barang')
                            ->columns(4)
                            ->defaultItems(1)
                            ->required()

                            ->itemLabel(
                                fn($state) =>
                                $state['barang']['nama_barang'] ?? 'Item'
                            )

                            ->afterStateHydrated(function (Get $get, Set $set) {
                                \App\Filament\Resources\Penjualans\PenjualanResource::updateTotals($get, $set);
                            })

                            ->live(debounce: 300)

                            ->afterStateUpdated(function (Get $get, Set $set) {
                                \App\Filament\Resources\Penjualans\PenjualanResource::updateTotals($get, $set);
                            })

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
                                    ->live()

                                    ->afterStateUpdated(function (
                                        Get $get,
                                        Set $set,
                                        $state
                                    ) {

                                        if (blank($state)) {
                                            return;
                                        }

                                        $hpp = (float) KartuStokAverage::query()
                                            ->where('barang_id', (int) $state)
                                            ->orderByDesc('id')
                                            ->value('harga_rata_rata');

                                        $hpp = $hpp ?: 0;

                                        $hargaJual = $hpp * 1.55;

                                        $qty = (int) ($get('qty') ?: 1);

                                        $subtotal = $qty * $hargaJual;

                                        $set(
                                            'harga_satuan',
                                            round($hargaJual, 2)
                                        );

                                        $set(
                                            'subtotal',
                                            round($subtotal, 2)
                                        );

                                        \App\Filament\Resources\Penjualans\PenjualanResource::updateTotals($get, $set);
                                    }),

                                TextInput::make('qty')
                                    ->label('Qty')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->suffix('pcs')
                                    ->live(debounce: 300)

                                    ->afterStateUpdated(function (
                                        Get $get,
                                        Set $set,
                                        $state
                                    ) {

                                        $harga = (float) ($get('harga_satuan') ?? 0);

                                        $qty = (int) ($state ?? 0);

                                        $subtotal = $qty * $harga;

                                        $set(
                                            'subtotal',
                                            round($subtotal, 2)
                                        );

                                        \App\Filament\Resources\Penjualans\PenjualanResource::updateTotals($get, $set);
                                    }),

                                TextInput::make('harga_satuan')
                                    ->label('Harga jual per satuan')
                                    ->numeric()
                                    ->required()
                                    ->prefix('Rp')
                                    ->live(debounce: 300)

                                    ->afterStateUpdated(function (
                                        Get $get,
                                        Set $set,
                                        $state
                                    ) {

                                        $qty = (int) ($get('qty') ?? 0);

                                        $harga = (float) ($state ?? 0);

                                        $subtotal = $qty * $harga;

                                        $set(
                                            'subtotal',
                                            round($subtotal, 2)
                                        );

                                        \App\Filament\Resources\Penjualans\PenjualanResource::updateTotals($get, $set);
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

                Section::make('Perhitungan Total')
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
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('diskon_rp')
                                    ->label('Diskon (Rp)')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->readOnly(),

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

                                $pajak = Pajak::where('kode', 'PPN')->first();

                                return $pajak
                                    ? "Gunakan {$pajak->kode} " . number_format($pajak->persen, 0) . "%"
                                    : 'Gunakan PPN';
                            })

                            ->default(false)
                            ->reactive()
                            ->dehydrated(false)

                            ->afterStateUpdated(function (
                                Get $get,
                                Set $set,
                                $state
                            ) {

                                if ($state) {

                                    $pajak = Pajak::where('kode', 'PPN')->first();

                                    if ($pajak) {

                                        $set('pajak_id', $pajak->id);

                                        $set('pajak_persen', $pajak->persen);
                                    }
                                } else {

                                    $set('pajak_id', null);

                                    $set('pajak_persen', 0);
                                }

                                \App\Filament\Resources\Penjualans\PenjualanResource::updateTotals($get, $set);
                            }),

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
}
