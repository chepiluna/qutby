<?php

namespace App\Filament\Resources\DaftarPiutangs\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;

class DaftarPiutangInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pelanggan')
                    ->schema([
                        TextEntry::make('kode_pelanggan')
                            ->label('Kode Pelanggan'),

                        TextEntry::make('nama_pelanggan')
                            ->label('Nama Pelanggan'),

                        TextEntry::make('alamat')
                            ->label('Alamat'),

                        TextEntry::make('no_telp')
                            ->label('No. Telp'),

                        TextEntry::make('total_piutang')
                            ->label('Total Piutang')
                            ->money('IDR'),

                        TextEntry::make('sisa_piutang')
                            ->label('Sisa Piutang')
                            ->money('IDR'),
                    ])
                    ->columns(2),
            ]);
    }
}
