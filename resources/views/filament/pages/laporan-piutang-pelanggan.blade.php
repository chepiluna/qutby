@php
    $bulan = data_get($this->data, 'bulan');
    $tahun = data_get($this->data, 'tahun');

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

    $periodeText = ($bulan && $tahun)
        ? ($namaBulan[(int) $bulan] ?? '-') . ' ' . $tahun
        : '-';
@endphp

<x-filament::page>

    {{-- HEADER --}}
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-6">
        <div class="mx-auto w-max text-center">
            <div class="text-lg font-bold tracking-wide">
                LAPORAN PIUTANG PELANGGAN
            </div>

            <div class="text-sm font-semibold">
                CV QUTBY COLLECTION
            </div>

            <div class="text-sm text-gray-600">
                PERIODE: {{ $periodeText }}
            </div>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="mb-6">
        {{ $this->form }}
        <x-filament-actions::modals />
    </div>

    {{-- TABEL --}}
    <x-filament::section>
        <x-slot name="heading">
            Daftar Seluruh Piutang Pelanggan
        </x-slot>

        <x-slot name="description">
            Menampilkan daftar piutang berdasarkan filter pelanggan, bulan, dan tahun.
        </x-slot>

        <div class="fi-ta">
            <div class="fi-ta-content overflow-x-auto">
                <table class="fi-ta-table w-full text-sm">

                    {{-- HEAD --}}
                    <thead class="fi-ta-header bg-gray-50">
                        <tr class="border-b">
                            <th class="px-3 py-2 text-left">No Faktur</th>
                            <th class="px-3 py-2 text-left">Nama Pelanggan</th>
                            <th class="px-3 py-2 text-right">Sisa Piutang</th>
                            <th class="px-3 py-2 text-center">Aksi</th>
                        </tr>
                    </thead>

                    {{-- BODY --}}
                    <tbody>
                        @forelse ($this->piutangs as $piutang)
                            <tr class="border-b hover:bg-gray-50">

                                <td class="px-3 py-2">
                                    {{ $piutang->no_faktur }}
                                </td>

                                <td class="px-3 py-2">
                                    {{ $piutang->pelanggan->nama_pelanggan ?? '-' }}
                                </td>

                                <td class="px-3 py-2 text-right">
                                    Rp {{ number_format($piutang->sisa_piutang, 0, ',', '.') }}
                                </td>

                                <td class="px-3 py-2 text-center">

                                    <button
                                        type="button"
                                        x-data="{}"
                                        x-on:click="$dispatch('open-modal', { id: 'detail-{{ $piutang->id }}' })"
                                        class="inline-flex items-center rounded-lg bg-primary-600 px-3 py-1 text-xs font-medium text-white hover:bg-primary-500"
                                    >
                                        View
                                    </button>

                                    {{-- MODAL --}}
                                    <x-filament::modal id="detail-{{ $piutang->id }}" width="2xl">
                                        <x-slot name="heading">
                                            Detail Piutang
                                        </x-slot>

                                        <div class="space-y-5">

                                            <div class="rounded-xl border bg-gray-50 p-4">
                                                <div class="text-lg font-bold">
                                                    {{ $piutang->no_faktur }}
                                                </div>

                                                <div class="text-sm text-gray-500">
                                                    {{ $piutang->pelanggan->nama_pelanggan ?? '-' }}
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 gap-4 text-sm">

                                                <div class="rounded-lg border p-3">
                                                    <div class="text-xs text-gray-500 mb-1">
                                                        Tanggal Faktur
                                                    </div>

                                                    <div class="font-semibold">
                                                        {{ $piutang->tanggal_faktur ? \Carbon\Carbon::parse($piutang->tanggal_faktur)->format('d-m-Y') : '-' }}
                                                    </div>
                                                </div>

                                                <div class="rounded-lg border p-3">
                                                    <div class="text-xs text-gray-500 mb-1">
                                                        Jatuh Tempo
                                                    </div>

                                                    <div class="font-semibold">
                                                        {{ $piutang->tgl_jatuh_tempo ? \Carbon\Carbon::parse($piutang->tgl_jatuh_tempo)->format('d-m-Y') : '-' }}
                                                    </div>
                                                </div>

                                                <div class="rounded-lg border p-3">
                                                    <div class="text-xs text-gray-500 mb-1">
                                                        Total Faktur
                                                    </div>

                                                    <div class="font-semibold text-primary-600">
                                                        Rp {{ number_format($piutang->total_piutang, 0, ',', '.') }}
                                                    </div>
                                                </div>

                                                <div class="rounded-lg border p-3">
                                                    <div class="text-xs text-gray-500 mb-1">
                                                        Sisa Piutang
                                                    </div>

                                                    <div class="font-semibold text-danger-600">
                                                        Rp {{ number_format($piutang->sisa_piutang, 0, ',', '.') }}
                                                    </div>
                                                </div>

                                                <div class="rounded-lg border p-3 col-span-2">
                                                    <div class="text-xs text-gray-500 mb-1">
                                                        Status
                                                    </div>

                                                    <span class="inline-flex rounded-lg px-3 py-1 text-xs font-semibold
                                                        {{ $piutang->status == 'lunas'
                                                            ? 'bg-success-100 text-success-700'
                                                            : 'bg-danger-100 text-danger-700' }}">
                                                        {{ str_replace('_', ' ', ucfirst($piutang->status)) }}
                                                    </span>
                                                </div>

                                            </div>

                                        </div>
                                    </x-filament::modal>

                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-4 text-center text-gray-500">
                                    Tidak ada data piutang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    {{-- FOOTER --}}
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="2" class="px-3 py-3 text-right font-semibold">
                                TOTAL PIUTANG SAAT INI
                            </td>

                            <td class="px-3 py-3 text-right font-bold text-danger-600">
                                Rp {{ number_format($this->totalPiutang, 0, ',', '.') }}
                            </td>

                            <td></td>
                        </tr>
                    </tfoot>

                </table>
            </div>
        </div>

    </x-filament::section>

</x-filament::page>