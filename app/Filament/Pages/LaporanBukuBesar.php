<?php

namespace App\Filament\Pages;

use App\Models\DaftarAkun;
use App\Models\JurnalUmum;
use App\Models\JurnalUmumDetail;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;
use Filament\Facades\Filament;

class LaporanBukuBesar extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;
    protected static UnitEnum|string|null $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Buku Besar';
    protected ?string $heading = '';

    protected string $view = 'filament.pages.laporan-buku-besar';

    public ?array $data = [
        'akun_id' => null,
        'bulan' => null,
        'tahun' => null,
    ];

    public function mount(): void
    {
        $this->form->fill([
            'akun_id' => null,
            'bulan' => now()->format('m'),
            'tahun' => now()->format('Y'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)->schema([
                    Select::make('akun_id')
                        ->label('Akun')
                        ->options(fn () => DaftarAkun::orderBy('kode_akun')->pluck('nama_akun', 'id'))
                        ->searchable()
                        ->placeholder('Semua akun')
                        ->live(),

                    Select::make('bulan')
                        ->label('Bulan')
                        ->options([
                            '01' => 'Januari',
                            '02' => 'Februari',
                            '03' => 'Maret',
                            '04' => 'April',
                            '05' => 'Mei',
                            '06' => 'Juni',
                            '07' => 'Juli',
                            '08' => 'Agustus',
                            '09' => 'September',
                            '10' => 'Oktober',
                            '11' => 'November',
                            '12' => 'Desember',
                        ])
                        ->required()
                        ->live(),

                    Select::make('tahun')
                        ->label('Tahun')
                        ->options([
                            '2024' => '2024',
                            '2025' => '2025',
                            '2026' => '2026',
                        ])
                        ->required()
                        ->live(),
                ]),
            ])
            ->statePath('data');
    }

    private function normalSideByHeader(?int $headerAkun): string
    {
        return in_array((int) $headerAkun, [1, 5], true) ? 'debit' : 'kredit';
    }

    private function normalSideForAccount(DaftarAkun $akun): string
    {
        $normal = $this->normalSideByHeader((int) $akun->header_akun);

        $contraRevenueDebit = ['412'];

        if (in_array((string) $akun->kode_akun, $contraRevenueDebit, true)) {
            return 'debit';
        }

        return $normal;
    }

    public function getLedgersProperty(): array
    {
        $state = $this->form->getState();

        $akunId = $state['akun_id'] ?? null;
        $bulan = $state['bulan'] ?? null;
        $tahun = $state['tahun'] ?? null;

        if (! $bulan || ! $tahun) {
            return [];
        }

        $from = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $until = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();

        $akunQuery = DaftarAkun::query()->orderBy('kode_akun');

        if ($akunId) {
            $akunQuery->whereKey($akunId);
        }

        $akuns = $akunQuery->get();
        $result = [];

        foreach ($akuns as $akun) {
            $normalSide = $this->normalSideForAccount($akun);

            $isTemporary = in_array((int) $akun->header_akun, [4, 5], true);

            $startForBefore = $isTemporary
                ? $from->copy()->startOfYear()
                : null;

            $debitBefore = JurnalUmumDetail::query()
                ->where('daftar_akun_id', $akun->id)
                ->whereHas('jurnalUmum', function ($q) use ($from, $startForBefore) {
                    $q->whereDate('tanggal', '<', $from);

                    if ($startForBefore) {
                        $q->whereDate('tanggal', '>=', $startForBefore);
                    }
                })
                ->where('posisi', 'debit')
                ->sum('nominal');

            $kreditBefore = JurnalUmumDetail::query()
                ->where('daftar_akun_id', $akun->id)
                ->whereHas('jurnalUmum', function ($q) use ($from, $startForBefore) {
                    $q->whereDate('tanggal', '<', $from);

                    if ($startForBefore) {
                        $q->whereDate('tanggal', '>=', $startForBefore);
                    }
                })
                ->where('posisi', 'kredit')
                ->sum('nominal');

            $saldoAwal = $normalSide === 'debit'
                ? ($debitBefore - $kreditBefore)
                : ($kreditBefore - $debitBefore);

            $rows = JurnalUmumDetail::query()
                ->with(['jurnalUmum'])
                ->where('daftar_akun_id', $akun->id)
                ->whereHas('jurnalUmum', fn ($q) => $q
                    ->whereDate('tanggal', '>=', $from)
                    ->whereDate('tanggal', '<=', $until)
                )
                ->orderBy(
                    JurnalUmum::select('tanggal')
                        ->whereColumn('jurnal_umum.id', 'jurnal_umum_details.jurnal_umum_id')
                )
                ->orderBy('jurnal_umum_details.id')
                ->get();

            if (! $akunId && $rows->isEmpty()) {
                continue;
            }

            $running = $saldoAwal;

            $mapped = $rows->map(function ($r) use ($akun, $normalSide, &$running) {
                $debit = $r->posisi === 'debit' ? (float) $r->nominal : 0.0;
                $kredit = $r->posisi === 'kredit' ? (float) $r->nominal : 0.0;

                $running += $normalSide === 'debit'
                    ? ($debit - $kredit)
                    : ($kredit - $debit);

                return [
                    'tanggal' => $r->jurnalUmum->tanggal,
                    'ref' => $r->jurnalUmum->kode_jurnal ?? null,
                    'keterangan' => $r->jurnalUmum->deskripsi ?? $akun->nama_akun,
                    'debit' => $debit,
                    'kredit' => $kredit,
                    'saldo' => $running,
                ];
            });

            $result[] = [
                'akun' => $akun,
                'normal_side' => $normalSide,
                'saldo_awal' => $saldoAwal,
                'rows' => $mapped->all(),
            ];
        }

        return $result;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_pdf')
                ->label('Cetak PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('danger')
                ->action(fn () => $this->exportPdf()),
        ];
    }

    public function exportPdf()
    {
        $state = $this->form->getState();

        $bulan = data_get($state, 'bulan');
        $tahun = data_get($state, 'tahun');
        $akunId = data_get($state, 'akun_id');

        $ledgers = $this->ledgers;

        $namaBulan = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];

        $periode = 'Semua Periode';

        if ($bulan && $tahun) {
            $periode = ($namaBulan[$bulan] ?? $bulan) . ' ' . $tahun;
        }

        $akun = null;
        if ($akunId) {
            $akun = DaftarAkun::find($akunId);
        }

        $pdf = Pdf::loadView('exports.laporan-buku-besar', [
            'ledgers' => $ledgers,
            'periode' => $periode,
            'akun' => $akun,
        ])->setPaper('A4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'laporan-buku-besar.pdf'
        );
    }

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(Filament::getCurrentPanel()?->getId(), ['admin', 'finance'], true);
    }
}
