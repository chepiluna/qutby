<?php

namespace App\Filament\Resources\Vendors\Pages;

//use App\Filament\Traits\HasBackButtonHeading;
use App\Filament\Resources\Vendors\VendorResource;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\HtmlString;

class ViewVendor extends ViewRecord
{
    //use HasBackButtonHeading;

    protected static string $resource = VendorResource::class;

    public function getTitle(): string
    {
        return 'Lihat ' . $this->record->nama_vendor;
    }

    public function getHeading(): HtmlString
    {
        $url = e($this->getResource()::getUrl('index'));
        $label = e('Lihat ' . $this->record->nama_vendor);

        return new HtmlString(<<<HTML
            <span style="display:inline-flex; align-items:center; gap:14px;">
                <a href="{$url}" aria-label="Kembali ke daftar vendor" style="display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:12px; background:#ffffff; border:1px solid #e5e7eb; color:#991b1b; text-decoration:none; box-shadow:0 8px 18px rgba(15,23,42,.08);">
                    <span style="font-size:24px; font-weight:900; line-height:1; transform:translateY(-1px);">&lsaquo;</span>
                </a>
                <span style="line-height:1.1;">{$label}</span>
            </span>
        HTML);
    }
}
