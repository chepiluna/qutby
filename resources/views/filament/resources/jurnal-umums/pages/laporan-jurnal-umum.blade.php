<x-filament-panels::page>
    {{-- HEADER LAPORAN --}}
    <div class="text-center mb-6">
        <h1 class="text-xl font-bold">
            LAPORAN JURNAL UMUM
        </h1>

        <h2 class="text-base font-semibold">
            QUTBY COLLECTION
        </h2>

        <p class="text-sm text-gray-600">
            @if ($this->from && $this->until)
                Periode
                {{ \Carbon\Carbon::parse($this->from)->translatedFormat('F Y') }}
            @else
                Semua Periode
            @endif
        </p>
    </div>

    {{-- TABEL FILAMENT --}}
    {{ $this->table }}
</x-filament-panels::page>
