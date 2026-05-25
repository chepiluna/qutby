<?php

namespace App\Filament\Resources\PembayaranVendors;

use App\Filament\Resources\PembayaranVendors\Pages\CreatePembayaranVendor;
use App\Filament\Resources\PembayaranVendors\Pages\EditPembayaranVendor;
use App\Filament\Resources\PembayaranVendors\Pages\ListPembayaranVendors;
use App\Filament\Resources\PembayaranVendors\Pages\ViewPembayaranVendor;
use App\Filament\Resources\PembayaranVendors\Schemas\PembayaranVendorForm;
use App\Filament\Resources\PembayaranVendors\Tables\PembayaranVendorsTable;

use App\Models\PembayaranVendor;

use BackedEnum;
use UnitEnum;

use Filament\Facades\Filament;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PembayaranVendorResource extends Resource
{
    protected static ?string $model = PembayaranVendor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static UnitEnum|string|null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Pembayaran Vendor';

    protected static ?string $pluralModelLabel = 'Pembayaran Vendor';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return PembayaranVendorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PembayaranVendorsTable::configure($table);
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
            'index'  => ListPembayaranVendors::route('/'),
            'create' => CreatePembayaranVendor::route('/create'),
            'view'   => ViewPembayaranVendor::route('/{record}'),
            'edit'   => EditPembayaranVendor::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'finance';
    }


    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('jenis')
                ->label('Jenis Transaksi'),

            TextEntry::make('penjualan.no_faktur')
                ->label('No. Faktur (Tunai)'),

            TextEntry::make('piutang.no_faktur')
                ->label('No. Faktur (Kredit)'),

            TextEntry::make('penjualan.pelanggan.nama_pelanggan')
                ->label('Nama Pelanggan (Tunai)'),

            TextEntry::make('piutang.pelanggan.nama_pelanggan')
                ->label('Nama Pelanggan (Kredit)'),

            TextEntry::make('tanggal_bayar')
                ->label('Tanggal Bayar / Pelunasan')
                ->date('d/m/Y'),

            TextEntry::make('metode_bayar')
                ->label('Metode Bayar'),

            TextEntry::make('jumlah_bayar')
                ->label('Total Tagihan')
                ->money('IDR', locale: 'id_ID', decimalPlaces: 0),
        ]);
    }
}