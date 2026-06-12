<?php

namespace App\Filament\Resources\Pelanggans\Pages;

use App\Filament\Resources\Pelanggans\PelangganResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPelanggans extends ListRecords
{
    protected static string $resource = PelangganResource::class;

    protected static ?string $title = 'Daftar Pelanggan';

    protected static ?string $breadcrumb = 'Daftar Pelanggan';

    protected ?string $heading = 'Daftar Pelanggan';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Pelanggan'),
        ];
    }
}
