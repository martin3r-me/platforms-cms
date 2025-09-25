<div class="h-full overflow-y-auto p-6">
    <!-- Header mit Datum -->
    <div class="mb-6">
        <div class="d-flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">CMS Dashboard</h1>
                <p class="text-gray-600">{{ $currentDay }}, {{ $currentDate }}</p>
            </div>
            <div class="d-flex items-center gap-4"></div>
        </div>
    </div>

    <!-- CMS Statistiken -->
    <div class="mb-4">
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <div class="grid grid-cols-4 gap-4">
                <x-ui-dashboard-tile title="Projekte" :count="$stats['projects_total']" icon="folder" variant="primary" size="lg" />
                <x-ui-dashboard-tile title="Boards" :count="$stats['boards_total']" icon="rectangle-stack" variant="info" size="lg" />
                <x-ui-dashboard-tile title="Inhalte" :count="$stats['contents_total']" icon="document-text" variant="neutral" size="lg" />
                <x-ui-dashboard-tile title="Veröffentlicht" :count="$stats['contents_published']" subtitle="Drafts: {{ $stats['contents_draft'] }}" icon="check-circle" variant="success" size="lg" />
            </div>
        </div>
    </div>

    <!-- Abstand -->
    <div class="mb-4"></div>

    <!-- Letzte Inhalte -->
    <div class="grid grid-cols-2 gap-6 mb-8">
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Zuletzt erstellte Inhalte</h3>
            <div class="space-y-2">
                @forelse($recentContents as $c)
                    <a href="{{ route('cms.contents.show', $c->id) }}" class="block p-3 bg-white rounded border hover:bg-gray-50" wire:navigate>
                        <div class="font-medium">{{ $c->title }}</div>
                        <div class="text-xs text-gray-500">Status: {{ $c->status }} @if($c->published_at) • {{ $c->published_at->format('d.m.Y') }} @endif</div>
                    </a>
                @empty
                    <div class="text-sm text-gray-500">Keine Inhalte vorhanden.</div>
                @endforelse
            </div>
        </div>
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Projekte & Boards</h3>
            <div class="grid grid-cols-2 gap-3">
                @foreach($projects as $p)
                    <div class="p-3 bg-white rounded border">
                        <div class="font-medium">{{ $p->name }}</div>
                        <div class="text-xs text-gray-500">Projekt</div>
                    </div>
                @endforeach
                @foreach($boards as $b)
                    <a href="{{ route('cms.boards.show', $b->id) }}" class="p-3 bg-white rounded border hover:bg-gray-50" wire:navigate>
                        <div class="font-medium">{{ $b->name }}</div>
                        <div class="text-xs text-gray-500">Board</div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Entfernt: Team-Mitglieder-Block (nicht CMS-relevant) -->

    <!-- Platz für Erweiterungen -->
</div>