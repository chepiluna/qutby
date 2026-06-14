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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['syarat_pembayaran'] ?? null) === 'tunai') {
            if (filled($data['vendor_manual'] ?? null)) {
                $data['vendor_id'] = null;
                $data['vendor_manual'] = trim((string) $data['vendor_manual']);
            } else {
                $data['vendor_manual'] = null;
            }
        } else {
            $data['vendor_manual'] = null;
        }

        return $data;
    }

    /**
     * ✅ SETELAH KLIK SIMPAN → KEMBALI KE DAFTAR PESANAN PEMBELIAN
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
