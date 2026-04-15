<?php

namespace App\Filament\Resources\KategoriPengeluarans\Pages;

use App\Filament\Resources\KategoriPengeluarans\KategoriPengeluaranResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKategoriPengeluaran extends EditRecord
{
    protected static string $resource = KategoriPengeluaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
