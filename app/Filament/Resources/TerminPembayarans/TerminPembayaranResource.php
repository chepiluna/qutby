<?php

namespace App\Filament\Resources\TerminPembayarans;

use App\Filament\Resources\TerminPembayarans\Pages\CreateTerminPembayaran;
use App\Filament\Resources\TerminPembayarans\Pages\EditTerminPembayaran;
use App\Filament\Resources\TerminPembayarans\Pages\ListTerminPembayarans;
use App\Filament\Resources\TerminPembayarans\Schemas\TerminPembayaranForm;
use App\Filament\Resources\TerminPembayarans\Tables\TerminPembayaransTable;
use App\Models\TerminPembayaran;
use BackedEnum;
use UnitEnum; // ⬅️ tambahkan ini
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
 use Filament\Facades\Filament;

class TerminPembayaranResource extends Resource
{
    protected static ?string $model = TerminPembayaran::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    // ⬅️ baris INI yang penting: tipenya harus UnitEnum|string|null
    protected static UnitEnum|string|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Termin Pembayaran';

    protected static ?string $recordTitleAttribute = 'nama';

    public static function form(Schema $schema): Schema
    {
        return TerminPembayaranForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TerminPembayaransTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListTerminPembayarans::route('/'),
            'create' => CreateTerminPembayaran::route('/create'),
            'edit'   => EditTerminPembayaran::route('/{record}/edit'),
        ];
    }
   
    public static function shouldRegisterNavigation(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'sales';
    }

}
