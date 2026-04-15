<?php

namespace App\Filament\Resources\KategoriPengeluarans\Pages;

use App\Filament\Resources\KategoriPengeluarans\KategoriPengeluaranResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateKategoriPengeluaran extends CreateRecord
{
    protected static string $resource = KategoriPengeluaranResource::class;

    protected static ?string $title = 'Tambah Kategori Pengeluaran';

    protected ?string $heading = 'Tambah Kategori Pengeluaran';

    public function getBreadcrumb(): string
    {
        return 'Tambah';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Tambah');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Tambah & tambah lainnya');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }
}
