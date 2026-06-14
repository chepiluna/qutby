<?php

namespace App\Filament\Resources\Vendors\Pages;

//use App\Filament\Traits\HasBackButtonHeading;

use App\Filament\Resources\Vendors\VendorResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;

class EditVendor extends EditRecord
{
    //use HasBackButtonHeading;


    protected static string $resource = VendorResource::class;

    public function getHeading(): HtmlString
    {
        return $this->backHeading('Ubah ' . $this->record->nama_vendor);
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
     * 🔹 Redirect setelah Simpan
     */
    protected function getRedirectUrl(): string
    {
        return VendorResource::getUrl('index');
    }

    /**
     * 🔹 Ubah teks notifikasi
     */
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Berhasil disimpan';
    }

    /**
     * 🔹 Tombol form
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('simpan')
                ->label('Simpan')
                ->color('primary')
                ->submit('save'),

            Action::make('batal')
                ->label('Batal')
                ->color('gray')
                ->url(VendorResource::getUrl('index')),
        ];
    }

    /**
     * 🔹 Hilangkan header actions
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
