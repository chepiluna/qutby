<?php

namespace App\Filament\Resources\Pajaks;

use App\Filament\Resources\Pajaks\Pages\EditPajak;
use App\Filament\Resources\Pajaks\Pages\ListPajaks;
use App\Filament\Resources\Pajaks\Schemas\PajakForm;
use App\Filament\Resources\Pajaks\Tables\PajaksTable;
use App\Models\Pajak;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

class PajakResource extends Resource
{
    protected static ?string $model = Pajak::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static UnitEnum|string|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Pengaturan Pajak';
    protected static ?string $pluralLabel = 'Pengaturan Pajak';

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
            'index' => ListPajaks::route('/'),
            'edit'  => EditPajak::route('/{record}/edit'),
        ];
    }

    // ❌ NONAKTIFKAN CREATE
    public static function canCreate(): bool
    {
        return false;
    }

    // ❌ NONAKTIFKAN DELETE
    public static function canDelete(Model $record): bool
    {
        return false;
    }

    // ✅ hanya tampil di panel sales
    public static function shouldRegisterNavigation(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'sales';
    }
}