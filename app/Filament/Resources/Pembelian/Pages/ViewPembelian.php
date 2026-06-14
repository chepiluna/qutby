<?php

namespace App\Filament\Resources\Pembelian\Pages;

//use App\Filament\Traits\HasBackButtonHeading;

use App\Filament\Resources\Pembelian\PembelianResource;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class ViewPembelian extends ViewRecord
{
    //use HasBackButtonHeading;


    protected static string $resource = PembelianResource::class;

    public function getTitle(): string
    {
        return 'Detail Pembelian Barang';
    }

    public function getHeading(): HtmlString
    {
        $url = e($this->getResource()::getUrl('index'));

        return new HtmlString(<<<HTML
            <span style="display:inline-flex; align-items:center; gap:14px;">
                <a href="{$url}" aria-label="Kembali ke daftar pesanan pembelian" style="display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:12px; background:#ffffff; border:1px solid #e5e7eb; color:#991b1b; text-decoration:none; box-shadow:0 8px 18px rgba(15,23,42,.08);">
                    <span style="font-size:24px; font-weight:900; line-height:1; transform:translateY(-1px);">&lsaquo;</span>
                </a>
                <span style="line-height:1.1;">Detail Pembelian Barang</span>
            </span>
        HTML);
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
        return [];
    }
}
