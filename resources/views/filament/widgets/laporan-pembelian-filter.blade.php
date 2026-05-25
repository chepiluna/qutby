<x-filament-widgets::widget>
    <div class="bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-700/80 rounded-xl shadow-sm p-4">
        <div class="flex items-end gap-3 flex-wrap">
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Bulan</label>
                <select wire:model.live="bulan"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 py-2 pl-3 pr-8">
                    @foreach(['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni',
                               '07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $v => $l)
                        <option value="{{ $v }}" @selected($bulan === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1 min-w-[110px]">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Tahun</label>
                <select wire:model.live="tahun"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 py-2 pl-3 pr-8">
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" @selected((int)$tahun === $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Vendor</label>
                <select wire:model.live="vendor_id"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 py-2 pl-3 pr-8">
                    <option value="">-- Semua Vendor --</option>
                    @foreach($this->getVendorOptions() as $id => $nama)
                        <option value="{{ $id }}" @selected((string)$vendor_id === (string)$id)>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
