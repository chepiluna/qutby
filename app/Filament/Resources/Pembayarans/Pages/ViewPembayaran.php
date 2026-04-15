<?php

namespace App\Filament\Resources\Pembayarans\Pages;

use App\Filament\Resources\Pembayarans\PembayaranResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPembayaran extends ViewRecord
{
    protected static string $resource = PembayaranResource::class;
    public function getTitle(): string
    {
        $noFaktur = $this->record->penjualan?->no_faktur 
                 ?? $this->record->piutang?->no_faktur 
                 ?? $this->record->id;
        
        return "Pembayaran {$noFaktur}";
    }
}
