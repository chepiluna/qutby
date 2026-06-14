<?php

namespace App\Filament\Resources\PembayaranPembelians;

use App\Filament\Resources\PembayaranPembelians\Pages\CreatePembayaranPembelian;
use App\Filament\Resources\PembayaranPembelians\Pages\ListPembayaranPembelians;
use App\Filament\Resources\PembayaranPembelians\Pages;
use App\Models\DaftarAkun;
use App\Models\JurnalUmumDetail;
use App\Models\PembayaranPembelian;
use App\Models\Pembelian;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use Filament\Facades\Filament;

class PembayaranPembelianResource extends Resource
{
    protected static array $allowedRoles = ['finance'];

    protected static ?string $model = PembayaranPembelian::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static UnitEnum|string|null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Pembayaran Utang';

    protected static ?string $modelLabel = 'Pembayaran Utang';

    protected static ?string $pluralModelLabel = 'Pembayaran Utang';

    protected static ?string $recordTitleAttribute = 'nomor_faktur_vendor';

    protected static ?int $navigationSort = 3;

    /**
     * Isi otomatis dari pembelian
     */
    protected static function fillFromPembelian($state, callable $set): void
    {
        if (! $state) {

            $set('vendor_id', null);
            $set('total_bruto', 0);
            $set('diskon_persen', 0);
            $set('total_netto', 0);

            return;
        }

        $pembelian = Pembelian::with(
            'details.barang',
            'vendor',
            'poTermins'
        )->find($state);

        if (! $pembelian) {

            $set('vendor_id', null);
            $set('total_bruto', 0);
            $set('diskon_persen', 0);
            $set('total_netto', 0);

            return;
        }

        /**
         * Vendor
         */
        $set('vendor_id', $pembelian->vendor_id);

        /**
         * Kalau kredit + termin
         */
        if (
            ($pembelian->syarat_pembayaran ?? 'kredit') === 'kredit'
            && $pembelian->poTermins->isNotEmpty()
        ) {

            $termin = $pembelian->poTermins
                ->where('status', '!=', 'lunas')
                ->sortBy('termin_ke')
                ->first()

                ?? $pembelian->poTermins
                    ->sortBy('termin_ke')
                    ->last();

            $nominal = (float) ($termin?->nominal ?? 0);

            $set('total_bruto', $nominal);
            $set('diskon_persen', 0);
            $set('total_netto', $nominal);

            return;
        }

        /**
         * Hitung total biasa
         */
        $details = $pembelian->details->map(fn ($item) => [

            'subtotal' => (float) (
                $item->subtotal
                ?? ((int) $item->qty)
                * (float) ($item->harga_satuan ?? 0)
            ),

        ])->toArray();

        $total = collect($details)->sum('subtotal');

        $diskonPersen = (float) ($pembelian->diskon ?? 0);

        $nilaiDiskon = ($diskonPersen / 100) * $total;

        $totalNetto = max($total - $nilaiDiskon, 0);

        $set('total_bruto', $total);

        $set('diskon_persen', $diskonPersen);

        $set('total_netto', $totalNetto);
    }

