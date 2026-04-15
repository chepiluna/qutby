<?php

namespace App\Filament\Resources\Pembayarans\Schemas;

use App\Models\Piutang;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;

class PembayaranForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Informasi Pembayaran')
                ->schema([

                    Hidden::make('customer_id')
                        ->dehydrated(true),

                    Select::make('piutang_id')
                        ->label('No Faktur Piutang')
                        ->relationship(
                            name: 'piutang',
                            titleAttribute: 'no_faktur',
                            modifyQueryUsing: fn (Builder $query) =>
                                $query->where('status', 'belum_lunas')
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {

                            if (! $state) {
                                $set('customer_id', null);
                                $set('nama_pelanggan', null);
                                $set('jumlah_bayar', null);
                                $set('tanggal_diskon', null);
                                $set('diskon_termin', 0);
                                $set('total_setelah_diskon', null);
                                return;
                            }

                            $piutang = Piutang::with(['pelanggan', 'penjualan.termin'])->find($state);

                            if (! $piutang) return;

                            $set('customer_id', $piutang->pelanggan_id);
                            $set('nama_pelanggan', $piutang->pelanggan?->nama_pelanggan);

                            $total = (float) $piutang->sisa_piutang;
                            $set('jumlah_bayar', $total);

                            $termin = $piutang->penjualan?->termin;

                            if (! $termin || ! $piutang->penjualan?->tanggal_faktur) {
                                $set('tanggal_diskon', null);
                                $set('diskon_termin', 0);
                                $set('total_setelah_diskon', $total);
                                return;
                            }

                            $hariDiskon = (int) $termin->hari_diskon;
                            $persenDiskon = (float) $termin->diskon_persen;

                            $tglFaktur = Carbon::parse($piutang->penjualan->tanggal_faktur);
                            $batas = $tglFaktur->copy()->addDays($hariDiskon);

                            $tglBayar = $get('tanggal_bayar')
                                ? Carbon::parse($get('tanggal_bayar'))
                                : now();

                            $diskon = $tglBayar->lessThanOrEqualTo($batas)
                                ? $total * ($persenDiskon / 100)
                                : 0;

                            $set('tanggal_diskon', $batas);
                            $set('diskon_termin', $diskon);
                            $set('total_setelah_diskon', $total - $diskon);
                        }),

                    TextInput::make('nama_pelanggan')
                        ->label('Nama Pelanggan')
                        ->readOnly()
                        ->dehydrated(false),

                    DatePicker::make('tanggal_bayar')
                        ->label('Tanggal Bayar')
                        ->required()
                        ->default(now())
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {

                            if (! $get('piutang_id')) return;

                            $piutang = Piutang::with(['penjualan.termin'])->find($get('piutang_id'));
                            if (! $piutang) return;

                            $total = (float) $piutang->sisa_piutang;

                            $termin = $piutang->penjualan?->termin;

                            if (! $termin || ! $piutang->penjualan?->tanggal_faktur) {
                                $set('diskon_termin', 0);
                                $set('total_setelah_diskon', $total);
                                return;
                            }

                            $hariDiskon = (int) $termin->hari_diskon;
                            $persenDiskon = (float) $termin->diskon_persen;

                            $tglFaktur = Carbon::parse($piutang->penjualan->tanggal_faktur);
                            $batas = $tglFaktur->copy()->addDays($hariDiskon);

                            $tglBayar = Carbon::parse($state);

                            $diskon = $tglBayar->lessThanOrEqualTo($batas)
                                ? $total * ($persenDiskon / 100)
                                : 0;

                            $set('tanggal_diskon', $batas);
                            $set('diskon_termin', $diskon);
                            $set('total_setelah_diskon', $total - $diskon);
                        }),



                    Select::make('metode_bayar')
                        ->label('Metode Bayar')
                        ->options([
                            'transfer' => 'Transfer',
                            'cash'     => 'Cash',
                        ])
                        ->required()
                        ->live(),

                    Select::make('akun_bank_id')
                        ->label('Kas / Bank')
                        ->relationship(
                            name: 'bankAkun',
                            titleAttribute: 'nama_akun',
                            modifyQueryUsing: fn (Builder $query) =>
                                $query->where('header_akun', 1)
                        )
                        ->searchable()
                        ->preload()
                        ->placeholder('Pilih akun bank')
                        ->visible(fn ($get) => $get('metode_bayar') === 'transfer')
                        ->required(fn ($get) => $get('metode_bayar') === 'transfer'),

                    Hidden::make('keterangan')
                        ->default('lunas')
                        ->dehydrated(true),

                ])
                ->columns(1),

            Section::make('Nominal & Diskon')
                ->schema([

                    TextInput::make('jumlah_bayar')
                        ->label('Total Tagihan')
                        ->numeric()
                        ->prefix('Rp')
                        ->readOnly(),

                    DatePicker::make('tanggal_diskon')
                        ->label('Batas Diskon')
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('diskon_termin')
                        ->label('Diskon Termin')
                        ->numeric()
                        ->prefix('Rp')
                        ->readOnly(),

                    TextInput::make('total_setelah_diskon')
                        ->label('Total Bayar')
                        ->numeric()
                        ->prefix('Rp')
                        ->readOnly(),
                    
                    Placeholder::make('info_diskon')
                        ->label('Info Diskon')
                        ->content(function ($get) {

                            if (! $get('piutang_id')) return '-';

                            $piutang = \App\Models\Piutang::with('penjualan.termin')
                                ->find($get('piutang_id'));

                            $termin = $piutang?->penjualan?->termin;

                            if (! $termin) return '-';

                            return "Diskon {$termin->diskon_persen}% (maks {$termin->hari_diskon} hari)";
                        })
                ])
                ->columns(1),
        ]);
    }
}