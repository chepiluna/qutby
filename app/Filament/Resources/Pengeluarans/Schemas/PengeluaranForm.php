<?php

namespace App\Filament\Resources\Pengeluarans\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PengeluaranForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode_pengeluaran')
                    ->required(),
                DatePicker::make('tanggal_pengeluaran')
                    ->required()
                    ->default(now()->toDateString()),
                Select::make('kategori_pengeluaran_id')
                    ->relationship('kategoriPengeluaran', 'id')
                    ->required(),
                TextInput::make('deskripsi')
                    ->default(null),
                TextInput::make('jumlah')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->options(['dibayar' => 'Dibayar', 'belum_dibayar' => 'Belum dibayar'])
                    ->default('belum_dibayar')
                    ->required(),
                TextInput::make('bukti_transaksi')
                    ->default(null),
            ]);
    }
}
