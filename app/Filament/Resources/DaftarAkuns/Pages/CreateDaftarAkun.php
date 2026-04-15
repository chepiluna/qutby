<?php

namespace App\Filament\Resources\DaftarAkuns\Pages;

use App\Filament\Resources\DaftarAkuns\DaftarAkunResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateDaftarAkun extends CreateRecord
{
    protected static string $resource = DaftarAkunResource::class;

    protected static ?string $title = 'Tambah Akun';

    protected ?string $heading = 'Tambah Akun';

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
