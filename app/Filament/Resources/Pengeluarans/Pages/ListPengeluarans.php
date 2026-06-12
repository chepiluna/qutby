<?php

namespace App\Filament\Resources\Pengeluarans\Pages;

use App\Filament\Resources\Pengeluarans\PengeluaranResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPengeluarans extends ListRecords
{
    protected static string $resource = PengeluaranResource::class;

    public function getTitle(): string
    {
        return 'Daftar Pengeluaran';
    }

    public function getHeading(): string
    {
        return 'Daftar Pengeluaran';
    }

    public function getBreadcrumb(): string
    {
        return 'Daftar Pengeluaran';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Pengeluaran'), // ← tambahin ini
        ];
    }
}
