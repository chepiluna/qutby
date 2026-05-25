<?php

namespace App\Filament\Resources\PembayaranPembelians\Schemas;

use App\Models\Pembelian;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PembayaranPembelianForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Pembayaran Utang')
                ->schema([
                    Select::make('pembelian_id')
                        ->label('Nomor Pembelian')
                        // ✅ FIX: di model Pembelian field-nya biasanya 'nomor' (bukan 'nomor_pembelian')
                        ->relationship('pembelian', 'nomor')
                        ->searchable()
                        ->preload()
                        ->required()
                        // ✅ ambil dari URL Step 2: ?pembelian_id=xx
                        ->default(fn () => request()->get('pembelian_id'))
                        ->live()
                        // ✅ saat pertama kali kebuka (hydrated), auto isi vendor + details
                        ->afterStateHydrated(function ($state, Set $set) {
                            if (! $state) {
                                return;
                            }

                            $pembelian = Pembelian::with(['details.barang'])->find($state);

                            if (! $pembelian) {
                                $set('vendor_id', null);
                                $set('details', []);
                                return;
                            }

                            // ✅ vendor otomatis
                            $set('vendor_id', $pembelian->vendor_id);

                            // isi detail pembayaran dari detail pembelian
                            $set('details', $pembelian->details->map(function ($item) {
                                return [
                                    'barang_id'   => $item->barang_id,
                                    'nama_barang' => $item->barang?->nama_barang ?? '-',
                                    'qty'         => $item->qty,
                                    // detail pembayaran pakai field 'harga' (bukan harga_satuan)
                                    'harga'       => $item->harga_satuan,
                                    'subtotal'    => $item->subtotal,
                                ];
                            })->toArray());
                        })
                        // ✅ kalau user ganti pembelian_id, isi ulang
                        ->afterStateUpdated(function ($state, Set $set) {
                            if (! $state) {
                                $set('vendor_id', null);
                                $set('details', []);
                                return;
                            }

                            $pembelian = Pembelian::with(['details.barang'])->find($state);

                            if (! $pembelian) {
                                $set('vendor_id', null);
                                $set('details', []);
                                return;
                            }

                            $set('vendor_id', $pembelian->vendor_id);

                            $set('details', $pembelian->details->map(function ($item) {
                                return [
                                    'barang_id'   => $item->barang_id,
                                    'nama_barang' => $item->barang?->nama_barang ?? '-',
                                    'qty'         => $item->qty,
                                    'harga'       => $item->harga_satuan,
                                    'subtotal'    => $item->subtotal,
                                ];
                            })->toArray());
                        }),

                    // ✅ vendor dibuat disabled tapi tetap tersimpan
                    Select::make('vendor_id')
                        ->label('Vendor')
                        ->relationship(name: 'vendor', titleAttribute: 'nama_vendor')
                        ->disabled()
                        // ✅ FIX: jangan pakai ->saved(); gunakan dehydrated
                        ->dehydrated(true)
                        ->required(),
                ]),

            Section::make('Detail Pembayaran')
                ->schema([
                    Repeater::make('details')
                        ->relationship('details')
                        ->label('Detail Barang')
                        ->schema([
                            // ✅ nama_barang hanya tampilan
                            TextInput::make('nama_barang')
                                ->label('Barang')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('qty')
                                ->numeric()
                                ->disabled()
                                ->dehydrated(true),

                            TextInput::make('harga')
                                ->numeric()
                                ->prefix('Rp')
                                ->disabled()
                                ->dehydrated(true),

                            TextInput::make('subtotal')
                                ->numeric()
                                ->prefix('Rp')
                                ->disabled()
                                ->dehydrated(true),
                        ])
                        ->columns(4)
                        ->deletable(false)
                        ->addable(false)
                        ->reorderable(false)
                        ->default([]),
                ]),
        ]);
    }
}
