<?php

namespace App\Filament\Resources\Pembelian\Schemas;

use App\Models\Barang;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Checkbox;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Pages\Page;


class PembelianForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            /* =========================
             * INFORMASI PEMBELIAN
             * ========================= */
            Section::make('Informasi Pembelian')
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    Select::make('vendor_id')
    ->label('Vendor')
    ->relationship('vendor', 'nama_vendor')
    ->searchable()
    ->preload()
    ->live() // ⬅️ WAJIB
    ->afterStateUpdated(function ($state, Set $set, Get $get) {
        $vendor = \App\Models\Vendor::find($state);

        $diskon = $vendor?->diskon_persen ?? 0;

        // isi diskon otomatis
        $set('diskon_persen', $diskon);

        // hitung ulang total
        self::hitungTotal($get, $set);
    })
    ->required(),



                    DatePicker::make('tanggal')
                        ->default(now())
                        ->required(),

                    TextInput::make('nomor')
                        ->label('Nomor Pembelian')
                        ->default(fn () => \App\Models\Pembelian::generateNomorPembelian())
                        ->disabled()
                        ->dehydrated()
                        ->required(),
                ]),

            /* =========================
             * DETAIL PEMBELIAN
             * ========================= */
            Section::make('Detail Pemesanan')
                ->columnSpanFull()
                ->schema([

                    Repeater::make('details')
                         ->relationship('details')
                        ->label('Rincian Barang')
                        ->columns(4)

                        // ⬅️ WAJIB: total update saat tambah / hapus baris
                        ->afterStateUpdated(fn (Get $get, Set $set) =>
                            self::hitungTotal($get, $set)
                        )

                        ->schema([

                            /* BARANG */
                            Select::make('barang_id')
                                ->label('Barang')
                                ->relationship('barang', 'nama_barang')
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    $barang = Barang::find($state);

                                    if (! $barang) return;

                                    $faktor = $barang->satuan === 'lusin' ? 12 : 1;

                                    $set('satuan', $barang->satuan);
                                    $set('qty', 1);
                                    $set('harga_satuan', $barang->harga_barang);
                                    $set('subtotal', 1 * $faktor * $barang->harga_barang);

                                    self::hitungTotal($get, $set);
                                })
                                ->required(),

                            /* QTY */
                            TextInput::make('qty')
                                ->label('Qty')
                                ->numeric()
                                ->default(1)
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                    $faktor = $get('satuan') === 'lusin' ? 12 : 1;

                                    $set(
                                        'subtotal',
                                        ($state ?? 0) * $faktor * ($get('harga_satuan') ?? 0)
                                    );

                                    self::hitungTotal($get, $set);
                                })
                                ->required(),

                            /* SATUAN (AUTO, READONLY) */
                            TextInput::make('satuan')
                                ->label('Satuan')
                                ->disabled()
                                ->dehydrated(),

                            /* HARGA */
                           TextInput::make('harga_satuan')
                               ->label('Harga Satuan')
                               ->numeric()
                               ->prefix('Rp ')
                               ->disabled()
                               ->dehydrated(),


                            /* SUBTOTAL */
                            TextInput::make('subtotal')
                             ->label('Subtotal')
                             ->numeric()
                             ->prefix('Rp ')
                             ->disabled()
                             ->dehydrated()
                             ->afterStateUpdated(fn (Get $get, Set $set) =>
                               self::hitungTotal($get, $set)
                             ),
                        ]),

                    /* =========================
                     * TOTAL
                     * ========================= */
                    TextInput::make('total_bruto')
                    ->label('Total')
                    ->numeric()
                    ->prefix('Rp ')
                    ->disabled()
                    ->dehydrated(), // ⬅️ WAJIB

                    TextInput::make('diskon_persen')
                     ->label('Diskon (%)')
                     ->numeric()
                     ->disabled()     // ⬅️ USER TIDAK BISA EDIT
                     ->dehydrated(), // ⬅️ TETAP DISIMPAN KE DB


                    Checkbox::make('pakai_pajak')
                        ->label('PPN 11%')
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) =>
                            self::hitungTotal($get, $set)
                        ),

                    TextInput::make('total_netto')
                        ->label('Total Akhir')
                        ->numeric()
                        ->prefix('Rp ')
                        ->disabled()
                        ->dehydrated(),
                        ]),

                    Select::make('status')
    ->label('Status Pembelian')
    ->options([
        'menunggu' => 'Menunggu',
        'partial'  => 'Partial',
        'selesai'  => 'Selesai',
    ])
    ->default('menunggu')
    ->disabled()     // ⬅️ tidak bisa diubah saat create
    ->dehydrated()   // ⬅️ tetap disimpan ke DB
    ->required(),

        ]);
    }

    /* =========================
     * HITUNG TOTAL
     * ========================= */
    protected static function hitungTotal(Get $get, Set $set): void
    {
        $details = $get('detail') ?? [];

        $total = collect($details)
            ->sum(fn ($row) => (float) ($row['subtotal'] ?? 0));

        $diskon = $total * ((float) ($get('diskon_persen') ?? 0) / 100);
        $netto  = $total - $diskon;

        if ($get('pakai_pajak')) {
            $netto += $netto * 0.11;
        }

        $set('total_bruto', $total);
        $set('total_netto', $netto);
    }
}
