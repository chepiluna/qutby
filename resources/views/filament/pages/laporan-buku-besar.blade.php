@php
    $bulan = data_get($this->data, 'bulan');
    $tahun = data_get($this->data, 'tahun');

    $periodeText = '-';

    if ($bulan && $tahun) {
        $periodeText = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)
            ->locale('id')
            ->translatedFormat('F Y');
    }
@endphp

<x-filament::page>
    {{-- HEADER --}}
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
            $saldoAkhir = collect($ledger['rows'])->last()['saldo'] ?? $ledger['saldo_awal'];
        @endphp

        <div class="mb-6 rounded-xl border border-gray-200 overflow-hidden">

            {{-- HEADER MAROON --}}
            <div class="bg-red-800 text-white px-4 py-3 flex justify-between items-center">
                <div class="font-semibold">
                    {{ $akun->kode_akun ?? '-' }} — {{ $akun->nama_akun ?? '-' }}
                </div>
                <div class="text-sm">
                    Saldo Awal: <strong>Rp {{ number_format($ledger['saldo_awal'], 0, ',', '.') }}</strong>
                    &nbsp; | &nbsp;
                    Saldo Akhir: <strong>Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</strong>
                </div>
            </div>

            {{-- TABLE --}}
            <div class="bg-white">
                <table class="w-full text-sm border-collapse">
                    <thead class="bg-red-100 text-red-800">
                        <tr>
                            <th class="px-3 py-2 text-left">Tanggal</th>
                            <th class="px-3 py-2 text-left">Keterangan</th>
                            <th class="px-3 py-2 text-left">Ref</th>
                            <th class="px-3 py-2 text-right">Debit</th>
                            <th class="px-3 py-2 text-right">Kredit</th>
                            <th class="px-3 py-2 text-right">{{ $saldoLabel }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        {{-- SALDO AWAL --}}
                        <tr class="border-b bg-gray-50">
                            <td></td>
                            <td class="font-semibold px-3 py-2">Saldo Awal</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-right px-3 py-2 font-bold">
                                Rp {{ number_format($ledger['saldo_awal'], 0, ',', '.') }}
                            </td>
                        </tr>

                        {{-- TRANSAKSI --}}
                        @forelse ($ledger['rows'] as $row)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-3 py-2">
                                    {{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}
                                </td>
                                <td class="px-3 py-2">{{ $row['keterangan'] }}</td>
                                <td class="px-3 py-2">{{ $row['ref'] }}</td>

                                {{-- DEBIT (HITAM) --}}
                                <td class="px-3 py-2 text-right text-black">
                                    {{ $row['debit'] ? 'Rp ' . number_format($row['debit'], 0, ',', '.') : '-' }}
                                </td>

                                {{-- KREDIT (HITAM) --}}
                                <td class="px-3 py-2 text-right text-black">
                                    {{ $row['kredit'] ? 'Rp ' . number_format($row['kredit'], 0, ',', '.') : '-' }}
                                </td>

                                {{-- SALDO (BOLD HITAM) --}}
                                <td class="px-3 py-2 text-right font-bold text-black">
                                    Rp {{ number_format($row['saldo'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-gray-500">
                                    Tidak ada transaksi bulan ini
                                </td>
                            </tr>
                        @endforelse

                        {{-- SALDO AKHIR --}}
                        <tr class="bg-gray-100 font-bold">
                            <td colspan="5" class="text-right px-3 py-2">Saldo Akhir</td>
                            <td class="text-right px-3 py-2 font-bold">
                                Rp {{ number_format($saldoAkhir, 0, ',', '.') }}
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

    @empty
        <div class="rounded-xl border border-gray-200 bg-white p-6 text-sm text-gray-500">
            Pilih bulan dan tahun terlebih dahulu.
        </div>
    @endforelse
</x-filament::page>