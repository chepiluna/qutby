<?php

namespace App\Filament\Resources\TerminPembayarans\Pages;

use App\Filament\Resources\TerminPembayarans\TerminPembayaranResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateTerminPembayaran extends CreateRecord
{
    protected static string $resource = TerminPembayaranResource::class;

    protected static ?string $title = 'Tambah Termin Pembayaran';

    protected ?string $heading = 'Tambah Termin Pembayaran';

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
