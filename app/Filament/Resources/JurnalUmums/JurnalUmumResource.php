<?php

namespace App\Filament\Resources\JurnalUmums;

use App\Filament\Resources\JurnalUmums\Pages;
use App\Filament\Resources\JurnalUmums\Pages\LaporanJurnalUmum;
use App\Filament\Resources\JurnalUmums\Pages\CreateJurnalUmum;
use App\Filament\Resources\JurnalUmums\Pages\EditJurnalUmum;
use App\Filament\Resources\JurnalUmums\Pages\ListJurnalUmums;
use App\Filament\Resources\JurnalUmums\Pages\ViewJurnalUmum;
use App\Filament\Resources\JurnalUmums\Schemas\JurnalUmumForm;
use App\Filament\Resources\JurnalUmums\Tables\JurnalUmumsTable;
use App\Filament\Resources\JurnalUmums\RelationManagers\DetailsRelationManager; // <– sesuaikan folder RM
use App\Models\JurnalUmum;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Facades\Filament;

class JurnalUmumResource extends Resource
{
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    protected static ?string $model = JurnalUmum::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;
    protected static UnitEnum|string|null $navigationGroup = 'Laporan';

    protected static ?string $pluralModelLabel = 'Jurnal Umum';
    protected static ?string $navigationLabel = 'Jurnal Umum';
    protected static ?string $recordTitleAttribute = 'kode_jurnal';

    public static function form(Schema $schema): Schema
    {
        // tadi kamu pakai DaftarAkunForm, harusnya JurnalUmumForm
        return JurnalUmumForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JurnalUmumsTable::configure($table);
    }
    public static function getRelations(): array
    {
    return [
        DetailsRelationManager::class,
    ];
    }
    
    public static function getPages(): array
{
    return [
        'index'   => Pages\ListJurnalUmums::route('/'),
        'create'  => Pages\CreateJurnalUmum::route('/create'),
        //'edit'    => Pages\EditJurnalUmum::route('/{record}/edit'),
        'laporan' => Pages\LaporanJurnalUmum::route('/laporan'),
        'view'   => Pages\ViewJurnalUmum::route('/{record}'),
    ];
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'finance';
    }

}
