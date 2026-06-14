<?php

namespace App\Filament\Resources\Penjualans;

use App\Filament\Resources\Penjualans\Pages\CreatePenjualan;
use App\Filament\Resources\Penjualans\Pages\ListPenjualans;
use App\Filament\Resources\Penjualans\Pages\ViewPenjualan;
use App\Filament\Resources\Penjualans\Schemas\PenjualanForm;
use App\Filament\Resources\Penjualans\Tables\PenjualansTable;
use App\Models\Penjualan;
use BackedEnum;
use UnitEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PenjualanResource extends Resource
{
    protected static ?string $model = Penjualan::class;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-receipt';

    protected static UnitEnum|string|null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Penjualan';

    protected static ?string $pluralModelLabel = 'Penjualan';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'no_faktur';

    public static function form(Schema $schema): Schema
    {
        return PenjualanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PenjualansTable::configure($table);
    }

    public static function updateTotals($get, $set): void
    {
        $details = collect($get('detail') ?? [])
            ->filter(fn ($item) => filled($item['barang_id'] ?? null));

        $totalBruto = $details->sum(function ($item) {

            $qty   = (int) ($item['qty'] ?? 0);

            $harga = (float) ($item['harga_satuan'] ?? 0);

            return $qty * $harga;
        });

        $set('total_bruto', $totalBruto);

        $totalDiskonRp = $details->sum(function ($item) {

            $qty   = (int) ($item['qty'] ?? 0);

            $harga = (float) ($item['harga_satuan'] ?? 0);

            $brutoItem = $qty * $harga;

            $diskonPersenItem = $qty > 5 ? 10 : 0;

            return $brutoItem * $diskonPersenItem / 100;
        });

        $set('diskon_rp', $totalDiskonRp);

        $set(
            'diskon_persen',
            $totalBruto > 0
                ? ($totalDiskonRp / $totalBruto) * 100
                : 0
        );

        $pajakPersen = (float) ($get('pajak_persen') ?? 0);

        $dpp = $totalBruto - $totalDiskonRp;

        $pajakRp = $dpp * $pajakPersen / 100;

        $set('total_netto', $dpp + $pajakRp);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPenjualans::route('/'),
            'create' => CreatePenjualan::route('/create'),
            'view'   => ViewPenjualan::route('/{record}'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(Filament::getCurrentPanel()?->getId(), ['admin', 'operasional'], true);
    }
}
