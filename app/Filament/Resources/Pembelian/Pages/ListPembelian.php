<?php

namespace App\Filament\Resources\Pembelian\Pages;

use App\Filament\Resources\Pembelian\PembelianResource;
use App\Filament\Resources\Pembelian\Widgets\LaporanPembelianFilter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPembelian extends ListRecords
{
    protected static string $resource = PembelianResource::class;

    public function getBreadcrumb(): string
    {
        return 'Daftar Pesanan Pembelian';
    }

    public function getTitle(): string
    {
        return 'Daftar Pesanan Pembelian';
    }

    public function getHeading(): string
    {
        return 'Daftar Pesanan Pembelian';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Pesanan'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            LaporanPembelianFilter::class,
        ];
    }
}
