<?php

namespace App\Filament\Resources\Pengeluarans\Tables;

use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PengeluaransTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_pengeluaran')->label('Kode')->searchable(),
                TextColumn::make('tanggal_pengeluaran')->date()->sortable(),
                TextColumn::make('kategoriPengeluaran.nama')->label('Kategori'),
                TextColumn::make('deskripsi'),
                TextColumn::make('jumlah')->numeric()->sortable(),
            ])
            ->recordActions([
                //EditAction::make(),
                ViewAction::make(),
            ]);
    }
}
