<div>
    <h1 class="text-2xl font-bold mb-4">{{ $project->name }}</h1>
    @if($project->description)
        <div class="text-sm text-gray-500 mb-4">{{ $project->description }}</div>
    @endif
    <div class="space-y-3">
        @forelse($boards as $board)
            <a href="{{ route('cms.boards.show', $board->id) }}" class="block p-4 bg-white rounded border hover:bg-gray-50" wire:navigate>
                <div class="font-medium">{{ $board->name }}</div>
                @if($board->description)
                    <div class="text-sm text-gray-500">{{ $board->description }}</div>
                @endif
            </a>
        @empty
            <div class="text-sm text-gray-500">Dieses Projekt hat noch keine Boards.</div>
        @endforelse
    </div>
</div>

