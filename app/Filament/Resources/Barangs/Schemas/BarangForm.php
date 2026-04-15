<?php

namespace App\Filament\Resources\Barangs\Schemas;

use App\Models\Barang;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class BarangForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Barang')
                    ->schema([
                        TextInput::make('kode_barang')
                            ->label('Kode barang')
                            ->required()
                            ->readOnly()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->afterStateHydrated(function (TextInput $component, $state) {
                                if (blank($state)) {
                                    $component->state(Barang::generateNextKodeBarang());
                                }
                            }),

                        TextInput::make('nama_barang')
                            ->label('Nama barang')
                            ->required(),

                        TextInput::make('satuan')
                            ->label('Satuan')
                            ->default(null),

                        TextInput::make('stok')
                            ->label('Stok')
                            ->required()
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Harga')
                    ->schema([
                        TextInput::make('hpp_satuan')
                            ->label('HPP satuan')
                            ->required()
                            ->prefix('Rp')
                            ->numeric()
                            ->live(debounce: 500)
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                // kalau user sudah pernah ubah harga jual manual, jangan timpa
                                if ($get('harga_barang_manual')) {
                                    return;
                                }

                                $hpp = (int) preg_replace('/\D/', '', (string) ($state ?? 0));
                                if ($hpp <= 0) {
                                    return;
                                }

                                $set('harga_barang', (int) round($hpp * 1.5));
                            }),

                        TextInput::make('harga_barang')
                            ->label('Harga jual')
                            ->required()
                            ->prefix('Rp')
                            ->numeric()
                            ->live(debounce: 150)
                            ->afterStateUpdated(function (Set $set, $state) {
                                // kalau user mengubah field ini (nilai > 0), tandai manual
                                $harga = (int) preg_replace('/\D/', '', (string) ($state ?? 0));
                                $set('harga_barang_manual', $harga > 0);
                            }),

                        // field tersembunyi untuk flag manual (tetap ikut state form)
                        TextInput::make('harga_barang_manual')
                            ->default(false)
                            ->dehydrated(false) // biar tidak tersimpan ke DB (kalau kolomnya tidak ada)
                            ->hidden(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
