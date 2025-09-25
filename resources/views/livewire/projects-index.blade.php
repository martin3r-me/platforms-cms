<div>
    <h1 class="text-2xl font-bold mb-4">Projekte</h1>
    <div class="space-y-3">
        @forelse($projects as $project)
            <a href="{{ route('cms.projects.boards', $project->id) }}" class="block p-4 bg-white rounded border hover:bg-gray-50" wire:navigate>
                <div class="font-medium">{{ $project->name }}</div>
                @if($project->description)
                    <div class="text-sm text-gray-500">{{ $project->description }}</div>
                @endif
            </a>
        @empty
            <div class="text-sm text-gray-500">Keine Projekte vorhanden.</div>
        @endforelse
    </div>
</div>

