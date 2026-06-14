<?php

namespace App\Filament\Resources\Pelanggans\Pages;

use App\Filament\Resources\Pelanggans\PelangganResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\HtmlString;

class CreatePelanggan extends CreateRecord
{
    protected static string $resource = PelangganResource::class;

    protected static ?string $title = 'Tambah Pelanggan';

    protected ?string $heading = 'Tambah Pelanggan';

    public function getBreadcrumb(): string
    {
        return 'Tambah Pelanggan';
    }

    public function getHeading(): HtmlString
    {
        $url = e($this->getResource()::getUrl('index'));

        return new HtmlString(<<<HTML
            <span style="display:inline-flex; align-items:center; gap:14px;">
                <a href="{$url}" aria-label="Kembali ke daftar pelanggan" style="display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:12px; background:#ffffff; border:1px solid #e5e7eb; color:#991b1b; text-decoration:none; box-shadow:0 8px 18px rgba(15,23,42,.08);">
                    <span style="font-size:24px; font-weight:900; line-height:1; transform:translateY(-1px);">&lsaquo;</span>
                </a>
                <span style="line-height:1.1;">Tambah Pelanggan</span>
            </span>
        HTML);
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Simpan');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->hidden();
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
