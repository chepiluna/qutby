<?php

namespace App\Filament\Resources\Pajaks;

use App\Filament\Resources\Pajaks\Pages\CreatePajak;
use App\Filament\Resources\Pajaks\Pages\EditPajak;
use App\Filament\Resources\Pajaks\Pages\ListPajaks;
use App\Filament\Resources\Pajaks\Schemas\PajakForm;
use App\Filament\Resources\Pajaks\Tables\PajaksTable;
use App\Models\Pajak;
use BackedEnum;
use UnitEnum; // ⬅️ tambahkan ini
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Facades\Filament;

class PajakResource extends Resource
{
    protected static ?string $model = Pajak::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    // ⬅️ baris INI yang penting: tipenya harus UnitEnum|string|null
    protected static UnitEnum|string|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Pajak';
    protected static ?string $pluralLabel = 'Pajak';

    protected static ?string $recordTitleAttribute = 'kode';

    public static function form(Schema $schema): Schema
    {
        return PajakForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PajaksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPajaks::route('/'),
            'create' => CreatePajak::route('/create'),
            'edit'   => EditPajak::route('/{record}/edit'),
        ];
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'sales';
    }

}
