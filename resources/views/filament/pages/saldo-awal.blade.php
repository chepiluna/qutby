<x-filament-panels::page>

    <form wire:submit.prevent="simpan">
        
        {{ $this->form }}

        <div class="mt-4 flex justify-end">
            <x-filament::button type="submit">
                Simpan Saldo Awal
            </x-filament::button>
        </div>

    </form>

</x-filament-panels::page>