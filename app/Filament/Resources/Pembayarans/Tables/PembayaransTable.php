<?php

namespace App\Filament\Resources\Pembayarans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Actions\Action;
use App\Filament\Resources\Pembayarans\PembayaranResource;

class PembayaransTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('penjualan.tanggal_faktur')
                    ->label('Tgl Faktur')
                    ->date('d-m-Y')
                    ->sortable(),

                TextColumn::make('tanggal_bayar')
                    ->label('Tgl Bayar')
                    ->date('d-m-Y')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('no_faktur')
                    ->label('No. Faktur')
                    ->getStateUsing(fn ($record) =>
                        $record->penjualan?->no_faktur ?? '-'
                    )
                    ->searchable(),

                TextColumn::make('pelanggan.nama_pelanggan')
                    ->label('Pelanggan')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('jumlah_bayar')
                    ->label('Total Tagihan')
                    ->money('IDR', locale: 'id'),

                TextColumn::make('sisa_piutang')
                    ->label('Sisa Piutang')
                    ->getStateUsing(fn ($record) =>
                        $record->keterangan === 'lunas'
                            ? 0
                            : ($record->piutang?->sisa_piutang ?? $record->jumlah_bayar)
                    )
                    ->money('IDR', locale: 'id'),

                TextColumn::make('metode_bayar')
                    ->label('Metode')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                BadgeColumn::make('jenis')
                    ->label('Jenis')
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->colors([
                        'success' => 'tunai',
                        'warning' => 'kredit',
                    ]),

                BadgeColumn::make('keterangan')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) =>
                        $state === 'belum_lunas'
                            ? 'Belum Lunas'
                            : 'Lunas'
                    )
                    ->icon(fn (string $state): ?string =>
                        $state === 'lunas'
                            ? 'heroicon-o-check-circle'
                            : 'heroicon-o-clock'
                    )
                    ->color(fn (string $state): string =>
                        $state === 'lunas'
                            ? 'success'
                            : 'danger'
                    ),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('jenis')
                    ->label('Jenis')
                    ->options([
                        'tunai'  => 'Tunai',
                        'kredit' => 'Kredit',
                    ]),

                Tables\Filters\SelectFilter::make('keterangan')
                    ->label('Status')
                    ->options([
                        'lunas'       => 'Lunas',
                        'belum_lunas' => 'Belum Lunas',
                    ]),
            ])

            ->recordActions([
                ViewAction::make(),

                Action::make('bayar')
                    ->label('Bayar')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')

                    ->visible(fn ($record) =>
                        $record->jenis === 'kredit' &&
                        $record->keterangan === 'belum_lunas'
                    )

                    ->url(fn ($record) =>
                        PembayaranResource::getUrl('edit', [
                            'record' => $record->id,
                        ])
                    ),

                Action::make('sudah_lunas')
                    ->label('Lunas')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')

                    ->visible(fn ($record) =>
                        $record->jenis === 'kredit' &&
                        $record->keterangan === 'lunas'
                    )

                    ->disabled(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}