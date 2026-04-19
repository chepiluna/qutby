<?php

namespace App\Filament\Resources\DaftarAkuns\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DaftarAkunForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Akun')
                ->schema([
                    TextInput::make('kode_akun')
                        ->label('Kode Akun')
                        ->required()
                        ->maxLength(10),

                    TextInput::make('nama_akun')
                        ->label('Nama Akun')
                        ->required()
                        ->maxLength(255),

                    Select::make('header_akun')
                        ->label('Header Akun')
                        ->options([
                            1 => 'Aset',
                            2 => 'Utang',
                            3 => 'Modal',
                            4 => 'Pendapatan',
                            5 => 'Beban',
                        ])
                        ->required(),
                ])
                ->columns(2)
                ->columnSpanFull(),

            /*Section::make('Relasi Akun')
                ->schema([
                    Select::make('parent_id')
                        ->label('Akun Induk')
                        ->relationship(
                            name: 'parent',
                            titleAttribute: 'nama_akun',
                            modifyQueryUsing: fn ($query) =>
                                $query->whereRaw('LENGTH(kode_akun) = 2')
                        )
                        ->preload()
                        ->searchable(),

                    Select::make('saldo_normal')
                        ->label('Saldo Normal')
                        ->options([
                            'debit'  => 'Debit',
                            'kredit' => 'Kredit',
                        ])
                        ->required(),
                ])
                ->columns(2)
                ->columnSpanFull(),*/
        ]);
    }
}
