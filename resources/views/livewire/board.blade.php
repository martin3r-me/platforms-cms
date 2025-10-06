<div>
    <h1 class="text-2xl font-bold mb-4">{{ $board->name }}</h1>
    @if($board->description)
        <div class="text-sm text-gray-500 mb-4">{{ $board->description }}</div>
    @endif
    <div class="space-y-3">
        @forelse($contents as $content)
            <a href="{{ route('cms.contents.show', $content->id) }}" class="block p-4 bg-white rounded border hover:bg-gray-50" wire:navigate>
                <div class="font-medium">{{ $content->title }}</div>
                @if($content->excerpt)
                    <div class="text-sm text-gray-500">{{ $content->excerpt }}</div>
                @endif
            </a>
        @empty
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="text-sm text-gray-500">Noch keine Inhalte in diesem Board.</div>
            </div>
        @endforelse
    </div>
</div>


