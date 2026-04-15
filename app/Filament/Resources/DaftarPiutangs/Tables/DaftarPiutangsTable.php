<?php

namespace App\Filament\Resources\DaftarPiutangs\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
//use Filament\Tables\Actions\ViewAction;
use App\Models\Pelanggan;
use Filament\Actions\ViewAction;

class DaftarPiutangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_pelanggan')
                    ->label('Nama Pelanggan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_piutang')
                    ->label('Total Piutang')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sisa_piutang')
                    ->label('Sisa Piutang')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(fn ($record) =>
                        ($record->sisa_piutang ?? 0) > 0 ? 'Belum Lunas' : 'Lunas'
                    )
                    ->colors([
                        'danger' => 'Belum Lunas',
                        'success' => 'Lunas',
                    ]),
            ])

            // Filter Nama Pelanggan
            ->filters([
                SelectFilter::make('id')
                    ->label('Nama Pelanggan')
                    ->options(Pelanggan::pluck('nama_pelanggan', 'id'))
                    ->query(function ($query, $data) {
                        if ($data['value']) {
                            $query->where('id', $data['value']);
                        }
                    }),
            ])

            // Aksi Detail di kanan tabel
            ->recordActions([
                ViewAction::make()
                    ->label('Detail')
                    ->icon('heroicon-o-eye'),
            ])

            ->toolbarActions([]);
    }
}
