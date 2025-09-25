<div class="h-full d-flex">
    <!-- Linke Spalte (Info & Aktionen & Boards) -->
    <div class="w-80 border-r border-muted p-4 flex-shrink-0">
        <div class="mb-6">
            <div class="d-flex justify-between items-start">
                <h3 class="text-lg font-semibold">{{ $project->name }}</h3>
                <x-ui-button variant="info" size="sm" @click="$dispatch('open-modal-cms-project-settings', { projectId: {{ $project->id }} })">
                    <div class="d-flex items-center gap-2">
                        @svg('heroicon-o-information-circle', 'w-4 h-4')
                        Info
                    </div>
                </x-ui-button>
            </div>
            <div class="mt-2 d-flex items-center gap-2">
                <x-ui-badge>
                    Kunde: 
                    @php($cp = $project->customerProject)
                    @if($cp && $cp->customer_model === 'crm.companies' && $cp->customer_id)
                        {{ app(\Platform\Core\Contracts\CrmCompanyResolverInterface::class)->displayName($cp->customer_id) }}
                    @elseif($cp && $cp->customer_model === 'crm.contacts' && $cp->customer_id)
                        {{ app(\Platform\Core\Contracts\CrmContactResolverInterface::class)->displayName($cp->customer_id) }}
                    @elseif($cp && $cp->company_id)
                        {{ app(\Platform\Core\Contracts\CrmCompanyResolverInterface::class)->displayName($cp->company_id) }}
                    @else
                        Keiner
                    @endif
                </x-ui-badge>
                <x-ui-button variant="neutral-outline" size="xs" @click="$dispatch('open-modal-cms-customer-project', { projectId: {{ $project->id }} })">
                    Kunde zuordnen
                </x-ui-button>
                @if($project->customerProject?->customer_url)
                    <a href="{{ $project->customerProject->customer_url }}" target="_blank" class="text-xs text-primary hover:underline">Kunde öffnen</a>
                @elseif($project->customerProject?->customer_tool && $project->customerProject?->customer_model && $project->customerProject?->customer_id)
                    @php
                        $tool = $project->customerProject->customer_tool;
                        $model = $project->customerProject->customer_model;
                        $cid = $project->customerProject->customer_id;
                        $url = null;
                        try {
                            // Versuche generischen Tool-Aufruf über CommandRegistry (falls verfügbar)
                            $url = \Platform\Core\Registry\CommandRegistry::buildNavigateUrl($tool, ['model' => $model, 'id' => $cid]);
                        } catch (\Throwable $e) {}
                    @endphp
                    @if($url)
                        <a href="{{ $url }}" target="_blank" class="text-xs text-primary hover:underline">Kunde öffnen</a>
                    @endif
                @endif
            </div>
            <div class="text-sm text-gray-600 mb-4">{{ $project->description ?? 'Keine Beschreibung' }}</div>
            <div class="grid grid-cols-2 gap-2 mb-4">
                <x-ui-dashboard-tile title="Inhalte (offen)" :count="$openCount" icon="document-text" variant="secondary" size="sm" />
            </div>
            <div class="d-flex flex-col gap-2 mb-4">
                <x-ui-button variant="success-outline" size="sm" wire:click="createContent({{ $selectedBoard?->id ?? 0 }})" :disabled="!$selectedBoard">+ Neuer Inhalt</x-ui-button>
                <x-ui-button variant="primary-outline" size="sm" wire:click="createSlot" :disabled="!$selectedBoard">+ Neue Spalte</x-ui-button>
                <x-ui-button variant="neutral-outline" size="sm" wire:click="createBoard">+ Board anlegen</x-ui-button>
            </div>
        </div>

        <div>
            <h4 class="font-medium mb-3">Boards</h4>
            <div class="space-y-1 max-h-60 overflow-y-auto">
                @foreach($boards as $b)
                    <button type="button"
                            wire:click="selectBoard({{ $b->id }})"
                            class="w-full text-left p-2 rounded hover:bg-gray-100 {{ ($selectedBoard?->id === $b->id) ? 'bg-primary text-on-primary' : '' }}">
                        {{ $b->name }}
                    </button>
                @endforeach
                @if($boards->isEmpty())
                    <div class="text-sm text-gray-500 italic">Noch keine Boards</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Rechte Spalte: Kanban des ausgewählten Boards -->
    <div class="flex-grow overflow-x-auto p-4">
        <x-ui-kanban-board wire:sortable="updateSlotOrder" wire:sortable-group="updateContentOrder">
            @if($selectedBoard)
                @foreach(($groups ?? collect()) as $column)
                <x-ui-kanban-column :title="$column->label" :sortable-id="$column->id">
                    <x-slot name="extra">
                        <div class="d-flex gap-1">
                            @if($column->isBacklog)
                                <x-ui-button variant="success-outline" size="sm" class="w-full" wire:click="createContent({{ $selectedBoard->id }})">+ Neuer Inhalt</x-ui-button>
                            @else
                                <x-ui-button variant="primary-outline" size="sm" class="w-full" @click="$dispatch('open-modal-cms-board-slot-settings', { slotId: {{ $column->id }} })">Settings</x-ui-button>
                            @endif
                        </div>
                    </x-slot>
                    @forelse(($column->items ?? []) as $item)
                        <livewire:cms.content-preview-card :content="$item" wire:key="content-preview-{{ $item->id }}" />
                    @empty
                        <div class="text-xs text-gray-400">Keine Inhalte</div>
                    @endforelse
                </x-ui-kanban-column>
                @endforeach
            @else
                <x-ui-kanban-column :title="'Keine Boards'">
                    <div class="text-xs text-gray-400 p-2">Dieses Projekt hat noch keine Boards.</div>
                </x-ui-kanban-column>
            @endif
        </x-ui-kanban-board>
    </div>

    <livewire:cms.project-settings-modal/>
    <livewire:cms.customer-project-settings-modal/>
    <livewire:cms.board-slot-settings-modal/>
</div>

