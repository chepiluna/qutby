<?php

namespace App\Filament\Resources\Pajaks\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PajakForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Pajak')
                ->schema([
                    TextInput::make('kode')
                        ->label('Kode')
                        ->required()
                        ->maxLength(20),

                    TextInput::make('nama')
                        ->label('Nama Pajak')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('persen')
                        ->label('Persen (%)')
                        ->numeric()
                        ->required(),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ]);
    }
}
