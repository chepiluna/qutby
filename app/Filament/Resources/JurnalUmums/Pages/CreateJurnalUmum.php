<?php

namespace App\Filament\Resources\JurnalUmums\Pages;

use App\Filament\Resources\JurnalUmums\JurnalUmumResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateJurnalUmum extends CreateRecord
{
    protected static string $resource = JurnalUmumResource::class;

    protected static ?string $title = 'Tambah Jurnal Umum';

    protected ?string $heading = 'Tambah Jurnal Umum';

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
