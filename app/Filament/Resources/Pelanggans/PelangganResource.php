<?php

namespace App\Filament\Resources\Pelanggans;

use App\Filament\Resources\Pelanggans\Pages\CreatePelanggan;
use App\Filament\Resources\Pelanggans\Pages\EditPelanggan;
use App\Filament\Resources\Pelanggans\Pages\ListPelanggans;
use App\Filament\Resources\Pelanggans\Pages\ViewPelanggan;
use App\Filament\Resources\Pelanggans\Schemas\PelangganForm;
use App\Filament\Resources\Pelanggans\Tables\PelanggansTable;
use App\Filament\Resources\Pelanggans\RelationManagers\PiutangRelationManager;
use App\Models\Pelanggan;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Facades\Filament;

class PelangganResource extends Resource
{
    protected static ?string $model = Pelanggan::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;
    protected static UnitEnum|string|null $navigationGroup = 'Master Data';

    // Label di sidebar
    protected static ?string $navigationLabel = 'Pelanggan';

    // Label jamak di header/breadcrumb
    protected static ?string $pluralModelLabel = 'Pelanggan';

    // Field yang dipakai sebagai judul record
    protected static ?string $recordTitleAttribute = 'nama_pelanggan';

    public static function form(Schema $schema): Schema
    {
        return PelangganForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PelanggansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PiutangRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPelanggans::route('/'),
            'create' => CreatePelanggan::route('/create'),
            'edit'   => EditPelanggan::route('/{record}/edit'),
            'view'   => ViewPelanggan::route('/{record}'),
        ];
    }
    public static function shouldRegisterNavigation(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'sales';
    }

}
