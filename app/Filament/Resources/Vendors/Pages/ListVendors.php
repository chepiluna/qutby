<?php

namespace App\Filament\Resources\Vendors\Pages;

use App\Filament\Resources\Vendors\VendorResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListVendors extends ListRecords
{
    protected static string $resource = VendorResource::class;

    public function getTitle(): string
    {
        return 'Daftar Vendor';
    }

    public function getHeading(): string
    {
        return 'Daftar Vendor';
    }

    public function getBreadcrumb(): string
    {
        return 'Daftar Vendor';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Vendor'),
        ];
    }
}
