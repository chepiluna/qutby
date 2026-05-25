<?php

namespace App\Filament\Resources\PembayaranPembelians\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;

class PembayaranPembeliansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('nomor_pembayaran_vendor')
                    ->label('No Pembayaran')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tanggal_jatuh_tempo')
                    ->label('Tanggal Pembayaran')
                    ->date()
                    ->sortable(),

                TextColumn::make('vendor.nama_vendor')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR', true)
                    ->sortable(),

                TextColumn::make('status_pembayaran')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'belum_lunas' => 'danger',
                        'lunas'       => 'success',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'belum_lunas' => 'Belum Lunas',
                        'lunas'       => 'Lunas',
                        default       => ucfirst($state),
                    })
                    ->sortable(),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(30)
                    ->wrap(),
            ])
            ->filters([
                SelectFilter::make('status_pembayaran')
                    ->label('Status Pembayaran')
                    ->options([
                        'belum_lunas' => 'Belum Lunas',
                        'lunas'       => 'Lunas',
                    ]),
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
