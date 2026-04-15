<?php

namespace App\Filament\Pages;

use App\Models\DaftarAkun;
use App\Models\JurnalUmumDetail;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use UnitEnum;
use Filament\Facades\Filament;

class LaporanLabaRugi extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartBar;
    protected static UnitEnum|string|null $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Laba Rugi';

    protected string $view = 'filament.pages.laporan-laba-rugi';

    /** state form */
    public ?array $data = [];

    /** hasil report utk ditampilkan */
    public array $report = [];

    public function mount(): void
    {
        $this->form->fill([
            'mode' => 'bulanan',
            'bulan' => now()->month,
            'tahun' => now()->year,
        ]);

        $this->generate();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('mode')
                    ->label('Periode')
                    ->options([
                        'bulanan' => 'Bulanan',
                        'tahunan' => 'Tahunan',
                    ])
                    ->live()
                    ->required(),

                Select::make('bulan')
                    ->label('Bulan')
                    ->visible(fn ($get) => $get('mode') === 'bulanan')
                    ->options([
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                    ])
                    ->required(fn ($get) => $get('mode') === 'bulanan'),

                Select::make('tahun')
                    ->label('Tahun')
                    ->options(function () {
                        $y = now()->year;
                        return collect(range($y - 5, $y + 1))
                            ->mapWithKeys(fn ($v) => [$v => (string) $v])
                            ->all();
                    })
                    ->required(),
            ])
            ->statePath('data');
    }

    public function generate(): void
    {
        $state = $this->form->getState();
        [$start, $end] = $this->resolveDateRange($state);

        $akunPenjualan = DaftarAkun::where('kode_akun', '411')->first(); // Pendapatan penjualan (normal kredit)
        $akunPotonganPenjualan = DaftarAkun::where('kode_akun', '412')->first(); // Contra revenue (normal debit)
        $akunHpp = DaftarAkun::where('kode_akun', '511')->first(); // HPP (normal debit)

        // Optional (info saja, tidak masuk perhitungan laba rugi)
        $akunPpnKeluaran = DaftarAkun::where('kode_akun', '212')->first(); // PPN Keluaran (kewajiban)

        if (! $akunPenjualan || ! $akunHpp) {
            $this->report = [
                'error' => 'Akun 411 (Penjualan) atau 511 (HPP) belum ada di daftar_akun.',
            ];
            return;
        }

        $base = JurnalUmumDetail::query()
            ->join('jurnal_umum', 'jurnal_umum.id', '=', 'jurnal_umum_details.jurnal_umum_id')
            ->whereBetween('jurnal_umum.tanggal', [$start->toDateString(), $end->toDateString()]);

        // Penjualan kotor (pendapatan): kredit - debit
        $penjualanKotor = (clone $base)
            ->where('jurnal_umum_details.daftar_akun_id', $akunPenjualan->id)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN posisi='kredit' THEN nominal ELSE 0 END),0) -
                COALESCE(SUM(CASE WHEN posisi='debit' THEN nominal ELSE 0 END),0)
                AS nilai
            ")
            ->value('nilai') ?? 0;

        // Potongan penjualan (contra revenue): debit - kredit
        $potonganPenjualan = 0;
        if ($akunPotonganPenjualan) {
            $potonganPenjualan = (clone $base)
                ->where('jurnal_umum_details.daftar_akun_id', $akunPotonganPenjualan->id)
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN posisi='debit' THEN nominal ELSE 0 END),0) -
                    COALESCE(SUM(CASE WHEN posisi='kredit' THEN nominal ELSE 0 END),0)
                    AS nilai
                ")
                ->value('nilai') ?? 0;
        }

        $penjualanBersih = $penjualanKotor - $potonganPenjualan;

        // HPP (beban): debit - kredit
        $hpp = (clone $base)
            ->where('jurnal_umum_details.daftar_akun_id', $akunHpp->id)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN posisi='debit' THEN nominal ELSE 0 END),0) -
                COALESCE(SUM(CASE WHEN posisi='kredit' THEN nominal ELSE 0 END),0)
                AS nilai
            ")
            ->value('nilai') ?? 0;

        // PPN Keluaran (info saja): kredit - debit
        $ppnKeluaran = 0;
        if ($akunPpnKeluaran) {
            $ppnKeluaran = (clone $base)
                ->where('jurnal_umum_details.daftar_akun_id', $akunPpnKeluaran->id)
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN posisi='kredit' THEN nominal ELSE 0 END),0) -
                    COALESCE(SUM(CASE WHEN posisi='debit' THEN nominal ELSE 0 END),0)
                    AS nilai
                ")
                ->value('nilai') ?? 0;
        }

        // Beban operasional: header_akun = 5, exclude akun HPP(511)
        $bebanRows = (clone $base)
            ->join('daftar_akun', 'daftar_akun.id', '=', 'jurnal_umum_details.daftar_akun_id')
            ->where('daftar_akun.header_akun', 5)
            ->where('daftar_akun.id', '!=', $akunHpp->id)
            ->groupBy('daftar_akun.id', 'daftar_akun.kode_akun', 'daftar_akun.nama_akun')
            ->selectRaw("
                daftar_akun.id,
                daftar_akun.kode_akun,
                daftar_akun.nama_akun,
                COALESCE(SUM(CASE WHEN posisi='debit' THEN nominal ELSE 0 END),0) -
                COALESCE(SUM(CASE WHEN posisi='kredit' THEN nominal ELSE 0 END),0)
                AS nilai
            ")
            ->orderBy('daftar_akun.kode_akun')
            ->get()
            ->toArray();

        $totalBebanOperasional = collect($bebanRows)->sum('nilai');

        $labaKotor = $penjualanBersih - $hpp;
        $labaOperasi = $labaKotor - $totalBebanOperasional;

        $this->report = [
            'periode' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ],

            // Penjualan
            'penjualan_kotor' => (float) $penjualanKotor,
            'potongan_penjualan' => (float) $potonganPenjualan,
            'penjualan_bersih' => (float) $penjualanBersih,

            // HPP
            'hpp' => (float) $hpp,

            // Pajak (info saja, tidak memengaruhi laba rugi)
            'ppn_keluaran' => (float) $ppnKeluaran,

            // Laba
            'laba_kotor' => (float) $labaKotor,

            // Beban
            'beban_operasional_rows' => $bebanRows,
            'total_beban_operasional' => (float) $totalBebanOperasional,

            // Final
            'laba_operasi' => (float) $labaOperasi,
            'laba_bersih' => (float) $labaOperasi,
        ];
    }

    private function resolveDateRange(array $state): array
    {
        $mode = $state['mode'] ?? 'bulanan';
        $y = (int) ($state['tahun'] ?? now()->year);

        if ($mode === 'tahunan') {
            $start = Carbon::create($y, 1, 1)->startOfDay();
            $end = Carbon::create($y, 12, 31)->endOfDay();
            return [$start, $end];
        }

        // bulanan
        $m = (int) ($state['bulan'] ?? now()->month);
        $start = Carbon::create($y, $m, 1)->startOfDay();
        $end = (clone $start)->endOfMonth()->endOfDay();
        return [$start, $end];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'finance';
    }
}
