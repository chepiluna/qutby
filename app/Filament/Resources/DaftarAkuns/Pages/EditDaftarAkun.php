<?php

namespace App\Filament\Resources\DaftarAkuns\Pages;

use App\Filament\Resources\DaftarAkuns\DaftarAkunResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDaftarAkun extends EditRecord
{
    protected static string $resource = DaftarAkunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
