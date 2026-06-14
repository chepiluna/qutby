<?php

namespace App\Filament\Resources\Penjualans\Pages;

use App\Filament\Resources\Penjualans\PenjualanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPenjualans extends ListRecords
{
    protected static string $resource = PenjualanResource::class;

    public function getTitle(): string
    {
        return 'Daftar Penjualan';
    }

    public function getHeading(): string
    {
        return 'Daftar Penjualan';
    }

    public function getBreadcrumb(): string
    {
        return 'Daftar Penjualan';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Penjualan'), 
        ];
    }
}
