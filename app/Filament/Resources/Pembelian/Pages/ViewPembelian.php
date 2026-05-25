<?php

namespace App\Filament\Resources\Pembelian\Pages;

//use App\Filament\Traits\HasBackButtonHeading;

use App\Filament\Resources\Pembelian\PembelianResource;
use App\Filament\Resources\PembayaranPembelians\PembayaranPembelianResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewPembelian extends ViewRecord
{
    //use HasBackButtonHeading;


    protected static string $resource = PembelianResource::class;

    public function getTitle(): string
    {
        return 'Detail Pembelian Barang';
    }


    /**
     * ✅ HARUS public (Filament memanggil ini)
     */
    public function getRecord(): Model
    {
        return parent::getRecord()->load([
            'vendor',
            'details.barang',

            // ✅ untuk alur terintegrasi (step)
            'pembayaranPembelian',
        ]);
    }

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        return [

            // ✅ PROSES PEMBAYARAN
            Action::make('proses_pembayaran')
                ->label('Proses Pembayaran')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->visible(fn (): bool => $record->status === 'selesai')
                ->url(fn (): string =>
                    PembayaranPembelianResource::getUrl('create') . '?pembelian_id=' . $record->id
                ),
        ];
    }
}
