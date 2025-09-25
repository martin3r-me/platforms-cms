<x-ui-modal wire:model="modalShow">
    <x-slot name="title">Kundenbezug</x-slot>
    <div class="space-y-3">
        <div>
            <x-ui-label>Unternehmen</x-ui-label>
            <x-ui-input-select name="company_id" :options="$companyOptions" wire:model="companyId" placeholder="Firma wählen" />
            <div class="text-xs text-muted mt-1">Aktuell: {{ $companyDisplay ?? '—' }}</div>
        </div>
        <div>
            <x-ui-input-text name="company_search" wire:model.debounce.400ms="companySearch" placeholder="Suche Unternehmen..." />
        </div>
        <div class="border-t pt-3">
            <x-ui-label>Kunde (polymorph)</x-ui-label>
            <div class="d-flex gap-2">
                <x-ui-input-select :options="[
                    ['value' => 'crm.companies', 'label' => 'Firma'],
                    ['value' => 'crm.contacts', 'label' => 'Kontakt'],
                ]" name="customer_model" wire:model="customerModel" placeholder="Typ wählen"/>
                <x-ui-input-number name="customer_id" wire:model="customerId" placeholder="ID" />
            </div>
            <div class="text-xs text-muted mt-1">Aktuell: {{ $customerDisplay ?? '—' }}</div>
        </div>
    </div>
    <x-slot name="footer">
        <x-ui-button variant="neutral" wire:click="closeModal">Abbrechen</x-ui-button>
        <x-ui-button variant="primary" wire:click="saveCompany">Speichern</x-ui-button>
    </x-slot>
</x-ui-modal>

 

