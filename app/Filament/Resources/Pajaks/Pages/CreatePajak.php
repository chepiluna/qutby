<?php

namespace App\Filament\Resources\Pajaks\Pages;

use App\Filament\Resources\Pajaks\PajakResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreatePajak extends CreateRecord
{
    protected static string $resource = PajakResource::class;

    protected static ?string $title = 'Tambah Pajak';

    protected ?string $heading = 'Tambah Pajak';

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
