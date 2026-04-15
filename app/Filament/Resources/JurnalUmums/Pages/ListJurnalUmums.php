<?php

namespace App\Filament\Resources\JurnalUmums\Pages;

use App\Filament\Resources\JurnalUmums\JurnalUmumResource;
use App\Filament\Resources\JurnalUmums\Pages\LaporanJurnalUmum; // <-- ganti ini
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJurnalUmums extends ListRecords
{
    protected static string $resource = JurnalUmumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('lihat_laporan')
                ->label('Lihat Laporan')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->url(LaporanJurnalUmum::getUrl(panel: 'finance')),

            CreateAction::make()
                ->label('Tambah Jurnal Umum'),
        ];
    }
}
