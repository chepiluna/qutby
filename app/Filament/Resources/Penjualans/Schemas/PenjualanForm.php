<?php

namespace App\Filament\Resources\Penjualans\Schemas;

use App\Models\Barang;
use App\Models\Pajak;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Repeater;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\DatePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class PenjualanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            DatePicker::make('tanggal_faktur')
                ->label('Tanggal faktur')
                ->required()
                ->default(now()),

            TextInput::make('no_faktur')
                ->label('No. Faktur')
                ->readOnly()
                ->required()
                ->maxLength(50),

            // 🔥 CARA BAYAR (WAJIB)
            Select::make('cara_bayar')
                ->label('Tipe Pembayaran')
                ->options([
                    'tunai' => 'Tunai',
                    'kredit' => 'Kredit',
                ])
                ->required()
                ->reactive(),

            // 🔥 PELANGGAN (DINAMIS)
            Select::make('pelanggan_id')
                ->label('Pelanggan')
                ->relationship('pelanggan', 'nama')
                ->searchable()
                ->preload()
                ->placeholder('Kosongkan jika tunai')
                ->nullable()
                ->reactive()
                ->required(fn (Get $get) => $get('cara_bayar') === 'kredit'),

            // DETAIL
            Repeater::make('detail')
                ->label('Detail penjualan')
                ->relationship('detail')
                ->columns(4)
                ->schema([
                    Select::make('barang_id')
                        ->label('Barang')
                        ->relationship('barang', 'nama_barang')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (Get $get, Set $set, ?int $state) {
                            $barang = $state ? Barang::find($state) : null;
                            $harga  = $barang?->harga_jual ?? 0;

                            $set('harga_satuan', $harga);

                            $qty = (int) ($get('qty') ?? 1);
                            $set('subtotal', $qty * $harga);

                            \App\Filament\Resources\Penjualans\PenjualanResource::updateTotals($get, $set);
                        }),

                    TextInput::make('qty')
                        ->label('Qty')
                        ->numeric()
                        ->default(1)
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (Get $get, Set $set, $state) {
                            $harga = (float) ($get('harga_satuan') ?? 0);
                            $qty   = (int) ($state ?? 0);

                            $set('subtotal', $qty * $harga);

                            \App\Filament\Resources\Penjualans\PenjualanResource::updateTotals($get, $set);
                        }),

                    TextInput::make('harga_satuan')
                        ->label('Harga satuan')
                        ->numeric()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (Get $get, Set $set, $state) {
                            $qty   = (int) ($get('qty') ?? 0);
                            $harga = (float) ($state ?? 0);

                            $set('subtotal', $qty * $harga);

                            \App\Filament\Resources\Penjualans\PenjualanResource::updateTotals($get, $set);
                        }),

                    TextInput::make('subtotal')
                        ->label('Subtotal')
                        ->numeric()
                        ->readOnly()
                        ->required(),
                ]),

            TextInput::make('total_bruto')
                ->label('Total bruto')
                ->numeric()
                ->default(0)
                ->readOnly(),

            // PAJAK
            Select::make('pajak_id')
                ->label('Pajak')
                ->relationship('pajak', 'kode')
                ->searchable()
                ->preload()
                ->reactive()
                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                    $pajak  = $state ? Pajak::find($state) : null;
                    $persen = $pajak?->persen ?? 0;

                    $set('pajak_persen', $persen);
                    \App\Filament\Resources\Penjualans\PenjualanResource::updateTotals($get, $set);
                }),

            TextInput::make('pajak_persen')
                ->label('Pajak (%)')
                ->numeric()
                ->default(0)
                ->readOnly(),

            TextInput::make('diskon_persen')
                ->label('Diskon (%)')
                ->numeric()
                ->default(0)
                ->reactive()
                ->afterStateUpdated(function (Get $get, Set $set) {
                    \App\Filament\Resources\Penjualans\PenjualanResource::updateTotals($get, $set);
                }),

            TextInput::make('diskon_rp')
                ->label('Diskon (Rp)')
                ->numeric()
                ->default(0)
                ->readOnly(),

            TextInput::make('total_netto')
                ->label('Total netto')
                ->numeric()
                ->default(0)
                ->readOnly(),
        ]);
    }
}