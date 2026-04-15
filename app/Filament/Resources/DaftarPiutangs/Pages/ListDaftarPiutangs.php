<?php

namespace App\Filament\Resources\DaftarPiutangs\Pages;

use App\Filament\Resources\DaftarPiutangs\DaftarPiutangResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDaftarPiutangs extends ListRecords
{
    protected static string $resource = DaftarPiutangResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

}
