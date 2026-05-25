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

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()->label('Buat Penerimaan Barang'),
        ];
    }
}
