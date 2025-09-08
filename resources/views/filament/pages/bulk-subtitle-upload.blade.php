<x-filament-panels::page>
    <form wire:submit="submit">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" size="lg">
                <x-heroicon-o-cloud-arrow-up class="w-5 h-5 mr-2" />
                Upload Subtitles to Selected Streams
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
