<?php

namespace App\Filament\Resources\Pembayarans;

use App\Filament\Resources\Pembayarans\Pages\CreatePembayaran;
use App\Filament\Resources\Pembayarans\Pages\EditPembayaran;
use App\Filament\Resources\Pembayarans\Pages\ListPembayarans;
use App\Filament\Resources\Pembayarans\Pages\ViewPembayaran;
use App\Filament\Resources\Pembayarans\Schemas\PembayaranForm;
use App\Filament\Resources\Pembayarans\Tables\PembayaransTable;
use App\Models\Pembayaran;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Infolists\Components\TextEntry;

class PembayaranResource extends Resource
{
    protected static ?string $model = Pembayaran::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;
    protected static UnitEnum|string|null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Pembayaran';

    protected static ?string $pluralModelLabel = 'Pembayaran';
    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return PembayaranForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PembayaransTable::configure($table);
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
            'index'  => ListPembayarans::route('/'),
            'create' => CreatePembayaran::route('/create'),
            'view'   => ViewPembayaran::route('/{record}'),
            'edit'   => EditPembayaran::route('/{record}/edit'),
        ];
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'sales';
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('jenis')->label('Jenis Transaksi'),

            TextEntry::make('penjualan.no_faktur')->label('No. Faktur (Tunai)'),
            TextEntry::make('piutang.no_faktur')->label('No. Faktur (Kredit)'),

            TextEntry::make('penjualan.pelanggan.nama_pelanggan')->label('Nama Pelanggan (Tunai)'),
            TextEntry::make('piutang.pelanggan.nama_pelanggan')->label('Nama Pelanggan (Kredit)'),

            TextEntry::make('tanggal_bayar')->label('Tanggal Bayar / Pelunasan')->date('d/m/Y'),
            TextEntry::make('metode_bayar')->label('Metode Bayar'),
            TextEntry::make('jumlah_bayar')->label('Total Tagihan') ->money('IDR', locale: 'id_ID', decimalPlaces: 0),
        ]);
}
}
