<?php

namespace App\Filament\Resources\Pembayarans\Pages;

use App\Filament\Resources\Pembayarans\PembayaranResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPembayarans extends ListRecords
{
    protected static string $resource = PembayaranResource::class;

    public function getTitle(): string
    {
        return 'Daftar Pembayaran Pelanggan';
    }

    public function getHeading(): string
    {
        return 'Daftar Pembayaran Pelanggan';
    }

    public function getBreadcrumb(): string
    {
        return 'Daftar Pembayaran Pelanggan';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
