<?php

namespace App\Filament\Resources\Penjualans\Pages;

use App\Filament\Resources\Penjualans\PenjualanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPenjualan extends EditRecord
{
    protected static string $resource = PenjualanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    // Hitung ulang total sebelum update ke DB
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $totalBruto = 0;

        foreach ($data['detail'] ?? [] as $row) {
            $totalBruto += (float) ($row['subtotal'] ?? 0);
        }

        $diskonPersen = (float) ($data['diskon_persen'] ?? 0);
        $diskonRp     = $totalBruto * $diskonPersen / 100;
        $totalNetto   = $totalBruto - $diskonRp;

        $data['total_bruto'] = $totalBruto;
        $data['diskon_rp']   = $diskonRp;
        $data['total_netto'] = $totalNetto;

        return $data;
    }
}
