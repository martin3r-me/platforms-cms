<x-ui-modal wire:model="show">
    <x-slot name="title">Board-Slot bearbeiten</x-slot>
    <div class="space-y-3">
        <x-ui-input-text label="Name" wire:model="name"/>
    </div>
    <x-slot name="footer">
        <x-ui-button variant="neutral" wire:click="$set('show', false)">Abbrechen</x-ui-button>
        <x-ui-button variant="primary" wire:click="save">Speichern</x-ui-button>
    </x-slot>
</x-ui-modal>

