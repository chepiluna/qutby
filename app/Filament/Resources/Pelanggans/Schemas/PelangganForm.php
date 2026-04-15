<?php

namespace App\Filament\Resources\Pelanggans\Schemas;

use App\Models\Pelanggan;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PelangganForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Data Pelanggan')
                ->schema([
                    TextInput::make('kode_pelanggan')
                        ->label('Kode pelanggan')
                        ->required()
                        ->readOnly()
                        ->maxLength(20)
                        ->unique(ignoreRecord: true)
                        ->afterStateHydrated(function (TextInput $component, $state) {
                            if (blank($state)) {
                                $component->state(Pelanggan::generateNextKodePelanggan());
                            }
                        }),

                    TextInput::make('nama_pelanggan')
                        ->label('Nama pelanggan')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('alamat')
                        ->label('Alamat')
                        ->maxLength(255),

                    TextInput::make('no_telp')
                        ->label('No. Telepon')
                        ->tel()
                        ->maxLength(13)
                        ->minLength(11)
                        ->regex('/^(62|0)8[1-9][0-9]{6,9}$/')
                        ->helperText('Contoh: 081234567890')
                        ->required()
                        ->suffixAction(fn (?string $state) => Action::make('chatWhatsapp')
                            ->label('Chat WA')
                            ->icon('heroicon-m-chat-bubble-left-right')
                            ->url(
                                filled($state)
                                    ? (function () use ($state) {
                                        $digits = preg_replace('/\D/', '', (string) $state);

                                        // 08xxx -> 628xxx
                                        if (str_starts_with($digits, '0')) {
                                            $digits = '62' . substr($digits, 1);
                                        }

                                        return 'https://wa.me/' . $digits;
                                    })()
                                    : null,
                                shouldOpenInNewTab: true,
                            )),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ]);
    }
}
