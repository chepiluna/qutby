<x-filament-panels::page>
    @php
        $cards = $this->getCards();
        $summary = $this->getSummary();

        $fmtQty = fn ($value) => (float) $value > 0
            ? number_format((float) $value, 0, ',', '.')
            : '-';

        $fmtRp = fn ($value) => (float) $value > 0
            ? 'Rp ' . number_format((float) $value, 0, ',', '.')
            : '-';

        $fmtNominal = fn ($value) => (float) $value > 0
            ? number_format((float) $value, 0, ',', '.')
            : '-';
    @endphp

    <style>
        .kartu-stok-average-table {
            border-collapse: collapse;
            border: 2px solid #111827;
        }

        .kartu-stok-average-table th,
        .kartu-stok-average-table td {
            border: 1px solid #111827;
        }

        .kartu-stok-average-table thead th {
            color: #000000;
            font-weight: 800;
            text-transform: none;
        }

        .dark .kartu-stok-average-table,
        .dark .kartu-stok-average-table th,
        .dark .kartu-stok-average-table td {
            border-color: #111827;
        }
    </style>

    <div class="space-y-6">

        {{-- HEADER --}}
        <div class="mb-16 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
            <div class="mx-auto w-max text-center">
                <div class="text-lg font-bold tracking-wide text-gray-900 dark:text-white">
                    LAPORAN KARTU STOK AVERAGE
                </div>

                <div class="text-sm font-semibold text-gray-900 dark:text-gray-200">
                    CV.QUTBY CREATIVINDO
                </div>

                <div class="text-sm text-gray-600 dark:text-gray-400">
                    PERIODE: {{ $this->getPeriodeLabel() }}
                </div>
            </div>
        </div>

        {{-- FILTER --}}
        <div class="mb-6">

            <div style="
                display:grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap:24px;
            ">

                {{-- BULAN --}}
                <div>
                    <label style="
                        display:block;
                        margin-bottom:8px;
                        font-size:14px;
                        font-weight:600;
                        color:#374151;
                    ">
                        Bulan
                    </label>

                    <select
                        wire:model.live="bulan"
                        style="
                            width:100%;
                            height:46px;
                            border:1px solid #d1d5db;
                            border-radius:12px;
                            padding:0 14px;
                            background:#fff;
                            font-size:14px;
                        "
                    >
                        @foreach([
                            '01'=>'Januari',
                            '02'=>'Februari',
                            '03'=>'Maret',
                            '04'=>'April',
                            '05'=>'Mei',
                            '06'=>'Juni',
                            '07'=>'Juli',
                            '08'=>'Agustus',
                            '09'=>'September',
                            '10'=>'Oktober',
                            '11'=>'November',
                            '12'=>'Desember'
                        ] as $v => $l)

                            <option value="{{ $v }}">
                                {{ $l }}
                            </option>

                        @endforeach
                    </select>
                </div>

                {{-- TAHUN --}}
                <div>
                    <label style="
                        display:block;
                        margin-bottom:8px;
                        font-size:14px;
                        font-weight:600;
                        color:#374151;
                    ">
                        Tahun
                    </label>

                    <select
                        wire:model.live="tahun"
                        style="
                            width:100%;
                            height:46px;
                            border:1px solid #d1d5db;
                            border-radius:12px;
                            padding:0 14px;
                            background:#fff;
                            font-size:14px;
                        "
                    >
                        @for($y = now()->year; $y >= now()->year - 5; $y--)

                            <option value="{{ $y }}">
                                {{ $y }}
                            </option>

                        @endfor
                    </select>
                </div>

                {{-- BARANG --}}
                <div>
                    <label style="
                        display:block;
                        margin-bottom:8px;
                        font-size:14px;
                        font-weight:600;
                        color:#374151;
                    ">
                        Barang
                    </label>

                    <select
                        wire:model.live="barangId"
                        style="
                            width:100%;
                            height:46px;
                            border:1px solid #d1d5db;
                            border-radius:12px;
                            padding:0 14px;
                            background:#fff;
                            font-size:14px;
                        "
                    >
                        <option value="">
                            Semua Barang
                        </option>

                        @foreach($this->getBarangOptions() as $id => $namaBarang)

                            <option value="{{ $id }}">
                                {{ $namaBarang }}
                            </option>

                        @endforeach
                    </select>
                </div>

            </div>
        </div>

        {{-- SUMMARY --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <div class="rounded-2xl bg-blue-50 p-5">
                <div class="text-xs uppercase font-semibold text-blue-600">
                    Total Pembelian
                </div>

                <div class="mt-2 text-2xl font-bold text-blue-700">
                    {{ $fmtRp($summary['total_pembelian'] ?? 0) }}
                </div>
            </div>

            <div class="rounded-2xl bg-red-50 p-5">
                <div class="text-xs uppercase font-semibold text-red-600">
                    Total HPP
                </div>

                <div class="mt-2 text-2xl font-bold text-red-700">
                    {{ $fmtRp($summary['total_hpp'] ?? 0) }}
                </div>
            </div>

            <div class="rounded-2xl bg-emerald-50 p-5">
                <div class="text-xs uppercase font-semibold text-emerald-600">
                    Persediaan Akhir
                </div>

                <div class="mt-2 text-2xl font-bold text-emerald-700">
                    {{ $fmtRp($summary['nilai_persediaan_akhir'] ?? 0) }}
                </div>
            </div>

            <div class="rounded-2xl bg-amber-50 p-5">
                <div class="text-xs uppercase font-semibold text-amber-600">
                    Metode
                </div>

                <div class="mt-2 text-2xl font-bold text-amber-700">
                    {{ $summary['metode'] ?? '-' }}
                </div>
            </div>

        </div>

        {{-- CONTENT --}}
        @if(empty($cards))

            <div class="bg-white dark:bg-gray-900 rounded-3xl py-20 text-center shadow-sm">

                <div class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                    Belum ada data transaksi
                </div>

                <div class="mt-2 text-sm text-gray-500">
                    Tidak ditemukan transaksi stok pada periode ini.
                </div>

            </div>

        @else

            <div class="space-y-8">

                @foreach($cards as $card)

                    <div class="rounded-3xl overflow-hidden bg-white dark:bg-gray-900 shadow-sm">

                        {{-- HEADER BARANG --}}
                        <div class="px-6 py-5 bg-gray-50 dark:bg-gray-800">

                            <div class="flex items-center justify-between flex-wrap gap-4">

                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                        {{ $card['barang']->nama_barang ?? '-' }}
                                    </h3>

                                    <div class="text-sm text-gray-500 mt-1">
                                        Kode Barang:
                                        {{ $card['barang']->kode_barang ?? '-' }}
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-3">

                                    <div class="px-4 py-2 rounded-full bg-blue-100 text-blue-700 text-sm font-semibold">
                                        Stok:
                                        {{ $fmtQty($card['stok_akhir'] ?? 0) }}
                                    </div>

                                    <div class="px-4 py-2 rounded-full bg-emerald-100 text-emerald-700 text-sm font-semibold">
                                        Average:
                                        {{ $fmtRp($card['harga_rata_rata_akhir'] ?? 0) }}
                                    </div>

                                </div>

                            </div>

                        </div>

                        {{-- TABLE --}}
                        <div class="overflow-x-auto">

                            <table class="kartu-stok-average-table w-full min-w-[1040px] text-sm">

                                <thead>

                                    <tr>

                                        <th rowspan="2" class="px-4 py-2 text-left align-top">
                                            TANGGAL
                                        </th>

                                        <th colspan="3" class="px-4 py-2 text-center">
                                            pembelian
                                        </th>

                                        <th colspan="3" class="px-4 py-2 text-center">
                                            harga pokok penjualan
                                        </th>

                                        <th colspan="3" class="px-4 py-2 text-center">
                                            persediaan
                                        </th>

                                    </tr>

                                    <tr>

                                        <th class="px-3 py-2 text-left">
                                            Unit
                                        </th>

                                        <th class="px-3 py-2 text-left">
                                            harga unit
                                        </th>

                                        <th class="px-3 py-2 text-left">
                                            total
                                        </th>

                                        <th class="px-3 py-2 text-left">
                                            unit
                                        </th>

                                        <th class="px-3 py-2 text-left">
                                            harga unit
                                        </th>

                                        <th class="px-3 py-2 text-left">
                                            total
                                        </th>

                                        <th class="px-3 py-2 text-left">
                                            unit
                                        </th>

                                        <th class="px-3 py-2 text-left">
                                            harga unit
                                        </th>

                                        <th class="px-3 py-2 text-left">
                                            total
                                        </th>

                                    </tr>

                                </thead>

                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">

                                    @foreach($card['rows'] ?? [] as $row)

                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">

                                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                                {{ $row['tanggal'] ?? '-' }}
                                            </td>

                                            {{-- PEMBELIAN --}}
                                            <td class="px-3 py-4 {{ ($row['jenis'] ?? '') === 'awal' ? 'text-left font-semibold uppercase' : 'text-center' }}">
                                                @if(($row['jenis'] ?? '') === 'awal')
                                                    SALDO AWAL
                                                @else
                                                    {{ $fmtQty($row['pembelian']['qty'] ?? 0) }}
                                                @endif
                                            </td>

                                            <td class="px-3 py-4 text-right">
                                                {{ $fmtRp($row['pembelian']['harga'] ?? 0) }}
                                            </td>

                                            <td class="px-3 py-4 text-right font-medium">
                                                {{ $fmtRp($row['pembelian']['total'] ?? 0) }}
                                            </td>

                                            {{-- HPP --}}
                                            <td class="px-3 py-4 text-center">
                                                {{ $fmtQty($row['hpp']['qty'] ?? 0) }}
                                            </td>

                                            <td class="px-3 py-4 text-right">
                                                {{ $fmtRp($row['hpp']['harga'] ?? 0) }}
                                            </td>

                                            <td class="px-3 py-4 text-right font-semibold">
                                                {{ $fmtRp($row['hpp']['total'] ?? 0) }}
                                            </td>

                                            {{-- PERSEDIAAN --}}
                                            <td class="px-3 py-4 text-center font-semibold">
                                                {{ $fmtQty($row['persediaan']['qty'] ?? 0) }}
                                            </td>

                                            <td class="px-3 py-4 text-right font-semibold">

                                                {{ $fmtRp($row['persediaan']['harga'] ?? 0) }}

                                            </td>

                                            <td class="px-3 py-4 text-right font-bold">
                                                {{ $fmtRp($row['persediaan']['total'] ?? 0) }}
                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                                <tfoot>

                                    <tr class="bg-gray-100 dark:bg-gray-800 font-bold">

                                        <td class="px-4 py-4 text-center">
                                            TOTAL
                                        </td>

                                        <td class="px-4 py-4 text-center">
                                            {{ $fmtQty($card['total_pembelian_unit'] ?? 0) }}
                                        </td>

                                        <td class="px-4 py-4">
                                            -
                                        </td>

                                        <td class="px-4 py-4 text-right">
                                            {{ $fmtRp($card['total_pembelian'] ?? 0) }}
                                        </td>

                                        <td class="px-4 py-4 text-center">
                                            {{ $fmtQty($card['total_jual_unit'] ?? 0) }}
                                        </td>

                                        <td class="px-4 py-4">
                                            -
                                        </td>

                                        <td class="px-4 py-4 text-right">
                                            {{ $fmtRp($card['total_hpp'] ?? 0) }}
                                        </td>

                                        <td class="px-4 py-4 text-center">
                                            {{ $fmtQty($card['stok_akhir'] ?? 0) }}
                                        </td>

                                        <td class="px-4 py-4 text-right">
                                            {{ $fmtRp($card['harga_rata_rata_akhir'] ?? 0) }}
                                        </td>

                                        <td class="px-4 py-4 text-right">
                                            {{ $fmtRp($card['persediaan_akhir'] ?? 0) }}
                                        </td>

                                    </tr>

                                </tfoot>

                            </table>

                        </div>

                        {{-- FOOTER --}}
                        <div class="px-6 py-5 bg-gray-50 dark:bg-gray-800 flex flex-wrap gap-5 text-sm">

                            <div>
                                Saldo Awal:
                                <span class="font-bold">
                                    {{ $fmtRp($card['saldo_awal_nilai'] ?? 0) }}
                                </span>
                            </div>

                            <div>
                                Pembelian:
                                <span class="font-bold text-blue-600">
                                    {{ $fmtRp($card['total_pembelian'] ?? 0) }}
                                </span>
                            </div>

                            <div>
                                HPP:
                                <span class="font-bold text-red-600">
                                    {{ $fmtRp($card['total_hpp'] ?? 0) }}
                                </span>
                            </div>

                            <div>
                                Persediaan Akhir:
                                <span class="font-bold text-emerald-700">
                                    {{ $fmtRp($card['persediaan_akhir'] ?? 0) }}
                                </span>
                            </div>

                            <div class="ml-auto">
                                @if($card['valid'] ?? false)
                                    <span class="px-4 py-2 rounded-full bg-emerald-100 text-emerald-700 font-semibold text-xs">
                                        VALID
                                    </span>
                                @else
                                    <span class="px-4 py-2 rounded-full bg-red-100 text-red-700 font-semibold text-xs">
                                        TIDAK VALID
                                    </span>
                                @endif
                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

        @endif

    </div>
</x-filament-panels::page>
