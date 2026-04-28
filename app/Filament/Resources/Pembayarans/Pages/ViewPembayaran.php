<?php

namespace App\Filament\Resources\Pembayarans\Pages;

use App\Filament\Resources\Pembayarans\PembayaranResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class ViewPembayaran extends ViewRecord
{
    protected static string $resource = PembayaranResource::class;

    public function getTitle(): string
    {
        $noFaktur = $this->record->penjualan?->no_faktur
            ?? $this->record->piutang?->no_faktur
            ?? $this->record->id;

        return "Pembayaran {$noFaktur}";
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Informasi Pembayaran')
                ->columns(2)
                ->schema([

                    TextEntry::make('no_faktur')
                        ->label('No Faktur')
                        ->state(
                            $this->record->penjualan?->no_faktur
                            ?? $this->record->piutang?->no_faktur
                            ?? '-'
                        ),

                    TextEntry::make('pelanggan')
                        ->label('Pelanggan')
                        ->state(
                            $this->record->pelanggan?->nama_pelanggan ?? '-'
                        ),

                    TextEntry::make('jenis')
                        ->label('Jenis Transaksi')
                        ->state(ucfirst($this->record->jenis)),

                    TextEntry::make('keterangan')
                        ->label('Status')
                        ->badge()
                        ->color(
                            $this->record->keterangan === 'lunas'
                                ? 'success'
                                : 'danger'
                        )
                        ->state(
                            $this->record->keterangan === 'lunas'
                                ? 'Lunas'
                                : 'Belum Lunas'
                        ),

                    TextEntry::make('tanggal_faktur')
                        ->label('Tanggal Faktur')
                        ->state($this->record->penjualan?->tanggal_faktur)
                        ->date('d/m/Y')
                        ->placeholder('-'),

                    TextEntry::make('tanggal_bayar')
                        ->label('Tanggal Bayar')
                        ->state($this->record->tanggal_bayar)
                        ->date('d/m/Y')
                        ->placeholder('-'),

                    TextEntry::make('metode_bayar')
                        ->label('Metode Bayar')
                        ->state(
                            $this->record->metode_bayar
                                ? ucfirst($this->record->metode_bayar)
                                : '-'
                        ),

                    TextEntry::make('bank')
                        ->label('Kas / Bank')
                        ->state(
                            $this->record->bankAkun?->nama_akun ?? '-'
                        ),
                ]),

            Section::make('Nominal Pembayaran')
                ->columns(2)
                ->schema([

                    TextEntry::make('jumlah_bayar')
                        ->label('Total Tagihan')
                        ->state($this->record->jumlah_bayar)
                        ->money('IDR'),

                    TextEntry::make('diskon_termin')
                        ->label('Diskon Termin')
                        ->state($this->record->diskon_termin ?? 0)
                        ->money('IDR'),

                    TextEntry::make('total_bayar')
                        ->label('Total Dibayar')
                        ->state(
                            $this->record->total_setelah_diskon
                            ?? ($this->record->jumlah_bayar - ($this->record->diskon_termin ?? 0))
                        )
                        ->money('IDR'),

                    TextEntry::make('sisa_piutang')
                        ->label('Sisa Piutang')
                        ->state(
                            $this->record->keterangan === 'lunas'
                                ? 0
                                : ($this->record->piutang?->sisa_piutang ?? $this->record->jumlah_bayar)
                        )
                        ->money('IDR'),
                ]),

            Section::make('Info Termin')
                ->columns(2)
                ->schema([

                    TextEntry::make('diskon_persen')
                        ->label('Diskon (%)')
                        ->state(
                            $this->record->piutang?->diskon_persen
                            ? $this->record->piutang->diskon_persen . '%'
                            : '-'
                        ),

                    TextEntry::make('hari_diskon')
                        ->label('Maksimal Hari Diskon')
                        ->state(
                            $this->record->piutang?->hari_diskon
                                ? $this->record->piutang->hari_diskon . ' Hari'
                                : '-'
                        ),

                    TextEntry::make('hari_jatuh_tempo')
                        ->label('Jatuh Tempo')
                        ->state(
                            $this->record->piutang?->hari_jatuh_tempo
                                ? $this->record->piutang->hari_jatuh_tempo . ' Hari'
                                : '-'
                        ),

                    TextEntry::make('tgl_jatuh_tempo')
                        ->label('Tanggal Jatuh Tempo')
                        ->state(
                            $this->record->piutang?->tgl_jatuh_tempo
                        )
                        ->date('d/m/Y')
                        ->placeholder('-'),
                ]),
        ]);
    }
}