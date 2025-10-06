<div>
    <h1 class="text-2xl font-bold mb-4">Boards</h1>
    <div class="space-y-4">
        @forelse($boards as $board)
            <a href="{{ route('cms.boards.show', $board->id) }}" class="block p-4 bg-white rounded border hover:bg-gray-50" wire:navigate>
                <div class="d-flex items-center justify-between">
                    <div>
                        <div class="font-medium">{{ $board->name }}</div>
                        @if($board->description)
                            <div class="text-sm text-gray-500">{{ $board->description }}</div>
                        @endif
                    </div>
                    @svg('heroicon-o-chevron-right', 'w-5 h-5 text-gray-400')
                </div>
            </a>
        @empty
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="text-sm text-gray-500">Noch keine Boards vorhanden.</div>
            </div>
        @endforelse
    </div>
</div>


