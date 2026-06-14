<?php

namespace App\Filament\Resources\Barangs\Pages;

use App\Filament\Resources\Barangs\BarangResource;
use App\Models\Barang;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditBarang extends EditRecord
{
    protected static string $resource = BarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (DeleteAction $action, Barang $record): void {
                    if (! $record->hasTransactionHistory()) {
                        return;
                    }

                    Notification::make()
                        ->title('Barang tidak bisa dihapus')
                        ->body('Barang ini sudah dipakai di transaksi penjualan.')
                        ->warning()
                        ->send();

                    $action->cancel();
                }),
        ];
    }
}
