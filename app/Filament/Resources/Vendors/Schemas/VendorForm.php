<?php

namespace App\Filament\Resources\Vendors\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Models\Vendor;

class VendorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Data Utama Vendor')
                ->schema([
                    TextInput::make('kode_vendor')
                    ->label('Vendor ID / Vendor Code')
                    ->default(fn () => Vendor::generateKodeVendor())
                    ->disabled()
                    ->dehydrated()
                    ->required(),

                    TextInput::make('nama_vendor')
                        ->label('Nama Vendor')
                        ->required(),

                    TextInput::make('alamat')
                        ->label('Alamat')
                        ->columnSpanFull(),

                    TextInput::make('no_telepon')
                       ->label('Nomor Telepon')
                       ->tel()
                       ->required()
                       ->maxLength(20)
                       ->placeholder('08xxxxxxxxxx'),

                    TextInput::make('email')
                        ->label('Email')
                        ->email(),

                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('Administrasi & Keuangan')
                ->schema([
                    Select::make('nama_bank')
                        ->label('Nama Bank')
                        ->options([
                            'BCA'        => 'BCA (Bank Central Asia)',
                            'BRI'        => 'BRI (Bank Rakyat Indonesia)',
                            'BNI'        => 'BNI (Bank Negara Indonesia)',
                            'Mandiri'   => 'Bank Mandiri',
                            'CIMB'      => 'CIMB Niaga',
                            'Danamon'   => 'Bank Danamon',
                            'Permata'   => 'Bank Permata',
                            'Maybank'   => 'Maybank Indonesia',
                            'OCBC'      => 'OCBC NISP',
                            'Panin'     => 'Bank Panin',
                            'BTN'       => 'BTN (Bank Tabungan Negara)',
                            'BTPN'      => 'BTPN / Jenius',
                            'Mega'      => 'Bank Mega',
                            'Sinarmas'  => 'Bank Sinarmas',
                            'Bukopin'   => 'Bank KB Bukopin',
                            'Muamalat'  => 'Bank Muamalat',
                            'BSI'       => 'Bank Syariah Indonesia (BSI)',
                        ])
                        ->searchable()
                        ->required(),

                    TextInput::make('nomor_rekening')
                        ->label('Nomor Rekening'),


                    Select::make('periode_pembayaran')
                        ->label('Periode Pembayaran')
                        ->options([
                            '1'  => '1 Bulan',
                            '3'  => '3 Bulan',
                            '6'  => '6 Bulan',
                            '9'  => '9 Bulan',
                            '12' => '12 Bulan',
                        ])
                        ->required(),

                   TextInput::make('diskon_persen')
                        ->label('Diskon (%)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->step(0.01)
                        ->suffix('%')
                        ->default(0)
                        ->required(),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ]);
    }
}
