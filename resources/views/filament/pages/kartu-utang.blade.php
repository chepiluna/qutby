<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-800 shadow rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="p-5 border-b border-gray-200 dark:border-gray-700">
            <div style="display:flex; justify-content:flex-end; margin-bottom:1rem;">
                <button wire:click="unduhPdf" type="button"
                    style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; background-color:#dc2626; color:#fff; font-size:14px; font-weight:600; border-radius:8px; border:none; cursor:pointer;"
                    onmouseover="this.style.backgroundColor='#b91c1c'"
                    onmouseout="this.style.backgroundColor='#dc2626'">
                    <svg style="width:16px;height:16px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Cetak PDF
                </button>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem;">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Vendor</label>
                    <select wire:model.live="vendor_id"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-colors cursor-pointer py-2 pl-3 pr-8">
                        <option value="">Pilih Vendor</option>
                        @foreach($this->getVendorOptions() as $id => $nama)
                            <option value="{{ $id }}">{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Bulan</label>
                    <select wire:model.live="bulan"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-colors cursor-pointer py-2 pl-3 pr-8">
                        @foreach(['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni',
                                   '07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $v => $l)
                            <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Tahun</label>
                    <select wire:model.live="tahun"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-colors cursor-pointer py-2 pl-3 pr-8">
                        @for($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>

        @php
            $data = $this->getLaporanData();
        @endphp

        <div class="p-6 overflow-x-auto print:p-0 print:m-0">
            @if(empty($data))
                <div class="text-center py-12 text-gray-500">
                    Silakan pilih <b>Vendor</b> terlebih dahulu untuk melihat Kartu Utang.
                </div>
            @else
                <div class="mb-4">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white pb-1">Kartu Utang</h2>
                    <p class="text-md text-gray-800 dark:text-gray-200 font-semibold pb-1">Vendor : {{ $data['vendor'] ? $data['vendor']->nama_vendor : '-' }}</p>
                    <p class="text-md text-gray-800 dark:text-gray-200 font-semibold pb-1">Nomer Rekening : {{ $this->getNomorRekeningLabel() }}</p>
                </div>

                <table class="w-full text-sm text-left text-gray-700 dark:text-gray-300 border-collapse border border-gray-400 dark:border-gray-500">
                    <thead class="text-xs font-semibold text-gray-900 dark:text-gray-100 bg-gray-300 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 border border-gray-400 dark:border-gray-500 text-center">Tanggal</th>
                            <th class="px-4 py-2 border border-gray-400 dark:border-gray-500 text-center">Keterangan</th>
                            <th class="px-4 py-2 border border-gray-400 dark:border-gray-500 text-center">Ref</th>
                            <th class="px-4 py-2 border border-gray-400 dark:border-gray-500 text-center">Debet</th>
                            <th class="px-4 py-2 border border-gray-400 dark:border-gray-500 text-center">Kredit</th>
                            <th class="px-4 py-2 border border-gray-400 dark:border-gray-500 text-center">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-2 border border-gray-400 dark:border-gray-500 text-center font-medium">{{ '01 ' . $this->getPeriodeLabel() }}</td>
                            <td class="px-4 py-2 border border-gray-400 dark:border-gray-500">Saldo Awal</td>
                            <td class="px-4 py-2 border border-gray-400 dark:border-gray-500 text-center"></td>
                            <td class="px-4 py-2 border border-gray-400 dark:border-gray-500 text-center">-</td>
                            <td class="px-4 py-2 border border-gray-400 dark:border-gray-500 text-center">-</td>
                            <td class="px-4 py-2 border border-gray-400 dark:border-gray-500 text-center font-semibold">
                                Rp {{ number_format($data['saldo_awal'], 0, ',', '.') }}
                            </td>
                        </tr>

                        @forelse($data['rows'] as $row)
                            <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-2 border border-gray-400 dark:border-gray-500 text-center">{{ $row['tanggal'] }}</td>
                                <td class="px-4 py-2 border border-gray-400 dark:border-gray-500">{{ $row['keterangan'] }}</td>
                                <td class="px-4 py-2 border border-gray-400 dark:border-gray-500 text-center">{{ $row['ref'] }}</td>
                                <td class="px-4 py-2 border border-gray-400 dark:border-gray-500 text-center">
                                    {{ $row['debet'] > 0 ? 'Rp '.number_format($row['debet'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-4 py-2 border border-gray-400 dark:border-gray-500 text-center">
                                    {{ $row['kredit'] > 0 ? 'Rp '.number_format($row['kredit'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-4 py-2 border border-gray-400 dark:border-gray-500 text-center font-semibold">
                                    {{ $row['saldo'] != 0 ? 'Rp '.number_format($row['saldo'], 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr class="bg-white dark:bg-gray-800">
                                <td colspan="6" class="px-4 py-6 text-center text-gray-500 italic border border-gray-400 dark:border-gray-500">
                                    Tidak ada transaksi di bulan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-filament-panels::page>
