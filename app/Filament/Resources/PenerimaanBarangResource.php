<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenerimaanBarangResource\Pages;
use App\Models\PenerimaanBarang;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use Filament\Resources\Resource;
//use App\Filament\Traits\HasRoleAccess;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Validation\Rule;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use BackedEnum;
use UnitEnum;
use Filament\Facades\Filament;

class PenerimaanBarangResource extends Resource
{
    //use HasRoleAccess;

    protected static array $allowedRoles = ['admin', 'operasional', 'gudang'];
    protected static ?string $model = PenerimaanBarang::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;
    protected static UnitEnum|string|null   $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Penerimaan Barang';
    protected static ?string $pluralModelLabel = 'Penerimaan Barang';
    protected static ?int    $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = PenerimaanBarang::where('status', 'draft')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    /* ===========================
     | FORM
     =========================== */
    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([

            Section::make('Informasi Penerimaan Barang')
                ->columns(3)
                ->schema([
                    TextInput::make('id_penerimaan')
                        ->label('Nomor Penerimaan')
                        ->default(fn (Get $get) => PenerimaanBarang::generateNomor((int) $get('pembelian_id') ?: null))
                        ->disabled()
                        ->dehydrated(),

                    Select::make('pembelian_id')
                        ->label('PO Terkait')
                        ->options(fn (): array => self::getAvailablePembelianOptions())
                        ->getOptionLabelUsing(fn ($value): ?string => Pembelian::find($value)?->nomor)
                        ->searchable()
                        ->preload()
                        ->required()
                        ->default(fn () => request()->query('pembelian_id'))
                        ->rules([
                            Rule::exists('pembelians', 'id')->whereIn('status', ['menunggu', 'partial']),
                        ])
                        ->live()
                        ->afterStateHydrated(fn (Get $get, Set $set, $state) => self::fillFromPembelian($set, $state))
                        ->afterStateUpdated(function (Get $get, Set $set, $state) {
                            if (! $state) return;
                            self::fillFromPembelian($set, $state);
                        }),

                    Select::make('vendor_id')
                        ->label('Vendor')
                        ->relationship('vendor', 'nama_vendor')
                        ->hidden(fn (Get $get): bool => filled($get('vendor_manual')))
                        ->disabled()
                        ->dehydrated(),

                    TextInput::make('vendor_manual')
                        ->label('Vendor')
                        ->hidden(fn (Get $get): bool => blank($get('vendor_manual')))
                        ->disabled()
                        ->dehydrated(false),

                    DatePicker::make('tanggal_terima')
                        ->label('Tanggal Terima')
                        ->default(now())
                        ->minDate(fn (Get $get) => Pembelian::find($get('pembelian_id'))?->tanggal)
                        ->required(),

                    TextInput::make('nomor_faktur')
                        ->label('Nomor Faktur')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('nomor_surat_jalan')
                        ->label('Nomor Surat Jalan')
                        ->required()
                        ->maxLength(100),

                ]),

            Section::make('Info Penerimaan Sebagian')
                ->visible(fn (Get $get) => self::isPartialPo($get('pembelian_id')))
                ->schema([
                    Placeholder::make('partial_info')
                        ->hiddenLabel()
                        ->content(new HtmlString(
                            '<div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900">' .
                                'PO ini sudah pernah diterima sebagian. Silakan isi qty penerimaan untuk sisa barang.' .
                            '</div>'
                        )),
                ]),

            Section::make('Rincian Barang Diterima')
                ->schema([
                    Repeater::make('details')
                        ->relationship('details')
                        ->label('Barang yang diterima')
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->mutateRelationshipDataBeforeCreateUsing(fn (array $data, Get $get): array => self::ensurePembelianDetailId($data, $get))
                        ->mutateRelationshipDataBeforeSaveUsing(fn (array $data, Get $get): array => self::ensurePembelianDetailId($data, $get))
                        ->columns(6)
                        ->schema([
                            Select::make('barang_id')
                                ->label('Barang')
                                ->relationship('barang', 'nama_barang')
                                ->searchable()
                                ->preload()
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(2),

                            Hidden::make('pembelian_detail_id')
                                ->dehydrated(),


                            TextInput::make('qty_po')
                                ->label('Qty')
                                ->disabled()
                                ->dehydrated(),

                            TextInput::make('satuan')
                                ->hidden()
                                ->dehydrated(false),

                            TextInput::make('qty_sudah_diterima')
                                ->label('Qty Diterima')
                                ->disabled()
                                ->visible(fn (Get $get) => (int) ($get('qty_sudah_diterima') ?? 0) > 0)
                                ->dehydrated(false),

                            TextInput::make('qty_sisa')
                                ->label('Qty Sisa')
                                ->disabled()
                                ->visible(fn (Get $get) => (int) ($get('qty_sudah_diterima') ?? 0) > 0)
                                ->dehydrated(false),

                            Hidden::make('qty_diterima')
                                ->dehydrated(),

                            Select::make('kondisi')
                                ->label('Kondisi')
                                ->options([
                                    'baik'           => '✅ Baik',
                                    'rusak_sebagian' => '⚠️ Rusak Sebagian',
                                    'rusak_semua'    => '❌ Rusak Semua',
                                ])
                                ->options([
                                    'baik' => 'Baik',
                                    'rusak_sebagian' => 'Rusak',
                                ])
                                ->default('baik')
                                ->hidden()
                                ->disabled(fn (Get $get) => (int) ($get('qty_diterima') ?? 0) === 0)
                                ->required(fn (Get $get) => (int) ($get('qty_diterima') ?? 0) > 0)
                                ->live(),

                            TextInput::make('qty_rusak')
                                ->label('Qty Rusak')
                                ->numeric()
                                ->default(0)
                                ->hidden()
                                ->minValue(0)
                                ->maxValue(fn (Get $get) => (int) ($get('qty_diterima') ?? 0))
                                ->live()
                                ->disabled(fn (Get $get) => (int) ($get('qty_diterima') ?? 0) === 0),

                            Textarea::make('catatan_item')
                                ->label('Catatan Selisih')
                                ->hidden()
                                ->rows(2)
                                ->placeholder('Wajib diisi jika qty kurang atau barang rusak.')
                                ->required(false)
                                ->columnSpanFull(),

                            Placeholder::make('indikator_qty')
                                ->label('')
                                ->hidden()
                                ->content(fn (Get $get) => self::renderQtyIndicator($get))
                                ->columnSpanFull(),
                        ]),
                ]),
        ]);
    }

    public static function getOpenPenerimaanBarangItems(Pembelian $po): array
    {
        $po->loadMissing(['details.barang', 'details.penerimaanBarangDetails.penerimaanBarang']);

        return $po->details
            ->filter(fn (PembelianDetail $detail) => $detail->qty_outstanding > 0)
            ->map(fn (PembelianDetail $detail) => [
                'pembelian_detail_id' => $detail->id,
                'barang_id'           => $detail->barang_id,
                'qty_po'              => $detail->qty,
                'qty_diterima'        => $detail->qty_outstanding,
                'qty_rusak'           => 0,
                'qty_sisa'            => $detail->qty_outstanding,
                'satuan'              => $detail->satuan,
                'qty_sudah_diterima'  => $detail->qty_diterima,
                'kondisi'             => 'baik',
                'foto'                => null,
                'catatan_item'        => null,
            ])
            ->values()
            ->toArray();
    }

    protected static function getAvailablePembelianOptions(): array
    {
        return Pembelian::with(['vendor', 'details.penerimaanBarangDetails.penerimaanBarang'])
            ->whereIn('status', ['menunggu', 'partial'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get()
            ->filter(fn (Pembelian $po): bool => $po->details->contains(
                fn (PembelianDetail $detail): bool => $detail->qty_outstanding > 0
            ))
            ->mapWithKeys(fn (Pembelian $po): array => [$po->id => $po->nomor])
            ->all();
    }

    protected static function fillFromPembelian(Set $set, mixed $pembelianId): void
    {
        $po = Pembelian::with(['vendor', 'details.barang', 'details.penerimaanBarangDetails.penerimaanBarang'])
            ->whereIn('status', ['menunggu', 'partial'])
            ->find($pembelianId);

        if (! $po) {
            $set('vendor_id', null);
            $set('details', []);
            return;
        }

        $items = self::getOpenPenerimaanBarangItems($po);

        if ($items === []) {
            $set('vendor_id', null);
            $set('vendor_manual', null);
            $set('details', []);
            return;
        }

        $set('vendor_id', $po->vendor_id);
        $set('vendor_manual', $po->vendor_manual);
        $set('details', $items);
        $set('id_penerimaan', PenerimaanBarang::generateNomor((int) $po->id));
    }

    protected static function isPartialPo(mixed $pembelianId): bool
    {
        if (! $pembelianId) {
            return false;
        }

        return Pembelian::query()
            ->whereKey($pembelianId)
            ->whereIn('status', ['partial'])
            ->exists();
    }

    protected static function hasItemIssue(Get $get): bool
    {
        $qtyPo = (int) ($get('qty_po') ?? 0);
        $qtyAktual = (int) ($get('qty_diterima') ?? 0);
        $qtySudahDiterima = (int) ($get('qty_sudah_diterima') ?? 0);
        $qtyTarget = max(0, $qtyPo - $qtySudahDiterima);

        return $qtyAktual < $qtyTarget;
    }

    protected static function getQtyIndicator(Get $get): array
    {
        $qtyPo = (int) ($get('qty_po') ?? 0);
        $qtyAktual = (int) ($get('qty_diterima') ?? 0);
        $qtySudahDiterima = (int) ($get('qty_sudah_diterima') ?? 0);
        $qtyTarget = max(0, $qtyPo - $qtySudahDiterima);
        $satuan = trim((string) ($get('satuan') ?? ''));
        $unit = $satuan !== '' ? ' ' . $satuan : '';
        $targetLabel = $qtySudahDiterima > 0 ? 'outstanding PO' : 'PO';

        if ($qtyAktual === 0) {
            return [
                'badge' => 'Belum Diterima',
                'message' => 'Barang belum diterima',
                'note' => 'Field Kondisi & Foto dikunci / tidak wajib diisi',
                'panelClass' => 'border-red-300 bg-red-50 text-red-800',
                'badgeClass' => 'bg-red-100 text-red-700 ring-red-200',
                'inputClass' => '!border-red-400 focus:!border-red-500 focus:!ring-red-500',
            ];
        }

        if ($qtyAktual < $qtyTarget) {
            $kurang = $qtyTarget - $qtyAktual;

            return [
                'badge' => "Kurang {$kurang}{$unit}",
                'message' => "Kurang {$kurang}{$unit} dari {$targetLabel}",
                'note' => 'Sisa akan dicatat sebagai Outstanding',
                'panelClass' => 'border-yellow-300 bg-yellow-50 text-yellow-900',
                'badgeClass' => 'bg-yellow-100 text-yellow-800 ring-yellow-200',
                'inputClass' => '!border-yellow-400 focus:!border-yellow-500 focus:!ring-yellow-500',
            ];
        }

        if ($qtyAktual === $qtyTarget) {
            return [
                'badge' => 'Lengkap',
                'message' => 'Qty diterima sudah lengkap sesuai PO',
                'note' => '',
                'panelClass' => 'border-green-300 bg-green-50 text-green-800',
                'badgeClass' => 'bg-green-100 text-green-700 ring-green-200',
                'inputClass' => '!border-green-400 focus:!border-green-500 focus:!ring-green-500',
            ];
        }

        $lebih = $qtyAktual - $qtyTarget;

        return [
            'badge' => "Lebih {$lebih}{$unit}",
            'message' => "Lebih {$lebih}{$unit} dari {$targetLabel}",
            'note' => 'Qty melebihi PO tidak bisa disimpan. Catat kelebihan sebagai barang titipan di catatan.',
            'panelClass' => 'border-blue-300 bg-blue-50 text-blue-800',
            'badgeClass' => 'bg-blue-100 text-blue-700 ring-blue-200',
            'inputClass' => '!border-blue-400 focus:!border-blue-500 focus:!ring-blue-500',
        ];
    }

    protected static function renderQtyIndicator(Get $get): HtmlString
    {
        $indicator = self::getQtyIndicator($get);
        $note = $indicator['note'] !== ''
            ? '<div class="mt-1 text-xs opacity-80">' . e($indicator['note']) . '</div>'
            : '';

        return new HtmlString(
            '<div class="rounded-lg border px-3 py-2 ' . e($indicator['panelClass']) . '">' .
                '<span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset ' . e($indicator['badgeClass']) . '">' .
                    e($indicator['badge']) .
                '</span>' .
                '<div class="mt-2 text-sm font-medium">' . e($indicator['message']) . '</div>' .
                $note .
            '</div>'
        );
    }

    protected static function ensurePembelianDetailId(array $data, Get $get): array
    {
        if (! empty($data['pembelian_detail_id'])) {
            return $data;
        }

        $pembelianId = $get('../../pembelian_id') ?? $get('pembelian_id');
        $barangId = $data['barang_id'] ?? null;

        if (! $pembelianId || ! $barangId) {
            return $data;
        }

        $pembelianDetailId = PembelianDetail::query()
            ->where('pembelian_id', $pembelianId)
            ->where('barang_id', $barangId)
            ->value('id');

        if ($pembelianDetailId) {
            $data['pembelian_detail_id'] = $pembelianDetailId;
        }

        return $data;
    }

    /* ===========================
     | TABLE
     =========================== */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_penerimaan')
                    ->label('Nomor Penerimaan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('pembelian.nomor')
                    ->label('No. PO')
                    ->searchable(),

                TextColumn::make('vendor_display_name')
                    ->label('Vendor')
                    ->getStateUsing(fn (PenerimaanBarang $record): string => $record->pembelian?->vendor_manual ?: ($record->vendor?->nama_vendor ?? '-'))
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('vendor', fn ($vendorQuery) => $vendorQuery->where('nama_vendor', 'like', "%{$search}%"))
                            ->orWhereHas('pembelian', fn ($pembelianQuery) => $pembelianQuery->where('vendor_manual', 'like', "%{$search}%"));
                    }),

                TextColumn::make('tanggal_terima')
                    ->label('Tgl. Terima')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('nomor_surat_jalan')
                    ->label('Surat Jalan')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('nomor_faktur')
                    ->label('Nomor Faktur')
                    ->searchable()
                    ->placeholder('-'),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make()->label('Detail'),
            ])
            ->emptyStateHeading('Belum ada Penerimaan Barang yang dibuat');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPenerimaanBarang::route('/'),
            'create' => Pages\CreatePenerimaanBarang::route('/create'),
            'view'   => Pages\ViewPenerimaanBarang::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['pembelian', 'vendor'])
            ->latest();
    }
    public static function shouldRegisterNavigation(): bool
    {
        return in_array(Filament::getCurrentPanel()?->getId(), ['admin', 'operasional'], true);
    }
}
