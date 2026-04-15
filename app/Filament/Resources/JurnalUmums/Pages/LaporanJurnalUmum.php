<?php

namespace App\Filament\Resources\JurnalUmums\Pages;

use App\Filament\Resources\JurnalUmums\JurnalUmumResource;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use App\Models\JurnalUmumDetail;
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Facades\Filament;


class LaporanJurnalUmum extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = JurnalUmumResource::class;
    protected static ?string $navigationLabel = 'Laporan Jurnal Umum';
    protected string $view = 'filament.resources.jurnal-umums.pages.laporan-jurnal-umum';

    public ?string $from = null;
    public ?string $until = null;

    public function exportPdf()
{
    $periodeState = $this->getTableFilterState('periode') ?? []; // ambil state filter [web:121]
    $from  = $periodeState['from']  ?? null;
    $until = $periodeState['until'] ?? null;

    $rows = JurnalUmumDetail::query()
        ->with(['jurnalUmum', 'akun'])
        ->when($from, fn ($q) =>
            $q->whereHas('jurnalUmum', fn ($qq) =>
                $qq->whereDate('tanggal', '>=', $from)
            )
        )
        ->when($until, fn ($q) =>
            $q->whereHas('jurnalUmum', fn ($qq) =>
                $qq->whereDate('tanggal', '<=', $until)
            )
        )
        ->orderBy('jurnal_umum_id')
        ->get();

    $periode = 'Semua Periode';
    if ($from && $until) {
        $periode = Carbon::parse($from)->translatedFormat('d F Y')
            . ' s/d ' .
            Carbon::parse($until)->translatedFormat('d F Y');
    } elseif ($from) {
        $periode = 'Mulai ' . Carbon::parse($from)->translatedFormat('d F Y');
    } elseif ($until) {
        $periode = 'Sampai ' . Carbon::parse($until)->translatedFormat('d F Y');
    }

    $pdf = Pdf::loadView('exports.laporan-jurnal-umum', [
        'rows' => $rows,
        'periode' => $periode,
    ])->setPaper('A4', 'portrait');

    return response()->streamDownload(
        fn () => print($pdf->output()),
        'laporan-jurnal-umum.pdf'
    );
}
    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                return JurnalUmumDetail::query()
                    ->with(['jurnalUmum', 'akun'])
                    ->when($this->from, fn (Builder $query) =>
                        $query->whereHas('jurnalUmum', fn (Builder $q) =>
                            $q->whereDate('tanggal', '>=', $this->from)
                        )
                    )
                    ->when($this->until, fn (Builder $query) =>
                        $query->whereHas('jurnalUmum', fn (Builder $q) =>
                            $q->whereDate('tanggal', '<=', $this->until)
                        )
                    );
            })
            ->columns([
                Tables\Columns\TextColumn::make('jurnalUmum.tanggal')
                    ->label('Tanggal')
                    ->date('d-M-y'),

                Tables\Columns\TextColumn::make('akun.nama_akun')
                    ->label('Keterangan')
                    ->wrap()
                    ->extraCellAttributes(fn ($record) => [
                        'class' => $record->posisi === 'kredit' ? 'ps-8' : '',
                    ]),
                
                Tables\Columns\TextColumn::make('akun.kode_akun')
                    ->label('No Akun'),

                // ===== DEBIT =====
                Tables\Columns\TextColumn::make('debit')
                    ->label('Debit')
                    ->alignEnd()
                    ->state(fn ($record) =>
                        $record->posisi === 'debit' ? $record->nominal : null
                    )
                    ->formatStateUsing(fn ($state) =>
                        $state ? 'Rp ' . number_format($state, 0, ',', '.') : ''
                    )
                    ->summarize(
                        Summarizer::make()
                            ->using(fn ($query) =>
                                $query->where('posisi', 'debit')->sum('nominal')
                            )
                            ->formatStateUsing(fn ($state) =>
                                $state ? 'Rp ' . number_format($state, 0, ',', '.') : ''
                            )
                            ->label('Total Debit')
                    ),

                // ===== KREDIT =====
                Tables\Columns\TextColumn::make('kredit')
                    ->label('Kredit')
                    ->alignEnd()
                    ->state(fn ($record) =>
                        $record->posisi === 'kredit' ? $record->nominal : null
                    )
                    ->formatStateUsing(fn ($state) =>
                        $state ? 'Rp ' . number_format($state, 0, ',', '.') : ''
                    )
                    ->summarize(
                        Summarizer::make()
                            ->using(fn ($query) =>
                                $query->where('posisi', 'kredit')->sum('nominal')
                            )
                            ->formatStateUsing(fn ($state) =>
                                $state ? 'Rp ' . number_format($state, 0, ',', '.') : ''
                            )
                            ->label('Total Kredit')
                    ),
            ])
            ->filters([
                Tables\Filters\Filter::make('periode')
                    ->form([
                        DatePicker::make('from')->label('Dari tanggal'),
                        DatePicker::make('until')->label('Sampai tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) =>
                                $q->whereHas('jurnalUmum', fn (Builder $qq) =>
                                    $qq->whereDate('tanggal', '>=', $date)
                                )
                            )
                            ->when($data['until'] ?? null, fn (Builder $q, $date) =>
                                $q->whereHas('jurnalUmum', fn (Builder $qq) =>
                                    $qq->whereDate('tanggal', '<=', $date)
                                )
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = 'Dari: ' . $data['from'];
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = 'Sampai: ' . $data['until'];
                        }

                        return $indicators;
                    }),
            ])
            
           ->headerActions([
                Actions\Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('danger')
                    ->action(fn () => $this->exportPdf()),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
{
    return Filament::getCurrentPanel()?->getId() === 'finance';
}
}
