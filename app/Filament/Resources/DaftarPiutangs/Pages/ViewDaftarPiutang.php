<?php

namespace App\Filament\Resources\DaftarPiutangs\Pages;

use App\Filament\Resources\DaftarPiutangs\DaftarPiutangResource;
use Filament\Resources\Pages\ViewRecord;

class ViewDaftarPiutang extends ViewRecord
{
    protected static string $resource = DaftarPiutangResource::class;

    protected string $view = 'filament.resources.daftar-piutang.view';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
