<?php

namespace App\Filament\Resources\Pembelian;

use App\Filament\Resources\Pembelian\Pages;
use App\Models\Pembelian;
use App\Models\Barang;
use App\Models\Pajak;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Actions as SchemaActions;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Checkbox;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
//use App\Filament\Traits\HasRoleAccess;
use Filament\Facades\Filament;

class PembelianResource extends Resource
{
    //use HasRoleAccess;

    protected static array $allowedRoles = ['admin', 'operasional'];
    protected static ?string $model = Pembelian::class;

    protected static BackedEnum|string|null $navigationIcon = 'tabler-shopping-cart';
    protected static UnitEnum|string|null $navigationGroup = 'Transaksi';

    // ✅ FIX: ganti label menu utama
    protected static ?string $navigationLabel = 'Pesanan Pembelian';

    // (opsional) rapikan label plural
    protected static ?string $pluralModelLabel = 'Pesanan Pembelian';

    // (opsional) urutan menu
    protected static ?int $navigationSort = 3;

    /* =========================
     * FORM
     * ========================= */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([

                Section::make('Informasi Pembelian')
                    ->columns(4)
                    ->schema([
                        DatePicker::make('tanggal')
                            ->label('Tanggal PO')
                            ->required()
                            ->default(now())
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state): void {
                                $set('nomor', Pembelian::generateNomorPembelian($state));
                            }),

                        TextInput::make('nomor')
                            ->label('Nomor PO')
                            ->default(fn (Get $get) => Pembelian::generateNomorPembelian($get('tanggal') ?: now()))
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        Select::make('syarat_pembayaran')
                            ->label('Cara Bayar')
                            ->options([
                                'kredit' => 'Kredit',
                                'tunai' => 'Tunai',
                            ])
                            ->default('kredit')
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                if ($state === 'tunai') {
                                    $set('vendor_id', null);
                                    $set('vendor_manual', null);
                                    $set('use_vendor_manual', true);

                                    return;
                                }

                                $set('vendor_manual', null);
                                $set('use_vendor_manual', false);
                            })
                            ->required(),

                        Hidden::make('use_vendor_manual')
                            ->default(false)
                            ->afterStateHydrated(function (Get $get, Set $set): void {
                                $set('use_vendor_manual', $get('syarat_pembayaran') === 'tunai');
                            })
                            ->dehydrated(false),

                        Select::make('vendor_id')
                            ->label('Vendor')
                            ->placeholder('Pilih vendor')
                            ->relationship('vendor', 'nama_vendor')
                            ->searchable()
                            ->preload()
                            ->required(fn (Get $get): bool => $get('syarat_pembayaran') !== 'tunai')
                            ->hidden(fn (Get $get): bool => $get('syarat_pembayaran') === 'tunai' && (bool) $get('use_vendor_manual'))
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                if (filled($state)) {
                                    $set('vendor_manual', null);
                                    $set('use_vendor_manual', false);
                                }

