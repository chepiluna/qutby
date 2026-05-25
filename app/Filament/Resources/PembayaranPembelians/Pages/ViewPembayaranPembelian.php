<?php

namespace App\Filament\Resources\PembayaranPembelians\Pages;

//use App\Filament\Traits\HasBackButtonHeading;

use App\Filament\Resources\PembayaranPembelians\PembayaranPembelianResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPembayaranPembelian extends ViewRecord
{
    //use HasBackButtonHeading;


    protected static string $resource = PembayaranPembelianResource::class;

    public function getTitle(): string
    {
        return 'Detail Pembayaran Utang';
    }

}
