<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex justify-end gap-x-3">
            @foreach ($this->getFormActions() as $action)
                {{ $action }}
            @endforeach
        </div>

        <x-filament-actions::modals />
    </form>
</x-filament-panels::page>
