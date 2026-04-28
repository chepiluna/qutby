<?php

namespace App\Filament\Resources\Pembayarans\Pages;

use App\Filament\Resources\Pembayarans\PembayaranResource;
use Filament\Resources\Pages\EditRecord;

class EditPembayaran extends EditRecord
{
    protected static string $resource = PembayaranResource::class;

    public function getTitle(): string
    {
        return 'Bayar Faktur ' . ($this->record->piutang?->no_faktur ?? '');
    }

    protected function afterSave(): void
    {
        $this->record->update([
            'keterangan' => 'lunas',
        ]);

        if ($this->record->piutang) {
            $this->record->piutang->update([
                'status' => 'lunas',
                'sisa_piutang' => 0,
            ]);
        }
    }
}