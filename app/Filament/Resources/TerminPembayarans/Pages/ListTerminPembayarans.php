<?php

namespace App\Filament\Resources\TerminPembayarans\Pages;

use App\Filament\Resources\TerminPembayarans\TerminPembayaranResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTerminPembayarans extends ListRecords
{
    protected static string $resource = TerminPembayaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Termin Pembayaran'),
        ];
    }
}