                                $set('diskon', 0);
                                self::hitungTotal($get, $set);
                            }),

                        SchemaActions::make([
                            Action::make('use_manual_vendor')
                                ->label('Vendor tidak terdaftar')
                                ->color('gray')
                                ->visible(fn (Get $get): bool =>
                                    $get('syarat_pembayaran') === 'tunai'
                                    && ! (bool) $get('use_vendor_manual')
                                    && blank($get('vendor_id'))
                                )
                            ->action(function (Set $set): void {
                                $set('vendor_id', null);
                                $set('vendor_manual', null);
                                $set('use_vendor_manual', true);
                            }),
                        ]),

                        TextInput::make('vendor_manual')
                            ->label('Vendor')
                            ->placeholder('Ketik nama vendor')
                            ->visible(fn (Get $get): bool =>
                                $get('syarat_pembayaran') === 'tunai'
                                && (bool) $get('use_vendor_manual')
                            )
                            ->live()
                            ->maxLength(255),
                    ]),

                Section::make('Rincian Pembelian Barang')
                    ->schema([
                        Repeater::make('details')
                            ->label('Rincian Barang')
                            ->relationship('details')
                            ->addActionLabel('Tambah Barang')
                            ->columns(5)
                            ->schema([
                                Select::make('barang_id')
                                    ->label('Barang')
                                    ->relationship('barang', 'nama_barang')
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                        $barang = Barang::find($state);
                                        if (! $barang) return;

                                        $qty   = (int) ($get('qty') ?? 1);
                                        $harga = (float) $barang->harga_barang;

                                        $set('satuan', $barang->satuan);
                                        $set('harga', $harga);
                                        self::hitungSubtotal($get, $set);
                                    }),

                                TextInput::make('qty')
                                    ->label('Qty')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->live(debounce: 600)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::hitungSubtotal($get, $set)),

                                TextInput::make('satuan')
                                    ->label('Satuan')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required(),


                                TextInput::make('harga')
                                    ->label('Harga Satuan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->live(debounce: 600)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::hitungSubtotal($get, $set)),

                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('Rp ')
                                    ->disabled()
                                    ->dehydrated(),
                            ])
                            ->minItems(1),
                    ]),

                Section::make('Perhitungan Total')
                    ->columns(4)
                    ->schema([
                        TextInput::make('total')
                            ->label('Total')
                            ->prefix('Rp ')
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('diskon')
                            ->label('Diskon')
                            ->suffix('%')
                            ->numeric()
                            ->default(0)
                            ->dehydrated()
                            ->live(debounce: 600)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::hitungTotal($get, $set)),

                        Checkbox::make('ppn')
                            ->label(fn (): string => self::getPpnLabel())
                            ->default(true)
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::hitungTotal($get, $set)),

                        TextInput::make('total_akhir')
                            ->label('Total Akhir')
                            ->prefix('Rp ')
                            ->disabled()
                            ->dehydrated(),
                    ]),

            ]);
    }

    /**
     * Hitung ulang Total, Diskon, PPN, dan Total Akhir.
     * $prefix = '' jika dipanggil dari form root (vendor, ppn checkbox)
     * $prefix = '../../' jika dipanggil dari dalam repeater item
     */
    protected static function recalculate(Get $get, Set $set, string $prefix = ''): void
    {
        // 1. Total = jumlah semua subtotal baris
        $details = $get($prefix . 'details') ?? [];
        $total = 0;

        foreach ($details as $item) {
            $qty = self::parseNumber($item['qty'] ?? 0);
            $harga = self::parseNumber($item['harga'] ?? 0);
            $total += $qty * $harga;
        }

        // 2. Diskon (Rp) = Total × (Diskon % / 100)
        $diskonPersen  = (float) ($get($prefix . 'diskon') ?? 0);
        $diskonRp      = $total * ($diskonPersen / 100);

        // 3. Setelah Diskon = Total − Diskon (Rp)
        $setelahDiskon = max(0, $total - $diskonRp);

        // 4. PPN (Rp) = Setelah Diskon × 11% (jika aktif)
        $ppnAktif = (bool) ($get($prefix . 'ppn') ?? false);
        $ppnRp    = $ppnAktif ? $setelahDiskon * (self::getPpnPersen() / 100) : 0;

        // 5. Total Akhir = Setelah Diskon + PPN (Rp)
        $totalAkhir = $setelahDiskon + $ppnRp;

        $set($prefix . 'total', round($total));
        $set($prefix . 'total_akhir', round($totalAkhir));
    }

    /** Dipanggil dari form root (vendor, ppn) */
    protected static function hitungTotal(Get $get, Set $set): void
    {
        self::recalculate($get, $set, '');
    }

    protected static function getPpnPajak(): ?Pajak
    {
        return Pajak::query()
            ->where('kode', 'PPN')
            ->first();
    }

    protected static function getPpnPersen(): float
    {
        return (float) (self::getPpnPajak()?->persen ?? 0);
    }

    protected static function getPpnLabel(): string
    {
        $pajak = self::getPpnPajak();

        return $pajak
            ? $pajak->kode . ' ' . number_format((float) $pajak->persen, 0) . '%'
            : 'PPN';
    }

    /** Dipanggil dari dalam repeater item (barang, qty, harga) */
    protected static function hitungSubtotal(Get $get, Set $set): void
    {
        $qty = self::parseNumber($get('qty') ?? 0);
        $harga = self::parseNumber($get('harga') ?? 0);
        $subtotal = $qty * $harga;

        $set('subtotal', round($subtotal));

        // Navigate up dari repeater scope ke form root
        self::recalculate($get, $set, '../../');
    }

    protected static function parseNumber(mixed $value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return 0;
        }

        $value = preg_replace('/[^\d,.-]/', '', $value) ?? '';

        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (str_contains($value, ',')) {
            $value = str_replace(',', '.', $value);
        } elseif (substr_count($value, '.') > 1 || preg_match('/\.\d{3}$/', $value)) {
            $value = str_replace('.', '', $value);
        }

        return (float) $value;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('tanggal')->label('Tanggal PO')->date()->sortable(),
                TextColumn::make('nomor')->label('No. PO')->searchable(),
                TextColumn::make('vendor_display_name')
                    ->label('Vendor')
                    ->getStateUsing(fn (Pembelian $record): string => $record->vendor_manual ?: ($record->vendor?->nama_vendor ?? '-'))
                    ->searchable(query: function ($query, string $search) {
                        $query->where('vendor_manual', 'like', "%{$search}%")
                            ->orWhereHas('vendor', fn ($vendorQuery) => $vendorQuery->where('nama_vendor', 'like', "%{$search}%"));
                    }),
                TextColumn::make('total_akhir')
                    ->label('Total Harga PO')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('status_penerimaan_display')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (Pembelian $record): string => $record->status === 'selesai' ? 'Diterima' : 'Belum Diterima')
                    ->color(fn (string $state): string => $state === 'Diterima' ? 'success' : 'warning'),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make()->label('Lihat'),
            ]);
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPembelian::route('/'),
            'create' => Pages\CreatePembelian::route('/create'),
            'edit'   => Pages\EditPembelian::route('/{record}/edit'),
            'view'   => Pages\ViewPembelian::route('/{record}'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(Filament::getCurrentPanel()?->getId(), ['admin', 'operasional'], true);
    }
}
