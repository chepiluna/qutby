<?php

namespace App\Filament\Resources\Vendors\Pages;

//use App\Filament\Traits\HasBackButtonHeading;
use App\Filament\Resources\Vendors\VendorResource;
use Filament\Resources\Pages\ViewRecord;

class ViewVendor extends ViewRecord
{
    //use HasBackButtonHeading;

    protected static string $resource = VendorResource::class;

    public function getTitle(): string
    {
        return 'Lihat ' . $this->record->nama_vendor;
    }
}
