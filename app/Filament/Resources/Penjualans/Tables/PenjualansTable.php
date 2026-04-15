<?php

namespace App\Filament\Resources\Penjualans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class PenjualansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal_faktur')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                TextColumn::make('no_faktur')
                    ->label('No. Faktur')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pelanggan.nama_pelanggan')
                    ->label('Pelanggan')
                    ->searchable(),
                TextColumn::make('termin.nama')
                    ->label('Termin')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_netto')
                    ->label('Total')
                    ->formatStateUsing(
                        fn ($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.')
                    ),
                TextColumn::make('cara_bayar')
                    ->label('Cara bayar')
                    ->badge(),

                
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
