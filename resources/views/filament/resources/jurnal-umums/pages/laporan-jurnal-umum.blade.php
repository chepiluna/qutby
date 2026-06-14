<x-filament-panels::page>
    {{-- HEADER LAPORAN --}}
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="mx-auto w-max space-y-1 text-center">
            <div class="text-xl font-bold text-gray-950 dark:text-white">
                CV.QUTBY CREATIVINDO
            </div>

            <div class="text-lg font-semibold tracking-wide text-gray-950 dark:text-white">
                LAPORAN JURNAL UMUM
            </div>

            @php
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
            @endphp

            <div class="text-sm text-gray-600 dark:text-gray-300">
                @if ($this->bulan && $this->tahun)
                    PERIODE:
                    {{ $namaBulan[$this->bulan] }}
                    {{ $this->tahun }}
                @elseif ($this->tahun)
                    PERIODE: Tahun {{ $this->tahun }}
                @else
                    PERIODE: Semua Periode
                @endif
            </div>
        </div>
    </div>

    {{-- FORM FILTER --}}
    {{ $this->form }}

    <br>

    {{-- TABEL FILAMENT --}}
    {{ $this->table }}
</x-filament-panels::page>
