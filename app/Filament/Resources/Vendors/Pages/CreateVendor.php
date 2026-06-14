<?php

namespace App\Filament\Resources\Vendors\Pages;

//use App\Filament\Traits\HasBackButtonHeading;

use App\Filament\Resources\Vendors\VendorResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;

class CreateVendor extends CreateRecord
{
    //use HasBackButtonHeading;


    protected static string $resource = VendorResource::class;

    /**
     * Judul halaman
     */
    public function getTitle(): string
    {
        return 'Tambah Vendor';
    }

    public function getBreadcrumb(): string
    {
        return 'Tambah Vendor';
    }

    public function getHeading(): HtmlString
    {
        return $this->backHeading('Tambah Vendor');
    }

    protected function backHeading(string $label): HtmlString
    {
        $url = e($this->getResource()::getUrl('index'));
        $label = e($label);

        return new HtmlString(<<<HTML
            <span style="display:inline-flex; align-items:center; gap:14px;">
                <a href="{$url}" aria-label="Kembali ke daftar vendor" style="display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:12px; background:#ffffff; border:1px solid #e5e7eb; color:#991b1b; text-decoration:none; box-shadow:0 8px 18px rgba(15,23,42,.08);">
                    <span style="font-size:24px; font-weight:900; line-height:1; transform:translateY(-1px);">&lsaquo;</span>
                </a>
                <span style="line-height:1.1;">{$label}</span>
            </span>
        HTML);
    }

    /**
     * Action form (Filament v4)
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan')
                ->submit('create'),

            Action::make('cancel')
                ->label('Batal')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
}
