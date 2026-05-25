<?php

namespace App\Filament\Resources\PembayaranVendors\Pages;

use App\Filament\Resources\PembayaranVendors\PembayaranVendorResource;

use Filament\Resources\Pages\ListRecords;

class ListPembayaranVendors extends ListRecords
{
    protected static string $resource = PembayaranVendorResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}