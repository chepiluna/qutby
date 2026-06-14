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
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Checkbox;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
//use App\Filament\Traits\HasRoleAccess;
use Illuminate\Support\HtmlString;
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
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::syncTerminRows($get, $set)),

                        TextInput::make('nomor')
                            ->label('Nomor PO')
                            ->default(fn () => Pembelian::generateNomorPembelian())
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
                                $set('vendor_id', null);
                                $set('vendor_manual', null);
                                $set('use_vendor_manual', false);

                                if ($state === 'tunai') {
                                    $set('termin', []);
                                    $set('jumlah_termin', null);
                                    return;
                                }

                                $set('jumlah_termin', $get('jumlah_termin') ?: '2');
                                self::syncTerminRows($get, $set);
                            })
                            ->required(),

                        Hidden::make('use_vendor_manual')
                            ->default(false)
                            ->afterStateHydrated(function (Get $get, Set $set): void {
                                $set(
                                    'use_vendor_manual',
                                    $get('syarat_pembayaran') === 'tunai'
                                    && filled($get('vendor_manual'))
                                );
                            })
                            ->dehydrated(false),

                        Select::make('vendor_id')
                            ->label('Vendor')
                            ->placeholder('Pilih vendor')
                            ->relationship('vendor', 'nama_vendor')
                            ->searchable()
                            ->preload()
                            ->required(fn (Get $get): bool => ! (
                                $get('syarat_pembayaran') === 'tunai'
                                && (bool) $get('use_vendor_manual')
                            ))
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
                            ->required(fn (Get $get): bool =>
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
                                    ->live()
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
                                    ->live()
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
                            ->live()
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

                Section::make('Skema Pembayaran')
                    ->hidden(fn (Get $get): bool => $get('syarat_pembayaran') === 'tunai')
                    ->schema([
                        ToggleButtons::make('jumlah_termin')
                            ->label('Pilih Pembayaran')
                            ->options([
                                '2' => '2x Bayar',
                                '3' => '3x Bayar',
                            ])
                            ->inline()
                            ->live()
                            ->dehydrated(false)
                            ->default(fn ($record): string => in_array($record?->poTermins()->count(), [2, 3], true)
                                ? (string) $record->poTermins()->count()
                                : '2')
                            ->required()
                            ->extraAttributes(['class' => 'po-termin-toggle'])
                            ->afterStateHydrated(fn (Set $set, Get $get) => self::syncTerminRows($get, $set))
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::syncTerminRows($get, $set)),

                        Repeater::make('termin')
                            ->label('Tahap')
                            ->relationship('poTermins')
                            ->columns(3)
                            ->schema([
                                Hidden::make('termin_ke')
                                    ->dehydrated(),

                                Hidden::make('status')
                                    ->default('belum_bayar')
                                    ->dehydrated(),

                                Placeholder::make('termin_badge')
                                    ->label('TAHAP')
                                    ->content(fn (Get $get): HtmlString => new HtmlString(
                                        '<span style="display:inline-flex; width:32px; height:32px; align-items:center; justify-content:center; border-radius:999px; background:#EEEDFE; color:#6d28d9; font-weight:800;">'
                                        . e((string) ($get('termin_ke') ?? '-'))
                                        . '</span>'
                                    )),

                                DatePicker::make('due_date')
                                    ->label('TANGGAL PEMBAYARAN')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required(),

                                TextInput::make('nominal')
                                    ->label('NOMINAL (Rp)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->live(),

                            ])
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->hidden(fn (Get $get): bool => blank($get('jumlah_termin')))
                            ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => [
                                ...$data,
                                'status' => $data['status'] ?? 'belum_bayar',
                            ])
                            ->mutateRelationshipDataBeforeSaveUsing(fn (array $data): array => [
                                ...$data,
                                'status' => $data['status'] ?? 'belum_bayar',
                            ]),

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
            $qty   = (float) ($item['qty'] ?? 0);
            $harga = (float) ($item['harga'] ?? 0);
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

        self::syncTerminRows($get, $set, $prefix);
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
        $qty     = (float) ($get('qty') ?? 0);
        $harga   = (float) ($get('harga') ?? 0);
        $subtotal = $qty * $harga;

        $set('subtotal', round($subtotal));

        // Navigate up dari repeater scope ke form root
        self::recalculate($get, $set, '../../');
    }

    protected static function syncTerminRows(Get $get, Set $set, string $prefix = ''): void
    {
        if (($get($prefix . 'syarat_pembayaran') ?? 'kredit') === 'tunai') {
            $set($prefix . 'termin', []);
            return;
        }

        $count = (int) ($get($prefix . 'jumlah_termin') ?? 0);

        if (! in_array($count, [2, 3], true)) {
            $count = 2;
            $set($prefix . 'jumlah_termin', (string) $count);
        }

        $totalAkhir = (float) ($get($prefix . 'total_akhir') ?? 0);
        $tanggal = $get($prefix . 'tanggal') ?: now()->toDateString();
        $existing = array_values($get($prefix . 'termin') ?? []);
        $baseNominal = $count > 0 ? floor($totalAkhir / $count) : 0;
        $allocated = 0;
        $rows = [];

        for ($i = 1; $i <= $count; $i++) {
            $current = $existing[$i - 1] ?? [];
            $nominal = $i === $count
                ? max(0, round($totalAkhir - $allocated, 2))
                : $baseNominal;

            $allocated += $nominal;

            $rows[] = [
                'termin_ke' => $i,
                'due_date' => \Illuminate\Support\Carbon::parse($tanggal)->addDays(30 * $i)->toDateString(),
                'nominal' => $nominal,
                'status' => $current['status'] ?? 'belum_bayar',
            ];
        }

        $set($prefix . 'termin', $rows);
    }

    public static function validateTerminState(array $state): void
    {
        if (($state['syarat_pembayaran'] ?? 'kredit') === 'tunai') {
            return;
        }

        $jumlahTermin = (int) ($state['jumlah_termin'] ?? 0);
        $termins = array_values($state['termin'] ?? []);
        $totalAkhir = (float) ($state['total_akhir'] ?? 0);
        $allocated = collect($termins)->sum(fn (array $termin): float => (float) ($termin['nominal'] ?? 0));

        if (! in_array($jumlahTermin, [2, 3], true)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'data.jumlah_termin' => 'Pilih jumlah termin pembayaran 2x atau 3x.',
            ]);
        }

        if (count($termins) !== $jumlahTermin) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'data.termin' => 'Jumlah baris termin harus sesuai dengan pilihan jumlah termin.',
            ]);
        }

        if (abs($allocated - $totalAkhir) >= 0.01) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'data.termin' => 'Total nominal termin harus sama dengan Total Akhir PO.',
            ]);
        }
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
                    ->label('Total PO')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                    ->sortable(),
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
        return in_array(Filament::getCurrentPanel()?->getId(), ['admin', 'sales'], true);
    }
}
