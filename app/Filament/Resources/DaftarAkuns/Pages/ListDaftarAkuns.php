<?php

namespace App\Filament\Resources\DaftarAkuns\Pages;

use App\Filament\Resources\DaftarAkuns\DaftarAkunResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDaftarAkuns extends ListRecords
{
    protected static string $resource = DaftarAkunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Daftar Akun'),
        ];
    }
}
