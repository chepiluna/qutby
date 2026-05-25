<?php

namespace App\Filament\Resources\PembayaranVendors\Pages;

use App\Filament\Resources\PembayaranVendors\PembayaranVendorResource;

use Filament\Resources\Pages\EditRecord;

class EditPembayaranVendor extends EditRecord
{
    protected static string $resource = PembayaranVendorResource::class;

    public function getTitle(): string
    {
        return 'Bayar Faktur ' .
            ($this->record->piutang?->no_faktur ?? '');
    }

    protected function afterSave(): void
    {
        $this->record->update([
            'keterangan' => 'lunas',
        ]);

        if ($this->record->piutang) {

            $this->record->piutang->update([
                'status'         => 'lunas',
                'sisa_piutang'   => 0,
            ]);
        }
    }
}