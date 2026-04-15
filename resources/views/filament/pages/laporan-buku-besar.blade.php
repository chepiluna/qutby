@php
    $from  = data_get($this->data, 'from');
    $until = data_get($this->data, 'until');

    $periodeText = '-';
    if ($from && $until) {
        $fromC = \Carbon\Carbon::parse($from)->locale('id');
        $untilC = \Carbon\Carbon::parse($until)->locale('id');

        $periodeText = $fromC->format('Y-m') === $untilC->format('Y-m')
            ? $fromC->translatedFormat('F Y')
            : $fromC->translatedFormat('d F Y') . ' s/d ' . $untilC->translatedFormat('d F Y');
    }
@endphp

<x-filament::page>
    {{-- HEADER (center area konten) --}}
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-6">
        <div class="mx-auto w-max text-center">
            <div class="text-lg font-bold tracking-wide">LAPORAN BUKU BESAR</div>
            <div class="text-sm font-semibold">CV QUTBY COLLECTION</div>
            <div class="text-sm text-gray-600">PERIODE: {{ $periodeText }}</div>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="mb-6">
        {{ $this->form }}
        <x-filament-actions::modals />
    </div>

    {{-- HASIL --}}
    @forelse ($this->ledgers as $ledger)
        @php
            $akun = $ledger['akun'];
            $saldoLabel = (($ledger['normal_side'] ?? 'debit') === 'debit') ? 'Saldo (D)' : 'Saldo (K)';
        @endphp

        <x-filament::section class="mb-6">
            <x-slot name="heading">
                {{ $akun->kode_akun ?? '-' }} — {{ $akun->nama_akun ?? '-' }}
            </x-slot>

            <x-slot name="description">
                Header akun: {{ $akun->header_akun ?? '-' }}
            </x-slot>

            {{-- Table ala Filament --}}
            <div class="fi-ta">
                <div class="fi-ta-content overflow-x-auto">
                    <table class="fi-ta-table w-full text-sm">
                        <thead class="fi-ta-header bg-gray-50">
                            <tr class="fi-ta-row border-b">
                                <th class="fi-ta-header-cell px-3 py-2 text-left whitespace-nowrap">Tanggal</th>
                                <th class="fi-ta-header-cell px-3 py-2 text-left">Keterangan</th>
                                <th class="fi-ta-header-cell px-3 py-2 text-left whitespace-nowrap">Ref</th>
                                <th class="fi-ta-header-cell px-3 py-2 text-right whitespace-nowrap">Debit</th>
                                <th class="fi-ta-header-cell px-3 py-2 text-right whitespace-nowrap">Kredit</th>
                                <th class="fi-ta-header-cell px-3 py-2 text-right whitespace-nowrap">{{ $saldoLabel }}</th>
                            </tr>
                        </thead>

                        <tbody class="fi-ta-body">
                            {{-- Saldo Awal --}}
                            <tr class="fi-ta-row border-b">
                                <td class="fi-ta-cell px-3 py-2"></td>
                                <td class="fi-ta-cell px-3 py-2 font-medium">Saldo Awal</td>
                                <td class="fi-ta-cell px-3 py-2"></td>
                                <td class="fi-ta-cell px-3 py-2 text-right"></td>
                                <td class="fi-ta-cell px-3 py-2 text-right"></td>
                                <td class="fi-ta-cell px-3 py-2 text-right font-medium">
                                    Rp {{ number_format((float) ($ledger['saldo_awal'] ?? 0), 0, ',', '.') }}
                                </td>
                            </tr>

                            {{-- Rows --}}
                            @forelse ($ledger['rows'] as $row)
                                <tr class="fi-ta-row border-b hover:bg-gray-50">
                                    <td class="fi-ta-cell px-3 py-2 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($row['tanggal'])->format('d-m-Y') }}
                                    </td>
                                    <td class="fi-ta-cell px-3 py-2">{{ $row['keterangan'] ?? '-' }}</td>
                                    <td class="fi-ta-cell px-3 py-2 whitespace-nowrap">{{ $row['ref'] ?? '-' }}</td>
                                    <td class="fi-ta-cell px-3 py-2 text-right whitespace-nowrap">
                                        {{ !empty($row['debit']) ? 'Rp ' . number_format((float) $row['debit'], 0, ',', '.') : '' }}
                                    </td>
                                    <td class="fi-ta-cell px-3 py-2 text-right whitespace-nowrap">
                                        {{ !empty($row['kredit']) ? 'Rp ' . number_format((float) $row['kredit'], 0, ',', '.') : '' }}
                                    </td>
                                    <td class="fi-ta-cell px-3 py-2 text-right whitespace-nowrap">
                                        Rp {{ number_format((float) ($row['saldo'] ?? 0), 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr class="fi-ta-row border-b">
                                    <td colspan="6" class="fi-ta-cell px-3 py-3 text-center text-gray-500">
                                        Tidak ada transaksi pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </x-filament::section>
    @empty
        <div class="rounded-xl border border-gray-200 bg-white p-6 text-sm text-gray-500">
            Pilih periode terlebih dahulu untuk menampilkan laporan.
        </div>
    @endforelse
</x-filament::page>
