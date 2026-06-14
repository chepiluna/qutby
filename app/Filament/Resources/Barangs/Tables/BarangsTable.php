<?php

namespace App\Filament\Resources\Barangs\Tables;

use App\Models\Barang;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BarangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_barang')
                    ->label('Kode barang')
                    ->searchable(),

                TextColumn::make('nama_barang')
                    ->label('Nama barang')
                    ->searchable(),

                TextColumn::make('satuan')
                    ->label('Satuan')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, Barang $record): void {
                        if (! $record->hasTransactionHistory()) {
                            return;
                        }

                        Notification::make()
                            ->title('Barang tidak bisa dihapus')
                            ->body('Barang ini sudah dipakai di transaksi penjualan.')
                            ->warning()
                            ->send();

                        $action->cancel();
                    }),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
