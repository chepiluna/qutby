<?php

namespace App\Filament\Resources\Pembelian\Pages;

//use App\Filament\Traits\HasBackButtonHeading;

use App\Filament\Resources\Pembelian\PembelianResource;
use Filament\Resources\Pages\EditRecord;

class EditPembelian extends EditRecord
{
    //use HasBackButtonHeading;


    protected static string $resource = PembelianResource::class;

    /**
     * ❌ Hilangkan action View & Delete di header
     */
    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function afterValidate(): void
    {
        PembelianResource::validateTerminState($this->form->getRawState());
    }

    /**
     * ✅ SETELAH KLIK SIMPAN → KEMBALI KE DAFTAR PESANAN PEMBELIAN
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
