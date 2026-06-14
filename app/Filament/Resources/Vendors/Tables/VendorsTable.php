<?php

namespace App\Filament\Resources\Vendors\Tables;

use App\Filament\Resources\Vendors\VendorResource;
use App\Models\Vendor;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;


class VendorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_vendor')
                    ->label('Kode Vendor'),

                TextColumn::make('nama_vendor')
                    ->label('Nama Vendor')
                    ->searchable(),

                TextColumn::make('no_telepon')
                    ->label('Nomor Telepon')
                    ->searchable(),

                TextColumn::make('alamat')
                    ->label('Alamat')
                    ->limit(30)
                    ->searchable(),
            ])
            ->recordUrl(
                fn ($record) => VendorResource::getUrl('view', ['record' => $record])
            )
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat'),   
                EditAction::make()
                    ->label('Edit'),
                Action::make('delete')
                    ->label('Hapus')
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Vendor')
                    ->modalDescription('Apakah Anda yakin ingin menghapus data vendor ini?')
                    ->modalSubmitActionLabel('Ya, hapus')
                    ->modalCancelActionLabel('Batal')
                    ->action(function (Vendor $record): void {
                        if ($record->pembelians()->exists() || $record->penerimaanBarangs()->exists()) {
                            Notification::make()
                                ->title('Vendor tidak bisa dihapus')
                                ->body('Vendor ini sudah dipakai di transaksi.')
                                ->warning()
                                ->send();

                            return;
                        }

                        DB::table('vendors')
                            ->where('id', $record->getKey())
                            ->delete();

                        Notification::make()
                            ->title('Vendor berhasil dihapus')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
            ]);
    }
}
