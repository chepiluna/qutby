<?php

namespace App\Filament\Resources\PembayaranPembelians;

use App\Filament\Resources\PembayaranPembelians\Pages\CreatePembayaranPembelian;
use App\Filament\Resources\PembayaranPembelians\Pages\ListPembayaranPembelians;
use App\Filament\Resources\PembayaranPembelians\Pages;
use App\Models\DaftarAkun;
use App\Models\JurnalUmumDetail;
use App\Models\PembayaranPembelian;
use App\Models\PenerimaanBarang;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
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

    protected static ?string $recordTitleAttribute = 'nomor_pembayaran_vendor';

    protected static ?string $slug = 'pembayaran-utang';

    protected static ?int $navigationSort = 3;

    public static function resolveTanggalPembayaran(?int $penerimaanId): ?string
    {
        if (! $penerimaanId) {
            return null;
        }

        $penerimaan = PenerimaanBarang::with([
            'vendor',
            'pembelian.vendor',
        ])->find($penerimaanId);

        if (! $penerimaan) {
            return null;
        }

        return static::calculateTanggalPembayaran($penerimaan);
    }

    protected static function calculateTanggalPembayaran(PenerimaanBarang $penerimaan): ?string
    {
        if (! $penerimaan->tanggal_terima) {
            return null;
        }

        $vendor = $penerimaan->vendor ?: $penerimaan->pembelian?->vendor;
        $periodePembayaran = max((int) ($vendor?->periode_pembayaran ?: 1), 0);

        return Carbon::parse($penerimaan->tanggal_terima)
            ->addMonthsNoOverflow($periodePembayaran)
            ->toDateString();
    }

    /**
     * Isi otomatis dari penerimaan barang
     */
    protected static function fillFromPenerimaan($state, callable $set): void
    {
        if (! $state) {

            $set('pembelian_id', null);
            $set('vendor_id', null);
            $set('nomor_faktur_vendor', null);
            $set('tanggal_faktur', null);
            $set('total_bruto', 0);
            $set('diskon_persen', 0);
            $set('total_netto', 0);

            return;
        }

        $penerimaan = PenerimaanBarang::with([
            'vendor',
            'pembelian.details.barang',
            'pembelian.vendor',
        ])->find($state);

        $pembelian = $penerimaan?->pembelian;

        if (! $penerimaan || ! $pembelian) {

            $set('pembelian_id', null);
            $set('vendor_id', null);
            $set('nomor_faktur_vendor', null);
            $set('tanggal_faktur', null);
            $set('total_bruto', 0);
            $set('diskon_persen', 0);
            $set('total_netto', 0);

            return;
        }

        /**
         * Vendor
         */
        $set('pembelian_id', $pembelian->id);
        $set('vendor_id', $penerimaan->vendor_id ?: $pembelian->vendor_id);
        $set('nomor_faktur_vendor', $penerimaan->nomor_faktur);
        $set('tanggal_faktur', static::calculateTanggalPembayaran($penerimaan));

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

    protected static function defaultPenerimaanId(): ?int
    {
        if ($penerimaanId = request()->integer('grn_id')) {
            return $penerimaanId;
        }

        if (! $pembelianId = request()->integer('pembelian_id')) {
            return null;
        }

        return PenerimaanBarang::query()
            ->where('pembelian_id', $pembelianId)
            ->orderByDesc('id')
            ->value('id');
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
                            ->label('Tanggal Pembayaran')
                            ->required()
                            ->dehydrated(),

                        TextInput::make('nomor_pembayaran_vendor')
                            ->label('Nomor Pembayaran Vendor')
                            ->default(
                                fn () => PembayaranPembelian::generateNomorPembayaranVendor()
                            )
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        Select::make('grn_id')
                            ->label('Nomor Penerimaan')

                            ->relationship(
                                name: 'penerimaanBarang',
                                titleAttribute: 'id_penerimaan',

                                modifyQueryUsing: fn ($query, $livewire) =>

                                    $livewire instanceof CreateRecord

                                        ? $query->whereHas('pembelian', fn ($pembelianQuery) => $pembelianQuery->where('status', '!=', 'lunas'))

                                        : $query
                            )

                            ->searchable()
                            ->preload()
                            ->required()

                            ->default(
                                fn () => static::defaultPenerimaanId()
                            )

                            ->live()

                            ->afterStateUpdated(
                                fn ($state, callable $set)

                                => static::fillFromPenerimaan($state, $set)
                            )

                            ->afterStateHydrated(
                                fn ($state, callable $set)

                                => static::fillFromPenerimaan($state, $set)
                            ),

                        TextInput::make('nomor_faktur_vendor')
                            ->label('Nomor Faktur')
                            ->readOnly()
                            ->dehydrated(true),

                        TextInput::make('pembelian_id')
                            ->hidden()
                            ->dehydrated(true),

                        Select::make('vendor_id')
                            ->label('Vendor')
                            ->relationship('vendor', 'nama_vendor')
                            ->disabled()
                            ->dehydrated(true)
                            ->required(),

                        TextInput::make('total_bruto')
                            ->hidden()
                            ->dehydrated(true),

                        TextInput::make('diskon_persen')
                            ->hidden()
                            ->dehydrated(true),

                        TextInput::make('total_netto')
                            ->label('Total Tagihan')
                            ->prefix('Rp ')
                            ->readOnly()
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

                TextColumn::make('nomor_pembayaran_vendor')
                    ->label('Nomor Pembayaran')
                    ->searchable(),

                TextColumn::make('vendor.nama_vendor')
                    ->label('Vendor')
                    ->searchable(),

                TextColumn::make('nomor_faktur_vendor')
                    ->label('Nomor Faktur')
                    ->placeholder('-')
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
                'pembelian.penerimaanBarangs',
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
