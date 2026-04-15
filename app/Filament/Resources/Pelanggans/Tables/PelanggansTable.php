<?php

namespace App\Filament\Resources\Pelanggans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PelanggansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_pelanggan')
                    ->label('Kode pelanggan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama_pelanggan')
                    ->label('Nama pelanggan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('no_telp')
                    ->label('No. Telepon')
                    ->formatStateUsing(function ($state): string {
                        // tampilkan apa adanya (mis. 08xxx)
                        return (string) $state;
                    })
                    ->url(function ($record): ?string {
                        // ambil dari field yang benar: no_telp
                        $raw = (string) ($record->no_telp ?? '');
                        $digits = preg_replace('/\D/', '', $raw);

                        if (blank($digits)) {
                            return null;
                        }

                        // 08xxx -> 628xxx (format internasional untuk wa.me)
                        if (str_starts_with($digits, '0')) {
                            $digits = '62' . substr($digits, 1);
                        }

                        // optional: kalau user input 6208... (double), rapihin sedikit
                        if (str_starts_with($digits, '6208')) {
                            $digits = '62' . substr($digits, 3);
                        }

                        return "https://wa.me/{$digits}";
                    })
                    ->openUrlInNewTab(),

                TextColumn::make('alamat')
                    ->label('Alamat')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
