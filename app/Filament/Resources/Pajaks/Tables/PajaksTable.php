<?php

namespace App\Filament\Resources\Pajaks\Tables;

use App\Models\Pajak;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PajaksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Pajak::query())
            ->columns([
                TextColumn::make('kode')
                    ->label('Kode')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nama')
                    ->label('Nama Pajak')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('persen')
                    ->label('Persen (%)')
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);

    }
}
