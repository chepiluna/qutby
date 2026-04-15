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
    {{-- HEADER --}}
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-6">
        <div class="mx-auto w-max text-center">
            <div class="text-lg font-bold tracking-wide">LAPORAN PIUTANG PELANGGAN</div>
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
    @if ($this->isReady)
        <x-filament::section>
            <x-slot name="heading">
                Data Piutang — {{ $this->pelangganTerpilih?->nama_pelanggan ?? '-' }}
            </x-slot>

            <x-slot name="description">
                Filter berdasarkan pelanggan dan tanggal faktur.
            </x-slot>

            <div class="fi-ta">
                <div class="fi-ta-content overflow-x-auto">
                    <table class="fi-ta-table w-full text-sm">
                        <thead class="fi-ta-header bg-gray-50">
                            <tr class="fi-ta-row border-b">
                                <th class="fi-ta-header-cell px-3 py-2 text-left whitespace-nowrap">No. Faktur</th>
                                <th class="fi-ta-header-cell px-3 py-2 text-left whitespace-nowrap">Tanggal Faktur</th>
                                <th class="fi-ta-header-cell px-3 py-2 text-left whitespace-nowrap">Tanggal Lunas</th>
                                <th class="fi-ta-header-cell px-3 py-2 text-right whitespace-nowrap">Total</th>
                                <th class="fi-ta-header-cell px-3 py-2 text-right whitespace-nowrap">Sisa</th>
                                <th class="fi-ta-header-cell px-3 py-2 text-left whitespace-nowrap">Status</th>
                            </tr>
                        </thead>

                        <tbody class="fi-ta-body">
                            @forelse ($this->piutangs as $piutang)
                                <tr class="fi-ta-row border-b hover:bg-gray-50">
                                    <td class="fi-ta-cell px-3 py-2 whitespace-nowrap">
                                        {{ $piutang->no_faktur ?? '-' }}
                                    </td>

                                    <td class="fi-ta-cell px-3 py-2 whitespace-nowrap">
                                        {{ $piutang->tanggal_faktur ? \Carbon\Carbon::parse($piutang->tanggal_faktur)->format('d-m-Y') : '-' }}
                                    </td>

                                    <td class="fi-ta-cell px-3 py-2 whitespace-nowrap">
                                        {{ $piutang->tanggal_lunas ? \Carbon\Carbon::parse($piutang->tanggal_lunas)->format('d-m-Y') : '-' }}
                                    </td>

                                    <td class="fi-ta-cell px-3 py-2 text-right whitespace-nowrap">
                                        Rp {{ number_format((float) ($piutang->total_piutang ?? 0), 0, ',', '.') }}
                                    </td>

                                    <td class="fi-ta-cell px-3 py-2 text-right whitespace-nowrap">
                                        Rp {{ number_format((float) ($piutang->sisa_piutang ?? 0), 0, ',', '.') }}
                                    </td>

                                    <td class="fi-ta-cell px-3 py-2 whitespace-nowrap">
                                        @php $status = $piutang->status ?? '-'; @endphp
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                            {{ $status === 'lunas' ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20' : '' }}
                                            {{ $status === 'belum_lunas' ? 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20' : '' }}
                                            {{ !in_array($status, ['lunas','belum_lunas']) ? 'bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-600/20' : '' }}
                                        ">
                                            {{ $status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr class="fi-ta-row border-b">
                                    <td colspan="6" class="fi-ta-cell px-3 py-3 text-center text-gray-500">
                                        Tidak ada data piutang pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </x-filament::section>
    @else
        <div class="rounded-xl border border-gray-200 bg-white p-6 text-sm text-gray-500">
            Pilih pelanggan dan periode terlebih dahulu untuk menampilkan laporan.
        </div>
    @endif
</x-filament::page>
