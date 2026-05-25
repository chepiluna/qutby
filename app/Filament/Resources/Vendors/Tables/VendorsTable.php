<?php

namespace App\Filament\Resources\Vendors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Filament\Resources\Vendors\VendorResource;


class VendorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_vendor')
                    ->label('Kode Vendor'),

                TextColumn::make('nama_vendor')
                    ->label('Nama Vendor')
                    ->searchable(),

                TextColumn::make('no_telepon')
                    ->label('Nomor Telepon')
                    ->searchable(),

                TextColumn::make('alamat')
                    ->label('Alamat')
                    ->limit(30)
                    ->searchable(),
            ])
            ->recordUrl(
                fn ($record) => VendorResource::getUrl('view', ['record' => $record])
            )
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat'),   
                EditAction::make()
                    ->label('Edit'),
                DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Hapus Vendor')
                    ->modalDescription('Apakah Anda yakin ingin menghapus data vendor ini?')
                    ->modalSubmitActionLabel('Ya, hapus')
                    ->modalCancelActionLabel('Batal'),
            ])
            ->toolbarActions([
            ]);
    }
}
