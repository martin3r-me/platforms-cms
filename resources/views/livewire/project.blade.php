<div>
    <h1 class="text-2xl font-bold mb-4">{{ $project->name }}</h1>
    @if($project->description)
        <div class="text-sm text-gray-500 mb-4">{{ $project->description }}</div>
    @endif
    <div class="mb-4">
        <x-ui-button variant="success" size="sm" wire:click="createBoard">+ Board anlegen</x-ui-button>
    </div>

    <!-- Kanban Board analog Planner -->
    <div class="flex-grow overflow-x-auto">
        <x-ui-kanban-board wire:sortable="updateSlotOrder" wire:sortable-group="updateContentOrder">
            @forelse($boards as $board)
                @foreach(($board->kanban ?? collect()) as $column)
                <x-ui-kanban-column :title="$column->label" :sortable-id="$column->id">
                    <x-slot name="extra">
                        <div class="d-flex gap-1">
                            @if($column->isBacklog)
                                <x-ui-button variant="success-outline" size="sm" class="w-full" wire:click="createContent({{ $board->id }})">+ Neuer Inhalt</x-ui-button>
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
            @empty
                <x-ui-kanban-column :title="'Keine Boards'">
                    <div class="text-xs text-gray-400 p-2">Dieses Projekt hat noch keine Boards.</div>
                </x-ui-kanban-column>
            @endforelse
        </x-ui-kanban-board>
    </div>

    <livewire:cms.project-settings-modal/>
    <livewire:cms.board-slot-settings-modal/>
</div>

