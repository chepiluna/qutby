<?php

namespace App\Filament\Resources\DaftarPiutangs\Pages;

use App\Filament\Resources\DaftarPiutangs\DaftarPiutangResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDaftarPiutang extends EditRecord
{
    protected static string $resource = DaftarPiutangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
