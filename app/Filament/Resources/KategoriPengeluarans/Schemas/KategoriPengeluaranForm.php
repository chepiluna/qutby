<?php

namespace App\Filament\Resources\KategoriPengeluarans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KategoriPengeluaranForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->required(),
                TextInput::make('deskripsi')
                    ->default(null),
            ]);
    }
}
