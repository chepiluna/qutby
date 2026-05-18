<?php

namespace App\Filament\Resources\Pajaks\Pages;

use App\Filament\Resources\Pajaks\PajakResource;
use Filament\Resources\Pages\ListRecords;

class ListPajaks extends ListRecords
{
    protected static string $resource = PajakResource::class;

    // ❌ HAPUS tombol Create (Tambah Pajak)
    protected function getHeaderActions(): array
    {
        return [];
    }
}