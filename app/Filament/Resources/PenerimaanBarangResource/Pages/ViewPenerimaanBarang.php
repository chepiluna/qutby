<?php

namespace App\Filament\Resources\PenerimaanBarangResource\Pages;

use App\Filament\Resources\PenerimaanBarangResource;
//use App\Filament\Traits\HasBackButtonHeading;
use App\Models\PenerimaanBarang;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewPenerimaanBarang extends ViewRecord
{
    //use HasBackButtonHeading;

    protected static string $resource = PenerimaanBarangResource::class;

    public function getTitle(): string
    {
        /** @var PenerimaanBarang $record */
        $record = $this->getRecord();

        return 'Detail Penerimaan Barang: ' . $record->nomor_grn;
    }

    public function getRecord(): Model
    {
        return parent::getRecord()->load([
            'pembelian.details.barang',
            'vendor',
            'details.barang',
            'details.pembelianDetail',
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
