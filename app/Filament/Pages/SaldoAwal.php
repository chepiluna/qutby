<?php

namespace App\Filament\Pages;

use App\Models\DaftarAkun;
use App\Models\JurnalUmum;
use App\Models\JurnalUmumDetail;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;
use Filament\Schemas\Components\Section;

class SaldoAwal extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;
    protected static UnitEnum|string|null $navigationGroup = 'Laporan Keuangan';
    protected static ?string $navigationLabel = 'Saldo Awal';
    protected ?string $heading = 'Saldo Awal';

    protected string $view = 'filament.pages.saldo-awal';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'akun_id' => null,
            'amount' => null,
            'bulan' => now()->format('m'),
            'tahun' => now()->format('Y'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Input Saldo Awal')
                    ->description('Saldo awal digunakan sebagai posisi keuangan untuk memulai periode akuntansi berikutnya.')
                    ->schema([

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
                            ->required(),

                        Select::make('tahun')
                            ->label('Tahun')
                            ->options([
                                '2024' => '2024',
                                '2025' => '2025',
                                '2026' => '2026',
                            ])
                            ->required(),

                        Select::make('akun_id')
                            ->label('Pilih Akun')
                            ->options(
                                DaftarAkun::query()
                                    ->whereIn('header_akun', [1, 2, 3])
                                    ->pluck('nama_akun', 'id')
                            )
                            ->searchable()
                            ->required(),

                        TextInput::make('amount')
                            ->label('Nominal')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function simpan()
    {
        $akunId = data_get($this->data, 'akun_id');
        $amount = data_get($this->data, 'amount');
        $bulan  = data_get($this->data, 'bulan');
        $tahun  = data_get($this->data, 'tahun');

        $akun = DaftarAkun::findOrFail($akunId);

        // ambil akun modal (lebih aman pakai kode akun kalau ada)
        $modal = DaftarAkun::where('nama_akun', 'Modal')->first();

        if (! $modal) {
            Notification::make()
                ->title('Akun modal belum ada!')
                ->danger()
                ->send();
            return;
        }

        // generate kode jurnal
        $last = JurnalUmum::latest()->first();
        $nextNumber = $last ? ((int) substr($last->kode_jurnal, 3)) + 1 : 1;
        $kode = 'JU-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        // 🔥 TANGGAL = AKHIR BULAN SEBELUMNYA
        $tanggal = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->subDay();

        // create jurnal
        $jurnal = JurnalUmum::create([
            'tanggal' => $tanggal,
            'kode_jurnal' => $kode,
            'deskripsi' => 'Saldo Awal - ' . $akun->nama_akun,
            'transaksi_type' => null,
            'transaksi_id' => null,
        ]);

        // LOGIC AKUNTANSI
        if ($akun->header_akun == 1) {
            // ASET → debit

            JurnalUmumDetail::create([
                'jurnal_umum_id' => $jurnal->id,
                'daftar_akun_id' => $akun->id,
                'posisi' => 'debit',
                'nominal' => $amount,
            ]);

            JurnalUmumDetail::create([
                'jurnal_umum_id' => $jurnal->id,
                'daftar_akun_id' => $modal->id,
                'posisi' => 'kredit',
                'nominal' => $amount,
            ]);

        } else {
            // UTANG & MODAL → kredit

            JurnalUmumDetail::create([
                'jurnal_umum_id' => $jurnal->id,
                'daftar_akun_id' => $akun->id,
                'posisi' => 'kredit',
                'nominal' => $amount,
            ]);

            JurnalUmumDetail::create([
                'jurnal_umum_id' => $jurnal->id,
                'daftar_akun_id' => $modal->id,
                'posisi' => 'debit',
                'nominal' => $amount,
            ]);
        }

        Notification::make()
            ->title('Saldo awal berhasil disimpan')
            ->success()
            ->send();

        // reset form
        $this->form->fill([
            'akun_id' => null,
            'amount' => null,
            'bulan' => $bulan,
            'tahun' => $tahun,
        ]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'finance';
    }
}
