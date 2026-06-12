<?php

namespace App\Filament\Resources\PenerimaanBarangResource\Pages;

use App\Filament\Resources\PenerimaanBarangResource;
use Filament\Resources\Pages\ListRecords;

class ListPenerimaanBarang extends ListRecords
{
    protected static string $resource = PenerimaanBarangResource::class;

    public function getTitle(): string
    {
        return 'Daftar Penerimaan Barang';
    }

    public function getHeading(): string
    {
        return 'Daftar Penerimaan Barang';
    }

    public function getBreadcrumb(): string
    {
        return 'Daftar Penerimaan Barang';
    }

    public function getPageClasses(): array
    {
        return [
            'qutrix-penerimaan-barang-list-page',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()->label('Buat Penerimaan Barang'),
        ];
    }
}