    /**
     * FORM
     */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([

                Section::make('Informasi Pembayaran Utang')
                    ->schema([

                        DatePicker::make('tanggal_faktur')
                            ->label('Tanggal')
                            ->required()
                            ->default(now()),

                        TextInput::make('nomor_faktur_vendor')
                            ->label('Nomor Pembayaran Vendor')
                            ->default(
                                fn () => PembayaranPembelian::generateNomorPembayaranVendor()
                            )
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        Select::make('pembelian_id')
                            ->label('Nomor Pembelian')

                            ->relationship(
                                name: 'pembelian',
                                titleAttribute: 'nomor',

                                modifyQueryUsing: fn ($query, $livewire) =>

                                    $livewire instanceof CreateRecord

                                        ? $query->where('status', '!=', 'lunas')

                                        : $query
                            )

                            ->searchable()
                            ->preload()
                            ->required()

                            ->default(
                                fn () => request()->get('pembelian_id')
                            )

                            ->live()

                            ->afterStateUpdated(
                                fn ($state, callable $set)

                                => static::fillFromPembelian($state, $set)
                            )

                            ->afterStateHydrated(
                                fn ($state, callable $set)

                                => static::fillFromPembelian($state, $set)
                            ),

                        Select::make('vendor_id')
                            ->label('Vendor')
                            ->relationship('vendor', 'nama_vendor')
                            ->disabled()
                            ->dehydrated(true)
                            ->required(),

                        Grid::make(2)
                            ->schema([

                                TextInput::make('total_bruto')
                                    ->hidden()
                                    ->dehydrated(true),

                                TextInput::make('diskon_persen')
                                    ->hidden()
                                    ->dehydrated(true),

                                /**
                                 * VALIDASI SALDO DI SINI
                                 */
                                Select::make('akun_kas_id')
                                    ->label('Akun Kas / Bank')

                                    ->options(
                                        DaftarAkun::query()

                                            ->where(function ($q) {

                                                $q->where('nama_akun', 'like', '%kas%')
                                                    ->orWhere('nama_akun', 'like', '%bank%');
                                            })

                                            ->orderBy('nama_akun')

                                            ->pluck('nama_akun', 'id')
                                    )

                                    ->searchable()

                                    ->preload()

                                    ->required()

                                    ->rules([

                                        function (callable $get) {

                                            return function (
                                                string $attribute,
                                                $value,
                                                \Closure $fail
                                            ) use ($get) {

                                                if (! $value) {
                                                    return;
                                                }

                                                $akun = DaftarAkun::find($value);

                                                if (! $akun) {
                                                    return;
                                                }

                                                $debit = JurnalUmumDetail::where(
                                                    'daftar_akun_id',
                                                    $value
                                                )
                                                    ->where('posisi', 'debit')
                                                    ->sum('nominal');

                                                $kredit = JurnalUmumDetail::where(
                                                    'daftar_akun_id',
                                                    $value
                                                )
                                                    ->where('posisi', 'kredit')
                                                    ->sum('nominal');

                                                $saldo = $debit - $kredit;

                                                $total = (float) (
                                                    $get('total_netto') ?? 0
                                                );

                                                if ($saldo < $total) {

                                                    $fail(
                                                        'Saldo akun '
                                                        . $akun->nama_akun
                                                        . ' tidak mencukupi. '
                                                        . 'Saldo tersedia Rp '
                                                        . number_format(
                                                            $saldo,
                                                            0,
                                                            ',',
                                                            '.'
                                                        )
                                                    );
                                                }
                                            };
                                        },
                                    ]),

                                TextInput::make('total_netto')
                                    ->label('Total Tagihan')
                                    ->prefix('Rp ')
                                    ->readOnly()
                                    ->dehydrated(true),

                            ])
                            ->columnSpanFull(),

                        FileUpload::make('bukti_pembayaran')
                            ->label('Upload Bukti Pembayaran')
                            ->image()
                            ->directory('bukti-pembayaran')
                            ->maxSize(5120)
                            ->columnSpanFull(),

                    ])
                    ->columns(2),

            ]);
    }

    /**
     * TABLE
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('tanggal_faktur')
                    ->label('Tanggal')
                    ->date(),

                TextColumn::make('nomor_faktur_vendor')
                    ->label('Nomor Pembayaran')
                    ->searchable(),

                TextColumn::make('vendor.nama_vendor')
                    ->label('Vendor')
                    ->searchable(),

                TextColumn::make('status_pembayaran_utang')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('total_netto')
                    ->label('Total')
                    ->formatStateUsing(
                        fn ($state)

                        => 'Rp ' . number_format(
                            (float) ($state ?? 0),
                            0,
                            ',',
                            '.'
                        )
                    ),

            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'vendor',
                'pembelian.poTermins',
            ]);
    }

    /**
     * PAGES
     */
    public static function getPages(): array
    {
        return [

            'index'  => ListPembayaranPembelians::route('/'),

            'create' => CreatePembayaranPembelian::route('/create'),

            'view'   => Pages\ViewPembayaranPembelian::route('/{record}'),

        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(Filament::getCurrentPanel()?->getId(), ['admin', 'finance'], true);
    }
}
