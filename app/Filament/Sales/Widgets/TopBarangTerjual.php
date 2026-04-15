<?php

namespace App\Filament\Sales\Widgets;

use App\Models\Barang;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TopBarangTerjual extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getHeading(): string
    {
        return 'Top 3 Barang Terjual';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder =>
                Barang::query()
                    ->leftJoin('penjualan_detail', 'penjualan_detail.barang_id', '=', 'barang.id')
                    ->select([
                        'barang.id',
                        'barang.nama_barang',
                        DB::raw('COALESCE(SUM(penjualan_detail.qty),0) as qty'),
                        DB::raw('COALESCE(SUM(penjualan_detail.subtotal),0) as omzet'),
                    ])
                    ->groupBy('barang.id', 'barang.nama_barang')
                    ->orderByDesc('qty')
                    ->limit(3)
            )
            ->columns([
                Tables\Columns\TextColumn::make('nama_barang')
                    ->label('Barang'),

                Tables\Columns\TextColumn::make('qty')
                    ->label('Qty')
                    ->numeric()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('omzet')
                    ->label('Omzet')
                    ->money('IDR')
                    ->alignEnd(), // <-- ini yang bikin rata kanan
            ])
            ->paginated(false);
    }
}
