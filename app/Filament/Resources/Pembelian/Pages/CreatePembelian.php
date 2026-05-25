<?php

namespace App\Filament\Resources\Pembelian\Pages;

//use App\Filament\Traits\HasBackButtonHeading;

use App\Filament\Resources\Pembelian\PembelianResource;
use App\Models\Pembelian;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;

class CreatePembelian extends CreateRecord
{
    //use HasBackButtonHeading;

    protected static string $resource = PembelianResource::class;

    public function getTitle(): string
    {
        return 'Pesanan Pembelian';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['nomor'] = Pembelian::generateNomorPembelian();
        $data['status'] = 'menunggu';

        return $data;
    }

    protected function afterValidate(): void
    {
        PembelianResource::validateTerminState($this->form->getRawState());
    }

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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}