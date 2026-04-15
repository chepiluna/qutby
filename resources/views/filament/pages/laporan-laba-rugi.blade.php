<x-filament-panels::page>
    <div class="space-y-6">

        {{-- FILTER --}}
        <x-filament::section>
            <form wire:submit.prevent="generate" class="grid gap-y-6">
                {{ $this->form }}

                <div class="flex items-center gap-3">
                    <x-filament::button type="submit">
                        Tampilkan
                    </x-filament::button>

                    <div class="text-sm text-gray-600">
                        Periode: {{ $this->report['periode']['start'] ?? '-' }} s/d {{ $this->report['periode']['end'] ?? '-' }}
                    </div>
                </div>
            </form>
        </x-filament::section>

        {{-- HASIL --}}
        @if(isset($this->report['error']))
            <x-filament::section>
                <div class="text-danger-600">
                    {{ $this->report['error'] }}
                </div>
            </x-filament::section>
        @else

            @php
                $labaOperasi = $this->report['laba_operasi'] ?? 0;
                $labaBersih = $this->report['laba_bersih'] ?? 0;

                $labelOperasi = $labaOperasi >= 0 ? 'Laba Operasi' : 'Rugi Operasi';
                $labelBersih = $labaBersih >= 0 ? 'Laba Bersih' : 'Rugi Bersih';

                function formatRupiah($value) {
                    if ($value < 0) {
                        return '(Rp ' . number_format(abs($value), 0, ',', '.') . ')';
                    }
                    return 'Rp ' . number_format($value, 0, ',', '.');
                }
            @endphp

            <x-filament::section>

                {{-- HEADER --}}
                <div class="text-center mb-6 space-y-1">
                    <div class="text-lg font-bold tracking-wide">
                        LAPORAN LABA RUGI
                    </div>
                    <div class="text-base font-semibold">
                        CV QUTBY COLLECTION
                    </div>
                    <div class="text-sm text-gray-600">
                        PERIODE {{ $this->report['periode']['start'] ?? '-' }} s/d {{ $this->report['periode']['end'] ?? '-' }}
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm table-fixed">

                        <thead class="bg-gray-50">
                            <tr class="border-b">
                                <th class="py-3 px-3 text-left font-semibold">Keterangan</th>
                                <th class="py-3 px-3 text-right font-semibold">Perhitungan</th>
                                <th class="py-3 px-3 text-right font-semibold">Total</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">

                            {{-- PENDAPATAN --}}
                            <tr class="bg-gray-50">
                                <td class="py-2 px-3 font-semibold">Pendapatan</td>
                                <td></td>
                                <td></td>
                            </tr>

                            <tr>
                                <td class="py-2 px-3 pl-10">Penjualan</td>
                                <td class="text-right">
                                    {{ formatRupiah($this->report['penjualan_kotor'] ?? 0) }}
                                </td>
                                <td></td>
                            </tr>

                            <tr>
                                <td class="py-2 px-3 pl-10">Potongan Penjualan</td>
                                <td class="text-right">
                                    (Rp {{ number_format($this->report['potongan_penjualan'] ?? 0, 0, ',', '.') }})
                                </td>
                                <td></td>
                            </tr>

                            <tr class="border-t">
                                <td class="py-2 px-3 font-semibold">Penjualan Bersih</td>
                                <td></td>
                                <td class="text-right font-semibold">
                                    {{ formatRupiah($this->report['penjualan_bersih'] ?? 0) }}
                                </td>
                            </tr>

                            {{-- HPP --}}
                            <tr>
                                <td class="py-2 px-3 pl-10">Harga Pokok Penjualan</td>
                                <td></td>
                                <td class="text-right">
                                    (Rp {{ number_format($this->report['hpp'] ?? 0, 0, ',', '.') }})
                                </td>
                            </tr>

                            <tr class="border-t">
                                <td class="py-2 px-3 font-semibold">Laba Kotor</td>
                                <td></td>
                                <td class="text-right font-semibold">
                                    {{ formatRupiah($this->report['laba_kotor'] ?? 0) }}
                                </td>
                            </tr>

                            {{-- BEBAN --}}
                            <tr class="bg-gray-50">
                                <td class="py-2 px-3 font-semibold">Beban Operasional</td>
                                <td></td>
                                <td></td>
                            </tr>

                            @forelse($this->report['beban_operasional_rows'] as $row)
                                <tr>
                                    <td class="py-2 px-3 pl-10">
                                        {{ $row['nama_akun'] }}
                                    </td>
                                    <td class="text-right">
                                        {{ formatRupiah($row['nilai']) }}
                                    </td>
                                    <td></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-3 text-gray-600">
                                        Belum ada beban operasional.
                                    </td>
                                </tr>
                            @endforelse

                            <tr class="border-t">
                                <td class="py-2 px-3 font-semibold">Total Beban Operasional</td>
                                <td></td>
                                <td class="text-right font-semibold">
                                    {{ formatRupiah($this->report['total_beban_operasional'] ?? 0) }}
                                </td>
                            </tr>

                            {{-- LABA/RUGI OPERASI --}}
                            <tr class="border-t">
                                <td class="py-2 px-3 font-semibold">{{ $labelOperasi }}</td>
                                <td></td>
                                <td class="text-right font-semibold {{ $labaOperasi < 0 ? 'text-danger-600' : 'text-success-600' }}">
                                    {{ formatRupiah($labaOperasi) }}
                                </td>
                            </tr>

                            {{-- LABA/RUGI BERSIH --}}
                            <tr class="border-t-2 border-gray-900 bg-gray-50">
                                <td class="py-3 px-3 font-bold text-base">{{ $labelBersih }}</td>
                                <td></td>
                                <td class="text-right font-bold text-base {{ $labaBersih < 0 ? 'text-danger-600' : 'text-success-600' }}">
                                    {{ formatRupiah($labaBersih) }}
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>