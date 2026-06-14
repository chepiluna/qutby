<?php

namespace App\Filament\Resources\PenerimaanBarangResource\Pages;

use App\Filament\Resources\PenerimaanBarangResource;
//use App\Filament\Traits\HasBackButtonHeading;
use App\Models\PenerimaanBarang;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class ViewPenerimaanBarang extends ViewRecord
{
    //use HasBackButtonHeading;

    protected static string $resource = PenerimaanBarangResource::class;

    public function getTitle(): string
    {
        /** @var PenerimaanBarang $record */
        $record = $this->getRecord();

        return 'Detail Penerimaan Barang: ' . $record->id_penerimaan;
    }

    public function getHeading(): HtmlString
    {
        /** @var PenerimaanBarang $record */
        $record = $this->getRecord();
        $url = e($this->getResource()::getUrl('index'));
        $label = e('Detail Penerimaan Barang: ' . $record->id_penerimaan);

        return new HtmlString(<<<HTML
            <span style="display:inline-flex; align-items:center; gap:14px;">
                <a href="{$url}" aria-label="Kembali ke daftar penerimaan barang" style="display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:12px; background:#ffffff; border:1px solid #e5e7eb; color:#991b1b; text-decoration:none; box-shadow:0 8px 18px rgba(15,23,42,.08);">
                    <span style="font-size:24px; font-weight:900; line-height:1; transform:translateY(-1px);">&lsaquo;</span>
                </a>
                <span style="line-height:1.1;">{$label}</span>
            </span>
        HTML);
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
