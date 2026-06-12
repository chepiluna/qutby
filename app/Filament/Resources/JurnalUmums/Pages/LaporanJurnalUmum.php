<?php

namespace App\Filament\Resources\JurnalUmums\Pages;

use App\Filament\Resources\JurnalUmums\JurnalUmumResource;
use Filament\Forms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use App\Models\JurnalUmumDetail;
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Facades\Filament;

class LaporanJurnalUmum extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static string $resource = JurnalUmumResource::class;

    protected static ?string $navigationLabel = 'Laporan Jurnal Umum';

    protected string $view = 'filament.resources.jurnal-umums.pages.laporan-jurnal-umum';

    public ?string $bulan = null;
    public ?string $tahun = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                \Filament\Schemas\Components\Grid::make(2)
                    ->schema([

                        Forms\Components\Select::make('bulan')
                            ->label('Bulan')
                            ->live()
                            ->options([
                                '1' => 'Januari',
                                '2' => 'Februari',
                                '3' => 'Maret',
                                '4' => 'April',
                                '5' => 'Mei',
                                '6' => 'Juni',
                                '7' => 'Juli',
                                '8' => 'Agustus',
                                '9' => 'September',
                                '10' => 'Oktober',
                                '11' => 'November',
                                '12' => 'Desember',
                            ]),

                        Forms\Components\Select::make('tahun')
                            ->label('Tahun')
                            ->live()
                            ->options([
                                '2024' => '2024',
                                '2025' => '2025',
                                '2026' => '2026',
                            ]),
                    ]),
            ])
            ->statePath('');
    }

    public function exportPdf()
    {
        $rows = JurnalUmumDetail::query()
            ->with(['jurnalUmum', 'akun'])

            ->when($this->bulan, fn ($q) =>
                $q->whereHas('jurnalUmum', fn ($qq) =>
                    $qq->whereMonth('tanggal', $this->bulan)
                )
            )

            ->when($this->tahun, fn ($q) =>
                $q->whereHas('jurnalUmum', fn ($qq) =>
                    $qq->whereYear('tanggal', $this->tahun)
                )
            )

            ->orderBy('jurnal_umum_id')
            ->get();

        $namaBulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $periode = 'Semua Periode';

        if ($this->bulan && $this->tahun) {
            $periode = $namaBulan[$this->bulan] . ' ' . $this->tahun;
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

                    ->when($this->bulan, fn ($q) =>
                        $q->whereHas('jurnalUmum', fn ($qq) =>
                            $qq->whereMonth('tanggal', $this->bulan)
                        )
                    )

                    ->when($this->tahun, fn ($q) =>
                        $q->whereHas('jurnalUmum', fn ($qq) =>
                            $qq->whereYear('tanggal', $this->tahun)
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

            ->headerActions([
                Actions\Action::make('export_pdf')
                    ->label('Cetak PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('danger')
                    ->action(fn () => $this->exportPdf()),
            ])

            ->actions([])
            ->bulkActions([]);
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return in_array(Filament::getCurrentPanel()?->getId(), ['admin', 'finance'], true);
    }
}
