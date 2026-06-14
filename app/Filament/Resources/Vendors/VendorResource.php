<?php

namespace App\Filament\Resources\Vendors;

use App\Filament\Resources\Vendors\Pages;
use App\Filament\Resources\Vendors\Schemas\VendorForm;
use App\Filament\Resources\Vendors\Tables\VendorsTable;
use App\Models\Vendor;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
//use App\Filament\Traits\HasRoleAccess;

class VendorResource extends Resource
{
    //use HasRoleAccess;

    protected static array $allowedRoles = ['finance', 'operasional'];
    protected static ?string $model = Vendor::class;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-building-store';

    protected static UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Vendor';

    protected static ?string $pluralModelLabel = 'Vendor';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'nama_vendor';

    /**
     * FORM
     * Semua field ada di VendorForm
     */
    public static function form(Schema $schema): Schema
    {
        return VendorForm::configure($schema);
    }

    /**
     * TABLE
     */
    public static function table(Table $table): Table
    {
        return VendorsTable::configure($table);
    }

    /**
     * RELATIONS
     */
    public static function getRelations(): array
    {
        return [];
    }

    /**
     * PAGES
     */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'view'   => Pages\ViewVendor::route('/{record}'),
            'edit'   => Pages\EditVendor::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(\Filament\Facades\Filament::getCurrentPanel()?->getId(), ['admin', 'sales'], true);
    }
}
