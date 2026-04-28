<?php

namespace App\Filament\Resources\Pembayarans\Schemas;

use App\Models\Piutang;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PembayaranForm
{
    public static function configure(Schema $schema): Schema
    {
        $piutangId = request()->get('piutang_id');

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
                        ->default($piutangId)
                        //->disabled($piutangId ? true : false)

                        ->afterStateHydrated(function ($state, callable $set, callable $get) {
                            self::loadPiutangData($state, $set, $get);
                        })

                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            self::loadPiutangData($state, $set, $get);
                        }),

                    TextInput::make('nama_pelanggan')
                        ->label('Nama Pelanggan')
                        ->default(function () use ($piutangId) {
                            if (! $piutangId) return null;

                            return \App\Models\Piutang::with('pelanggan')
                                ->find($piutangId)?->pelanggan?->nama_pelanggan;
                        })
                        ->readOnly()
                        ->dehydrated(false),

                    DatePicker::make('tanggal_bayar')
                        ->label('Tanggal Bayar')
                        ->required()
                        ->default(now())
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            self::loadPiutangData($get('piutang_id'), $set, $get);
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
                        ->default(function () use ($piutangId) {
                            return \App\Models\Piutang::find($piutangId)?->sisa_piutang;
                        })
                        ->readOnly(),

                    DatePicker::make('tanggal_diskon')
                        ->label('Batas Diskon')
                        ->default(function () use ($piutangId) {

                            $piutang = \App\Models\Piutang::with('penjualan.termin')
                                ->find($piutangId);

                            if (! $piutang?->penjualan?->termin) return null;

                            return \Carbon\Carbon::parse($piutang->penjualan->tanggal_faktur)
                                ->addDays($piutang->penjualan->termin->hari_diskon);
                        })
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('diskon_termin')
                        ->label('Diskon Termin')
                        ->default(function () use ($piutangId) {

                            $piutang = \App\Models\Piutang::with('penjualan.termin')
                                ->find($piutangId);

                            if (! $piutang?->penjualan?->termin) return 0;

                            return $piutang->sisa_piutang *
                                ($piutang->penjualan->termin->diskon_persen / 100);
                        })
                        ->readOnly(),

                    TextInput::make('total_setelah_diskon')
                        ->label('Total Bayar')
                        ->default(function () use ($piutangId) {

                            $piutang = \App\Models\Piutang::with('penjualan.termin')
                                ->find($piutangId);

                            if (! $piutang?->penjualan?->termin)
                                return $piutang?->sisa_piutang;

                            $diskon = $piutang->sisa_piutang *
                                ($piutang->penjualan->termin->diskon_persen / 100);

                            return $piutang->sisa_piutang - $diskon;
                        })
                        ->readOnly(),

                    Placeholder::make('info_diskon')
                        ->label('Info Diskon')
                        ->content(function ($get) {

                            if (! $get('piutang_id')) return '-';

                            $piutang = Piutang::with('penjualan.termin')
                                ->find($get('piutang_id'));

                            $termin = $piutang?->penjualan?->termin;

                            if (! $termin) return '-';

                            return "Diskon {$termin->diskon_persen}% (maks {$termin->hari_diskon} hari)";
                        }),

                ])
                ->columns(1),
        ]);
    }

    public static function loadPiutangData($state, callable $set, callable $get): void
    {
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

        $hariDiskon   = (int) $termin->hari_diskon;
        $persenDiskon = (float) $termin->diskon_persen;

        $tglFaktur = Carbon::parse($piutang->penjualan->tanggal_faktur);
        $batas     = $tglFaktur->copy()->addDays($hariDiskon);

        $tglBayar = $get('tanggal_bayar')
            ? Carbon::parse($get('tanggal_bayar'))
            : now();

        $diskon = $tglBayar->lessThanOrEqualTo($batas)
            ? $total * ($persenDiskon / 100)
            : 0;

        $set('tanggal_diskon', $batas);
        $set('diskon_termin', $diskon);
        $set('total_setelah_diskon', $total - $diskon);
    }
}