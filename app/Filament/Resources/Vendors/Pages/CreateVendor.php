<?php

namespace App\Filament\Resources\Vendors\Pages;

//use App\Filament\Traits\HasBackButtonHeading;

use App\Filament\Resources\Vendors\VendorResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;

class CreateVendor extends CreateRecord
{
    //use HasBackButtonHeading;


    protected static string $resource = VendorResource::class;

    /**
     * Judul halaman
     */
    public function getTitle(): string
    {
        return 'Tambah Vendor';
    }

    public function getBreadcrumb(): string
    {
        return 'Tambah Vendor';
    }

    /**
     * Action form (Filament v4)
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan')
                ->submit('create'),

            Action::make('cancel')
                ->label('Batal')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
}
