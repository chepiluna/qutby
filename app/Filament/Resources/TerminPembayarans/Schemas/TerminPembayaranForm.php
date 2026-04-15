<?php

namespace App\Filament\Resources\TerminPembayarans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TerminPembayaranForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Termin Pembayaran')
                ->schema([
                    TextInput::make('kode')
                        ->label('Kode')
                        ->required()
                        ->maxLength(50),

                    TextInput::make('nama')
                        ->label('Nama')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('diskon_persen')
                        ->label('% Diskon')
                        ->numeric(),

                    TextInput::make('hari_diskon')
                        ->label('Hari diskon')
                        ->numeric(),

                    TextInput::make('hari_jatuh_tempo')
                        ->label('Hari jatuh tempo')
                        ->numeric()
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ]);
    }
}
