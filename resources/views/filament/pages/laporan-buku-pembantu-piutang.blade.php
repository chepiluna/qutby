<x-filament-panels::page>
    <div class="space-y-6">

        {{-- FILTER + EXPORT --}}
        <x-filament::section>

            {{-- BUTTON --}}
            <div class="mb-4 flex justify-end">
                <button
                    wire:click="exportPdf"
                    type="button"
                    style="display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 0 18px; background: #dc2626; color: #ffffff; font-size: 14px; font-weight: 600; border: 0; border-radius: 8px; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.12);"
                >
                    Cetak PDF
                </button>
            </div>

            {{-- FILTER --}}
            <div>
                {{ $this->form }}
            </div>

        </x-filament::section>

        {{-- DATA --}}
        @forelse($this->laporan as $index => $item)

            <div class="mb-6 rounded-xl border border-gray-200 overflow-hidden">

                {{-- HEADER MAROON --}}
                <div class="bg-red-800 text-white px-4 py-3 flex justify-between items-center">
                    <div class="font-semibold">
                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }} - {{ $item['customer'] }}
                    </div>
                    <div class="text-sm">
                        Status: <strong>{{ $item['status'] }}</strong>
                        &nbsp; | &nbsp;
                        Saldo Akhir: <strong>Rp {{ number_format($item['saldo_akhir'], 0, ',', '.') }}</strong>
                    </div>
                </div>

                {{-- TABLE --}}
                <div class="bg-white overflow-x-auto">
                    <table class="w-full text-sm border-collapse">

                        {{-- HEADER --}}
                        <thead class="bg-red-100 text-red-800">
                            <tr>
                                <th class="px-3 py-2 text-left">Tanggal</th>
                                <th class="px-3 py-2 text-left">Keterangan</th>
                                <th class="px-3 py-2 text-left">Ref</th>
                                <th class="px-3 py-2 text-right">Debit</th>
                                <th class="px-3 py-2 text-right">Kredit</th>
                                <th class="px-3 py-2 text-right">Saldo</th>
                            </tr>
                        </thead>

                        {{-- BODY --}}
                        <tbody>
                            @foreach($item['data'] as $row)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-3 py-2">
                                        {{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}
                                    </td>
                                    <td class="px-3 py-2">{{ $row['keterangan'] }}</td>
                                    <td class="px-3 py-2">{{ $row['ref'] }}</td>
                                    <td class="px-3 py-2 text-right text-black">
                                        {{ $row['debit'] ? 'Rp ' . number_format($row['debit'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-right text-black">
                                        {{ $row['kredit'] ? 'Rp ' . number_format($row['kredit'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-right font-bold text-black">
                                        Rp {{ number_format($row['saldo'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        {{-- FOOTER TOTAL --}}
                        <tfoot>
                            <tr class="bg-gray-100 font-bold">
                                <td colspan="3" class="text-right px-3 py-2">Total</td>
                                <td class="text-right px-3 py-2">
                                    Rp {{ number_format($item['total_debit'], 0, ',', '.') }}
                                </td>
                                <td class="text-right px-3 py-2">
                                    Rp {{ number_format($item['total_kredit'], 0, ',', '.') }}
                                </td>
                                <td class="text-right px-3 py-2">
                                    Rp {{ number_format($item['saldo_akhir'], 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>

                    </table>
                </div>

            </div>

        @empty
            <x-filament::section>
                <div class="text-center text-gray-500">
                    Tidak ada data piutang
                </div>
            </x-filament::section>
        @endforelse

    </div>
</x-filament-panels::page>
