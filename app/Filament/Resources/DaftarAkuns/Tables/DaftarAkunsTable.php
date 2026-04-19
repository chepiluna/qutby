<?php

namespace App\Filament\Resources\DaftarAkuns\Tables;

use App\Models\DaftarAkun;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions;
use Illuminate\Support\HtmlString;

class DaftarAkunsTable
{
    private static function isTwoDigitParent(DaftarAkun $record): bool
    {
        // Ambil angka saja, biar aman kalau ada spasi / titik / strip.
        $digits = preg_replace('/\D+/', '', (string) $record->kode_akun);

        return strlen($digits) === 2;
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('kode_akun')
            ->columns([
                TextColumn::make('kode_akun')
                    ->label('Kode Akun')
                    ->formatStateUsing(function (string $state, DaftarAkun $record) {
                        if (self::isTwoDigitParent($record)) {
                            return new HtmlString('<strong>' . e($state) . '</strong>');
                        }

                        return e($state);
                    })
                    ->html(),

                TextColumn::make('nama_akun')
                    ->label('Nama Akun')
                    ->formatStateUsing(function (string $state, DaftarAkun $record) {
                        if (self::isTwoDigitParent($record)) {
                            return new HtmlString('<strong>' . e($state) . '</strong>');
                        }

                        return e($state);
                    })
                    ->html(),

                TextColumn::make('header_akun')
                    ->label('Header Akun')
                    ->formatStateUsing(function (string $state, DaftarAkun $record) {
                        if (self::isTwoDigitParent($record)) {
                            return new HtmlString('<strong>' . e($state) . '</strong>');
                        }

                        return e($state);
                    })
                    ->html(),

                // saldo_normal DIHILANGKAN dulu
                // parent_id DIHILANGKAN (memang tidak ada column parent_id di sini)
            ])
            ->filters([])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
                Actions\ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
