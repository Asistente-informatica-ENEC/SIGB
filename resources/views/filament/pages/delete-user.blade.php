<x-filament::page>
        <form wire:submit.prevent="submit" class="space-y-6">
    {{ $this->form }}
    <x-filament::button wire:click="submit" color="danger" class="mt-4">
        Eliminar Usuario
    </x-filament::button>
        </form>
</x-filament::page>
