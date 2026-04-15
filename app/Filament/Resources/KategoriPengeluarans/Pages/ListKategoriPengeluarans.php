<?php

namespace App\Filament\Resources\KategoriPengeluarans\Pages;

use App\Filament\Resources\KategoriPengeluarans\KategoriPengeluaranResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKategoriPengeluarans extends ListRecords
{
    protected static string $resource = KategoriPengeluaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Tambah Kategori Pengeluaran'),
        ];
    }
}
