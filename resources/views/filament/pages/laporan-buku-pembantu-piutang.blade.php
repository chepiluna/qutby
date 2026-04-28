<x-filament-panels::page>
    <div class="space-y-6">

        {{-- FILTER + EXPORT --}}
        <x-filament::section>

            {{-- FILTER --}}
            <div class="mb-4">
                {{ $this->form }}
            </div>

            {{-- BUTTON --}}
            <div class="flex justify-end">
                <button
                    wire:click="exportPdf"
                    class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-primary-700 transition"
                >
                    📄 Export PDF
                </button>
            </div>

        </x-filament::section>

        {{-- DATA --}}
        @forelse($this->laporan as $index => $item)

            <x-filament::section>

                {{-- HEADER --}}
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <div class="text-base font-semibold">
                            {{ $item['customer'] }}
                        </div>

                        <div class="text-sm text-gray-500 mt-1">
                            Status:
                            <span class="{{ $item['status'] == 'Lunas' ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold' }}">
                                {{ $item['status'] }}
                            </span>
                        </div>
                    </div>

                    <div class="text-sm text-gray-500">
                        NP: {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm table-fixed">

                        {{-- HEADER --}}
                        <thead class="bg-gray-50">
                            <tr class="border-b">
                                <th class="py-3 px-3 text-center font-semibold">Tanggal</th>
                                <th class="py-3 px-3 text-center font-semibold">Ref</th>
                                <th class="py-3 px-3 text-left font-semibold">Keterangan</th>
                                <th class="py-3 px-3 text-right font-semibold">Debit</th>
                                <th class="py-3 px-3 text-right font-semibold">Kredit</th>
                                <th class="py-3 px-3 text-right font-semibold">Saldo</th>
                            </tr>
                        </thead>

                        {{-- BODY --}}
                        <tbody class="divide-y divide-gray-100">

                            @foreach($item['data'] as $row)
                                <tr class="hover:bg-gray-50">

                                    {{-- TANGGAL --}}
                                    <td class="py-2 px-3 text-center whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($row['tanggal'])->format('d-m-Y') }}
                                    </td>

                                    {{-- REF --}}
                                    <td class="py-2 px-3 text-center">
                                        {{ $row['ref'] }}
                                    </td>

                                    {{-- KETERANGAN --}}
                                    <td class="py-2 px-3">
                                        {{ $row['keterangan'] }}
                                    </td>

                                    {{-- DEBIT --}}
                                    <td class="py-2 px-3 text-right tabular-nums whitespace-nowrap">
                                        {{ $row['debit'] > 0 ? number_format($row['debit'],0,',','.') : '-' }}
                                    </td>

                                    {{-- KREDIT --}}
                                    <td class="py-2 px-3 text-right tabular-nums whitespace-nowrap">
                                        {{ $row['kredit'] > 0 ? number_format($row['kredit'],0,',','.') : '-' }}
                                    </td>

                                    {{-- SALDO --}}
                                    <td class="py-2 px-3 text-right font-semibold tabular-nums whitespace-nowrap text-orange-600">
                                        {{ number_format($row['saldo'],0,',','.') }}
                                    </td>

                                </tr>
                            @endforeach

                        </tbody>

                        {{-- FOOTER TOTAL --}}
                        <tfoot class="bg-gray-50 border-t">
                            <tr>
                                <td colspan="3" class="py-3 px-3 font-semibold text-right">
                                    TOTAL
                                </td>

                                <td class="py-3 px-3 text-right font-semibold text-blue-600">
                                    {{ number_format($item['total_debit'],0,',','.') }}
                                </td>

                                <td class="py-3 px-3 text-right font-semibold text-green-600">
                                    {{ number_format($item['total_kredit'],0,',','.') }}
                                </td>

                                <td class="py-3 px-3 text-right font-bold text-orange-600">
                                    {{ number_format($item['saldo_akhir'],0,',','.') }}
                                </td>
                            </tr>
                        </tfoot>

                    </table>
                </div>

            </x-filament::section>

        @empty
            <x-filament::section>
                <div class="text-center text-gray-500">
                    Tidak ada data piutang
                </div>
            </x-filament::section>
        @endforelse

    </div>
</x-filament-panels::page>