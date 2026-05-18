<x-filament-panels::page>
    {{-- HEADER LAPORAN --}}
    <div class="text-center mb-6">
        <h1 class="text-xl font-bold">
            LAPORAN JURNAL UMUM
        </h1>

        <h2 class="text-base font-semibold">
            QUTBY COLLECTION
        </h2>

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

        <p class="text-sm text-gray-600">
            @if ($this->bulan && $this->tahun)
                Periode
                {{ $namaBulan[$this->bulan] }}
                {{ $this->tahun }}
            @elseif ($this->tahun)
                Tahun {{ $this->tahun }}
            @else
                Semua Periode
            @endif
        </p>
    </div>

    {{-- FORM FILTER --}}
    {{ $this->form }}

    <br>

    {{-- TABEL FILAMENT --}}
    {{ $this->table }}
</x-filament-panels::page>