<?php

namespace App\Filament\Resources\Barangs\Pages;

use App\Filament\Resources\Barangs\BarangResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBarangs extends ListRecords
{
    protected static string $resource = BarangResource::class;

    protected static ?string $title = 'Daftar Barang';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Barang'), // ← tambahin ini
        ];
    }
}
