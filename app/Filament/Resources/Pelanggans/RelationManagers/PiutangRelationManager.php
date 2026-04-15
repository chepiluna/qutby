<?php

namespace App\Filament\Resources\Pelanggans\RelationManagers;

use Filament\Actions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;

class PiutangRelationManager extends RelationManager
{
    // nama method relasi di model Pelanggan
    protected static string $relationship = 'piutang';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('termin_id')
                    ->label('Syarat pembayaran')
                    ->relationship('termin', 'nama')   // relasi di model Piutang
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        $termin = \App\Models\TerminPembayaran::find($state);

                        // reset kalau tidak ada termin
                        if (! $termin) {
                            $set('diskon_persen', 0);
                            $set('hari_diskon', null);
                            $set('hari_jatuh_tempo', 30);
                            return;
                        }

                        // copy nilai dari termin ke field piutang
                        $set('diskon_persen', $termin->diskon_persen);
                        $set('hari_diskon', $termin->hari_diskon);
                        $set('hari_jatuh_tempo', $termin->hari_jatuh_tempo);
                    }),

                TextInput::make('no_faktur')
                    ->label('No. Faktur')
                    ->maxLength(50),

                TextInput::make('diskon_persen')
                    ->label('% Diskon')
                    ->numeric()
                    ->readOnly(),

                TextInput::make('hari_diskon')
                    ->label('Hari diskon')
                    ->numeric()
                    ->readOnly(),

                TextInput::make('hari_jatuh_tempo')
                    ->label('Hari jatuh tempo')
                    ->numeric()
                    ->readOnly(),

                TextInput::make('total_piutang')
                    ->label('Total piutang')
                    ->numeric()
                    ->required(),

                TextInput::make('sisa_piutang')
                    ->label('Sisa piutang')
                    ->numeric()
                    ->required(),

                Select::make('status')
                ->label('Status')
                ->options([
                    'belum_lunas' => 'Belum lunas',
                    'lunas'       => 'Lunas',
                ])
                ->default('belum_lunas')
                ->required(),

                DatePicker::make('tanggal_faktur')
                    ->label('Tanggal faktur')
                    ->required()
                    ->default(now()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('no_faktur')
            ->columns([
                TextColumn::make('no_faktur')
                    ->label('No. Faktur')
                    ->searchable(),

                TextColumn::make('total_piutang')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('sisa_piutang')
                    ->label('Sisa')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->summarize(
                        Sum::make()->label('Total sisa')
                    ),

                TextColumn::make('status')
                    ->label('Status'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
        
            ]);
    }
}
