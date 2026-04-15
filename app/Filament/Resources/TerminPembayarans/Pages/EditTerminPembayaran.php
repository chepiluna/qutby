<?php

namespace App\Filament\Resources\TerminPembayarans\Pages;

use App\Filament\Resources\TerminPembayarans\TerminPembayaranResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTerminPembayaran extends EditRecord
{
    protected static string $resource = TerminPembayaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
