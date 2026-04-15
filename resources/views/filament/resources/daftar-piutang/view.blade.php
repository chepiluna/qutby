<x-filament::page>

    <div class="space-y-6">

        {{-- Informasi Pelanggan --}}
        <div class="p-6 bg-white rounded-xl shadow">
            <h2 class="text-lg font-bold mb-4">Informasi Pelanggan</h2>

            <div class="grid grid-cols-2 gap-4">
                <div><b>Kode:</b> {{ $record->kode_pelanggan }}</div>
                <div><b>Nama:</b> {{ $record->nama_pelanggan }}</div>
                <div><b>Alamat:</b> {{ $record->alamat }}</div>
                <div><b>No Telp:</b> {{ $record->no_telp }}</div>
            </div>
        </div>

        {{-- Ringkasan --}}
        <div class="p-6 bg-white rounded-xl shadow">
            <h2 class="text-lg font-bold mb-4">Ringkasan Piutang</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <b>Total Piutang:</b>
                    Rp {{ number_format($record->total_piutang ?? 0, 0, ',', '.') }}
                </div>

                <div>
                    <b>Sisa Piutang:</b>
                    Rp {{ number_format($record->sisa_piutang ?? 0, 0, ',', '.') }}
                </div>
            </div>
        </div>

        {{-- Riwayat Piutang --}}
        <div class="p-6 bg-white rounded-xl shadow">
            <h2 class="text-lg font-bold mb-4">Riwayat Piutang</h2>

            <table class="w-full border border-gray-400 text-sm" style="border-collapse: collapse;">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="p-2 border border-gray-400 text-center font-semibold">
                            No Faktur
                        </th>
                        <th class="p-2 border border-gray-400 text-center font-semibold">
                            Tanggal
                        </th>
                        <th class="p-2 border border-gray-400 text-center font-semibold">
                            Total
                        </th>
                        <th class="p-2 border border-gray-400 text-center font-semibold">
                            Sisa
                        </th>
                        <th class="p-2 border border-gray-400 text-center font-semibold">
                            Status
                        </th>
                    </tr>
                </thead>

                <tbody class="text-sm">
                    @forelse($record->piutang->sortBy('tanggal_faktur') as $p)
                        <tr class="hover:bg-gray-50">
                            {{-- No Faktur --}}
                            <td class="px-3 py-2 border border-gray-400 whitespace-nowrap">
                                {{ $p->no_faktur }}
                            </td>

                            {{-- Tanggal --}}
                            <td class="px-3 py-2 border border-gray-400 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($p->tanggal_faktur)->format('d-m-Y') }}
                            </td>

                            {{-- Total --}}
                            <td class="px-3 py-2 border border-gray-400 text-right whitespace-nowrap font-medium">
                                Rp {{ number_format($p->total_piutang, 0, ',', '.') }}
                            </td>

                            {{-- Sisa --}}
                            <td class="px-3 py-2 border border-gray-400 text-right whitespace-nowrap font-medium">
                                Rp {{ number_format($p->sisa_piutang, 0, ',', '.') }}
                            </td>

                            {{-- Status --}}
                            <td class="px-3 py-2 border border-gray-400 text-center">
                                @if($p->sisa_piutang > 0)
                                    <span class="text-red-600 font-medium">Belum Lunas</span>
                                @else
                                    <span class="text-green-600 font-medium">Lunas</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-4 text-center border border-gray-400 text-gray-500">
                                Tidak ada data piutang
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</x-filament::page>
