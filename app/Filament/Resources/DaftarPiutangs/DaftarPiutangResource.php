<?php

namespace App\Filament\Resources\DaftarPiutangs;

use App\Filament\Resources\DaftarPiutangs\Pages\ListDaftarPiutangs;
use App\Models\Pelanggan;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\DaftarPiutangs\Pages\ViewDaftarPiutang;

class DaftarPiutangResource extends Resource
{
    // Model utama
    protected static ?string $model = Pelanggan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static UnitEnum|string|null $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Daftar Piutang';

    protected static ?string $recordTitleAttribute = 'nama_pelanggan';

    /**
     * Query:
     * - Hanya pelanggan yang punya piutang
     * - Hitung total & sisa piutang
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('piutang') // ← hanya yang punya piutang
            ->with('piutang')
            ->withSum('piutang as total_piutang', 'total_piutang')
            ->withSum('piutang as sisa_piutang', 'sisa_piutang');
    }

    /**
     * Table configuration
     */
    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\DaftarPiutangs\Tables\DaftarPiutangsTable::configure($table);
    }

    /**
     * Resource sebagai laporan (read-only)
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    /**
     * Hanya halaman list (tanpa create/edit/view)
     */
    public static function getPages(): array
    {
        return [
            'index' => ListDaftarPiutangs::route('/'),
            'view' => ViewDaftarPiutang::route('/{record}'),
        ];
    }
}
