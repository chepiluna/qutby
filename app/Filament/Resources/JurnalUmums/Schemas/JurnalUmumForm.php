<?php

namespace App\Filament\Resources\JurnalUmums\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class JurnalUmumForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Jurnal')
                ->schema([
                    DatePicker::make('tanggal')
                        ->label('Tanggal')
                        ->required(),

                    TextInput::make('kode_jurnal')
                        ->label('Kode Jurnal')
                        ->required()
                        ->maxLength(50),

                    Textarea::make('deskripsi')
                        ->label('Deskripsi')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(), // biar section-nya full lebar
        ]);
    }
}
