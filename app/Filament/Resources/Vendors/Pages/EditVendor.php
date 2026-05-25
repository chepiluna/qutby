<?php

namespace App\Filament\Resources\Vendors\Pages;

//use App\Filament\Traits\HasBackButtonHeading;

use App\Filament\Resources\Vendors\VendorResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;

class EditVendor extends EditRecord
{
    //use HasBackButtonHeading;


    protected static string $resource = VendorResource::class;

    /**
     * 🔹 Redirect setelah Simpan
     */
    protected function getRedirectUrl(): string
    {
        return VendorResource::getUrl('index');
    }

    /**
     * 🔹 Ubah teks notifikasi
     */
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Berhasil disimpan';
    }

    /**
     * 🔹 Tombol form
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('simpan')
                ->label('Simpan')
                ->color('primary')
                ->submit('save'),

            Action::make('batal')
                ->label('Batal')
                ->color('gray')
                ->url(VendorResource::getUrl('index')),
        ];
    }

    /**
     * 🔹 Hilangkan header actions
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
