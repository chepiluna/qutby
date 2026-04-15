<?php

namespace App\Filament\Resources\JurnalUmums\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class DetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'details';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Pilih akun berdasarkan nama, simpan ke daftar_akun_id
                Select::make('daftar_akun_id')
                    ->label('Akun')
                    ->relationship('akun', 'nama_akun')
                    ->searchable()
                    ->required(),

                Select::make('posisi')
                    ->label('Posisi')
                    ->options([
                        'debit'  => 'Debit',
                        'kredit' => 'Kredit',
                    ])
                    ->required(),

                TextInput::make('nominal')
                    ->label('Nominal')
                    ->numeric()
                    ->required(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('akun.nama_akun')
                    ->label('Akun'),
                TextEntry::make('akun.kode_akun')
                    ->label('No Akun'),
                TextEntry::make('posisi')
                    ->label('Posisi')
                    ->badge(),
                TextEntry::make('nominal')
                    ->label('Nominal')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public function table(Table $table): Table
{
    return $table
        ->recordTitleAttribute('nominal')
        ->columns([
            Tables\Columns\TextColumn::make('jurnalUmum.tanggal')
                ->label('Tanggal')
                ->date('d-M-y'),

            Tables\Columns\TextColumn::make('akun.nama_akun')
                ->label('Keterangan'),

            Tables\Columns\TextColumn::make('akun.kode_akun')
                ->label('No Akun'),

            // DEBIT
            Tables\Columns\TextColumn::make('debit')
                ->label('Debit')
                ->state(fn ($record) =>
                    $record->posisi === 'debit' ? $record->nominal : null
                )
                ->money('IDR'),

            // KREDIT
            Tables\Columns\TextColumn::make('kredit')
                ->label('Kredit')
                ->state(fn ($record) =>
                    $record->posisi === 'kredit' ? $record->nominal : null
                )
                ->money('IDR'),
        ])
        ->headerActions([
            CreateAction::make()
                ->label('Tambah Jurnal Umum Detail'),
        ])
        ->recordActions([
            ViewAction::make(),
            EditAction::make(),
            DeleteAction::make(),
        ])
        ->toolbarActions([
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ]);
}
}
