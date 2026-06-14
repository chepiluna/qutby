<?php

namespace App\Filament\Resources\PembayaranPembelians\Pages;

use App\Filament\Resources\PembayaranPembelians\PembayaranPembelianResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPembayaranPembelians extends ListRecords
{
    protected static string $resource = PembayaranPembelianResource::class;

    public function getTitle(): string
    {
        return 'Daftar Pembayaran Utang';
    }

    public function getHeading(): string
    {
        return 'Daftar Pembayaran Utang';
    }

    public function getBreadcrumb(): string
    {
        return 'Daftar Pembayaran Utang';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Buat Pembayaran'),
        ];
    }
}
