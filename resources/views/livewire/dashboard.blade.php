<div class="h-full overflow-y-auto p-6">
    <!-- Header mit Datum -->
    <div class="mb-6">
        <div class="d-flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
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

    <!-- Haupt-Statistiken (4x2 Grid) -->
    <div class="grid grid-cols-4 gap-4 mb-8">
        <!-- Projekte -->
        <x-ui-dashboard-tile
            title="Aktive Projekte"
            :count="$activeProjects"
            subtitle="von {{ $totalProjects }}"
            icon="folder"
            variant="primary"
            size="lg"
        />
        
        <!-- Aufgaben -->
        <x-ui-dashboard-tile
            title="Offene Aufgaben"
            :count="$openTasks"
            subtitle="von {{ $totalTasks }}"
            icon="clock"
            variant="warning"
            size="lg"
        />
        
        <!-- Erledigte Aufgaben -->
        <x-ui-dashboard-tile
            title="Erledigte Aufgaben"
            :count="$completedTasks"
            subtitle="diesen Monat: {{ $monthlyCompletedTasks }}"
            icon="check-circle"
            variant="success"
            size="lg"
        />
        
        <!-- Story Points -->
        <x-ui-dashboard-tile
            title="Story Points"
            :count="$openStoryPoints"
            subtitle="erledigt: {{ $completedStoryPoints }}"
            icon="chart-bar"
            variant="info"
            size="lg"
        />
    </div>

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

    <!-- Team-Mitglieder-Übersicht -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Team-Mitglieder Übersicht</h3>
            <p class="text-sm text-gray-600 mt-1">Aufgaben und Story Points pro Team-Mitglied</p>
        </div>
        
        <div class="p-6">
            @if($teamMembers->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($teamMembers as $member)
                        <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition">
                            <div class="d-flex items-center gap-3 mb-3">
                                @if($member['profile_photo_url'])
                                    <img src="{{ $member['profile_photo_url'] }}" 
                                         alt="{{ $member['name'] }}" 
                                         class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 bg-primary text-on-primary rounded-full d-flex items-center justify-center">
                                        <span class="text-sm font-medium">
                                            {{ substr($member['name'], 0, 2) }}
                                        </span>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-gray-900 truncate">{{ $member['name'] }}</h4>
                                    <p class="text-sm text-gray-600 truncate">{{ $member['email'] }}</p>
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <!-- Aufgaben-Statistik -->
                                <div class="d-flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Aufgaben:</span>
                                    <div class="d-flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-900">{{ $member['open_tasks'] }}</span>
                                        <span class="text-xs text-gray-500">/ {{ $member['total_tasks'] }}</span>
                                        @if($member['completed_tasks'] > 0)
                                            <span class="text-xs text-success">✓{{ $member['completed_tasks'] }}</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Story Points-Statistik -->
                                @if($member['total_story_points'] > 0)
                                    <div class="d-flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Story Points:</span>
                                        <div class="d-flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-900">{{ $member['open_story_points'] }}</span>
                                            <span class="text-xs text-gray-500">/ {{ $member['total_story_points'] }}</span>
                                            @if($member['completed_story_points'] > 0)
                                                <span class="text-xs text-success">✓{{ $member['completed_story_points'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Fortschritts-Balken -->
                                @if($member['total_tasks'] > 0)
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-success h-2 rounded-full" 
                                             style="width: {{ $member['total_tasks'] > 0 ? ($member['completed_tasks'] / $member['total_tasks']) * 100 : 0 }}%"></div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    @svg('heroicon-o-users', 'w-12 h-12 text-gray-400 mx-auto mb-4')
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Keine Team-Mitglieder</h4>
                    <p class="text-gray-600">Es sind noch keine Team-Mitglieder vorhanden.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Platz für Erweiterungen -->
</div>