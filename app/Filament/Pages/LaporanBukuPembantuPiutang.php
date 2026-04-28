<?php

namespace App\Filament\Pages;

use App\Models\Pelanggan;
use App\Models\Piutang;
use App\Models\Pembayaran;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;
use Barryvdh\DomPDF\Facade\Pdf;

// 🔥 PAKAI SCHEMA (BUKAN FORM)
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;

class LaporanBukuPembantuPiutang extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static UnitEnum|string|null $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Buku Pembantu Piutang';

    protected string $view = 'filament.pages.laporan-buku-pembantu-piutang';

    // 🔥 STATE FILTER
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'customer_id' => null,
        ]);
    }

    // =====================================
    // 🔥 SCHEMA FILTER
    // =====================================
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter')
                    ->schema([
                        Grid::make(1)->schema([
                            Select::make('customer_id')
                                ->label('Pilih Pelanggan')
                                ->options(Pelanggan::pluck('nama_pelanggan', 'id'))
                                ->searchable()
                                ->placeholder('Semua Pelanggan')
                                ->live(),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    // =====================================
    // 🔥 DATA LAPORAN
    // =====================================
    public function getLaporanProperty()
    {
        $state = $this->form->getState();
        $customerId = $state['customer_id'] ?? null;

        $customers = $customerId
            ? Pelanggan::where('id', $customerId)->get()
            : Pelanggan::all();

        $laporan = [];

        foreach ($customers as $customer) {

            // ======================
            // DEBIT (PENJUALAN KREDIT)
            // ======================
            $piutang = Piutang::where('pelanggan_id', $customer->id)
                ->get()
                ->map(function ($item) {
                    return [
                        'tanggal' => $item->tanggal_faktur,
                        'ref' => $item->no_faktur,
                        'keterangan' => 'Penjualan Kredit',
                        'debit' => $item->total_piutang,
                        'kredit' => 0,
                        'urutan' => 1,
                    ];
                });

            // ======================
            // KREDIT (PELUNASAN)
            // ======================
            $pembayaran = Pembayaran::with(['piutang'])
                ->whereHas('piutang', function ($q) use ($customer) {
                    $q->where('pelanggan_id', $customer->id);
                })
                ->where('keterangan', 'lunas')
                ->get()
                ->map(function ($item) {
                    return [
                        'tanggal' => $item->tanggal_bayar,
                        'ref' => $item->piutang->no_faktur ?? '-',
                        'keterangan' => 'Pelunasan Piutang',
                        'debit' => 0,
                        'kredit' => $item->jumlah_bayar,
                        'urutan' => 2,
                    ];
                });

            // ======================
            // SORT TANGGAL + URUTAN
            // ======================
            $transaksi = $piutang
                ->merge($pembayaran)
                ->sortBy([
                    ['tanggal', 'asc'],
                    ['urutan', 'asc'],
                ])
                ->values();

            // ======================
            // HITUNG SALDO
            // ======================
            $saldo = 0;
            $totalDebit = 0;
            $totalKredit = 0;
            $data = [];

            foreach ($transaksi as $t) {
                $saldo += $t['debit'];
                $saldo -= $t['kredit'];

                $totalDebit += $t['debit'];
                $totalKredit += $t['kredit'];

                $t['saldo'] = $saldo;
                $data[] = $t;
            }

            if (count($data) > 0) {
                $laporan[] = [
                    'customer' => $customer->nama_pelanggan,
                    'data' => $data,
                    'total_debit' => $totalDebit,
                    'total_kredit' => $totalKredit,
                    'saldo_akhir' => $saldo,
                    'status' => $saldo <= 0 ? 'Lunas' : 'Belum Lunas',
                ];
            }
        }

        return $laporan;
    }

    // =====================================
    // 🔥 EXPORT PDF
    // =====================================
    public function exportPdf()
    {
        $state = $this->form->getState();
        $customerId = $state['customer_id'] ?? null;

        $customers = $customerId
            ? Pelanggan::where('id', $customerId)->get()
            : Pelanggan::all();

        $laporan = [];

        foreach ($customers as $customer) {

            $piutang = Piutang::where('pelanggan_id', $customer->id)
                ->get()
                ->map(function ($item) {
                    return [
                        'tanggal' => $item->tanggal_faktur,
                        'ref' => $item->no_faktur,
                        'keterangan' => 'Penjualan Kredit',
                        'debit' => $item->total_piutang,
                        'kredit' => 0,
                        'urutan' => 1,
                    ];
                });
            $pembayaran = Pembayaran::with(['piutang'])
                ->whereHas('piutang', function ($q) use ($customer) {
                    $q->where('pelanggan_id', $customer->id);
                })
                ->where('keterangan', 'lunas')
                ->get()
                ->map(function ($item) {
                    return [
                        'tanggal' => $item->tanggal_bayar,
                        'ref' => $item->piutang->no_faktur ?? '-',
                        'keterangan' => 'Pelunasan Piutang',
                        'debit' => 0,
                        'kredit' => $item->jumlah_bayar,
                        'urutan' => 2,
                    ];
                });

            $transaksi = $piutang
                ->merge($pembayaran)
                ->sortBy([
                    ['tanggal', 'asc'],
                    ['urutan', 'asc'],
                ])
                ->values();

            $saldo = 0;
            $totalDebit = 0;
            $totalKredit = 0;
            $data = [];

            foreach ($transaksi as $t) {
                $saldo += $t['debit'];
                $saldo -= $t['kredit'];

                $totalDebit += $t['debit'];
                $totalKredit += $t['kredit'];

                $t['saldo'] = $saldo;
                $data[] = $t;
            }

            if (count($data) > 0) {
                $laporan[] = [
                    'customer' => $customer->nama_pelanggan,
                    'data' => $data,
                    'total_debit' => $totalDebit,
                    'total_kredit' => $totalKredit,
                    'saldo_akhir' => $saldo,
                    'status' => $saldo <= 0 ? 'Lunas' : 'Belum Lunas',
                ];
            }
        }

        $pdf = Pdf::loadView('exports.buku-pembantu-piutang', compact('laporan'));

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'buku-pembantu-piutang.pdf'
        );
    }
}