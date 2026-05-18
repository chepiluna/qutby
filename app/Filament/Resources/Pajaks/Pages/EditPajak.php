<?php

namespace App\Filament\Resources\Pajaks\Pages;

use App\Filament\Resources\Pajaks\PajakResource;
use Filament\Resources\Pages\EditRecord;

class EditPajak extends EditRecord
{
    protected static string $resource = PajakResource::class;

    // ❌ HAPUS tombol Delete
    protected function getHeaderActions(): array
    {
        return [];
    }
}