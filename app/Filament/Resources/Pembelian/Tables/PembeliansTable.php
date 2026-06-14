<?php

namespace App\Filament\Resources\Pembelian\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Models\Pembelian;

// ⬇️ ACTIONS
use Filament\Actions\ViewAction;

class PembeliansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor')
                    ->label('Kode Pembelian')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tanggal')
                    ->label('Tanggal Pemesanan')
                    ->date()
                    ->sortable(),

                TextColumn::make('vendor.nama_vendor')
                    ->label('Vendor')
                    ->searchable(),

                TextColumn::make('total_akhir')
                    ->label('Total Harga PO')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('status_penerimaan_display')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (Pembelian $record): string => $record->status === 'selesai' ? 'Diterima' : 'Belum Diterima')
                    ->color(fn (string $state): string => $state === 'Diterima' ? 'success' : 'warning'),

            ])
            ->actions([
                ViewAction::make()->label('Lihat'),
            ]);
    }
}
