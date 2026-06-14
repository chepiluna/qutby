<x-filament-panels::page>
    <style>
        .laporan-pembelian-filters {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1.75rem;
        }

        @media (max-width: 767px) {
            .laporan-pembelian-filters {
                grid-template-columns: 1fr;
            }
        }

        .laporan-pembelian-table {
            border-collapse: collapse;
            border: 1px solid #9ca3af;
        }

        .laporan-pembelian-table th,
        .laporan-pembelian-table td {
            border: 1px solid #9ca3af;
        }
    </style>

    @php
        $rows = $this->getLaporanRows();
        $grandTotal = $rows->sum('total');
    @endphp

    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="mx-auto w-max text-center">
            <div class="text-xl font-bold text-gray-950 dark:text-white">
                CV.QUTBY CREATIVINDO
            </div>

            <div class="text-lg font-semibold tracking-wide text-gray-950 dark:text-white">
                LAPORAN PEMBELIAN
            </div>

            <div class="text-sm text-gray-600 dark:text-gray-300">
                PERIODE: {{ $this->getPeriodeLabel() }}
            </div>
        </div>
    </div>

    <div class="laporan-pembelian-filters mb-6">
        <div>
            <label class="mb-2 block text-sm font-semibold text-gray-950 dark:text-white">
                Vendor<span class="text-danger-600">*</span>
            </label>
            <select wire:model.live="vendor_id"
                class="fi-select-input block h-11 w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="">-- Semua Vendor --</option>
                @foreach($this->getVendorOptions() as $v)
                    <option value="{{ $v->id }}" @selected($vendor_id == $v->id)>{{ $v->nama_vendor }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-gray-950 dark:text-white">
                Bulan<span class="text-danger-600">*</span>
            </label>
            <select wire:model.live="bulan"
                class="fi-select-input block h-11 w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                @foreach(['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $k => $v)
                    <option value="{{ $k }}" @selected($bulan === $k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-gray-950 dark:text-white">
                Tahun<span class="text-danger-600">*</span>
            </label>
            <select wire:model.live="tahun"
                class="fi-select-input block h-11 w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                @for($y = now()->year; $y >= now()->year - 4; $y--)
                    <option value="{{ $y }}" @selected($tahun == $y)>{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="flex flex-col gap-4 border-b border-gray-200 px-7 py-5 dark:border-gray-800 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-950 dark:text-white">
                    Daftar Pembelian
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Filter berdasarkan vendor, bulan, dan tahun.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ $this->getPdfUrl() }}" target="_blank"
                    style="display:inline-flex; align-items:center; justify-content:center; min-height:36px; padding:0 14px; background:#dc2626; color:#ffffff; font-size:12px; font-weight:700; border-radius:8px; text-decoration:none; box-shadow:0 1px 2px rgba(15,23,42,.12);">
                    Cetak PDF
                </a>
            </div>
        </div>

        <div class="px-7 py-7">
            <div class="overflow-x-auto">
                <table class="laporan-pembelian-table w-full min-w-[920px] text-sm">
                    <thead class="bg-red-50 text-red-800">
                        <tr class="border-b border-gray-950">
                            <th class="px-3 py-3 text-center font-bold">Tanggal Pembelian</th>
                            <th class="px-3 py-3 text-center font-bold">No. PO</th>
                            <th class="px-3 py-3 text-center font-bold">Vendor</th>
                            <th class="px-3 py-3 text-center font-bold">Nama Barang</th>
                            <th class="px-3 py-3 text-center font-bold">Jumlah</th>
                            <th class="px-3 py-3 text-center font-bold">Harga Satuan</th>
                            <th class="px-3 py-3 text-center font-bold">Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($rows as $row)
                            <tr class="border-b border-gray-200 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800/50">
                                <td class="px-3 py-3 text-gray-950 dark:text-gray-100">
                                    {{ $row['tanggal'] instanceof \Carbon\Carbon ? $row['tanggal']->format('d/m/Y') : \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}
                                </td>
                                <td class="px-3 py-3 text-gray-950 dark:text-gray-100">{{ $row['nomor_po'] }}</td>
                                <td class="px-3 py-3 text-gray-950 dark:text-gray-100">{{ $row['vendor'] }}</td>
                                <td class="px-3 py-3 text-gray-950 dark:text-gray-100">{{ $row['nama_barang'] }}</td>
                                <td class="px-3 py-3 text-right text-gray-950 dark:text-gray-100">{{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                                <td class="px-3 py-3 text-right text-gray-950 dark:text-gray-100">Rp {{ number_format($row['harga_satuan'], 0, ',', '.') }}</td>
                                <td class="px-3 py-3 text-right font-semibold text-gray-950 dark:text-gray-100">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-6 text-center text-gray-500">
                                    Tidak ada data pembelian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    <tfoot class="bg-gray-50 dark:bg-gray-800/70">
                        <tr>
                            <td colspan="6" class="px-3 py-4 text-center font-semibold text-gray-950 dark:text-white">
                                TOTAL PEMBELIAN BULANAN
                            </td>
                            <td class="px-3 py-4 text-right font-bold text-danger-600">
                                Rp {{ number_format($grandTotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
