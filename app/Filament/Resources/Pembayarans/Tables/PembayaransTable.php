<?php

namespace App\Filament\Resources\Pembayarans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class PembayaransTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal_bayar')
                    ->label('Tanggal')
                    ->date('d-m-Y')
                    ->sortable(),

                TextColumn::make('no_faktur')
                    ->label('No. Faktur')
                    ->getStateUsing(fn ($record) => 
                        $record->penjualan?->no_faktur 
                        ?? $record->piutang?->no_faktur 
                        ?? '-'
                    )
                    ->searchable(),

                TextColumn::make('jumlah_tampil')
                    ->label('Total Pembayaran')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('metode_bayar')
                    ->label('Metode'),

                // kolom nama bank (hanya terisi kalau metode transfer)
                TextColumn::make('bankAkun.nama_akun')
                    ->label('Bank')
                    ->toggleable()
                    ->placeholder('-'),

                TextColumn::make('jenis')
                    ->label('Jenis'),

                BadgeColumn::make('keterangan')
                    ->label('Keterangan')
                    ->icon(fn (string $state): ?string =>
                        $state === 'lunas' ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'
                    )
                    ->color(fn (string $state): string =>
                        $state === 'lunas' ? 'success' : 'danger'
                    ),
            ])
            ->filters([
                // filter berdasarkan jenis: tunai / kredit
                Tables\Filters\SelectFilter::make('jenis')
                    ->label('Jenis')
                    ->options([
                        'tunai'  => 'Tunai',
                        'kredit' => 'Kredit',
                    ]),

                // filter berdasarkan status pembayaran: lunas / belum lunas
                Tables\Filters\SelectFilter::make('keterangan')
                    ->label('Status')
                    ->options([
                        'lunas'       => 'Lunas',
                        'belum lunas' => 'Belum Lunas',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
