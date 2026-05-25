<?php

namespace App\Filament\Resources\PembayaranPembelians\Pages;

//use App\Filament\Traits\HasBackButtonHeading;

use App\Filament\Resources\PembayaranPembelians\PembayaranPembelianResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPembayaranPembelian extends EditRecord
{
    //use HasBackButtonHeading;


    protected static string $resource = PembayaranPembelianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
