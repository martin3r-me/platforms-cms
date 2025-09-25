{{-- CMS Sidebar (minimaler Rahmen) --}}
<div>
    {{-- Modul Header --}}
    <x-sidebar-module-header module-name="CMS" />
    
    {{-- Abschnitt: Allgemein --}}
    <div>
        <h4 x-show="!collapsed" class="p-3 text-sm italic text-secondary uppercase">Allgemein</h4>

        {{-- Dashboard --}}
        <a href="{{ route('cms.dashboard') }}"
           class="relative d-flex items-center p-2 my-1 rounded-md font-medium transition"
           :class="[
               window.location.pathname === '/' || 
               window.location.pathname.endsWith('/planner') || 
               window.location.pathname.endsWith('/planner/') ||
               (window.location.pathname.split('/').length === 1 && window.location.pathname === '/')
                   ? 'bg-primary text-on-primary shadow-md'
                   : 'text-black hover:bg-primary-10 hover:text-primary hover:shadow-md',
               collapsed ? 'justify-center' : 'gap-3'
           ]"
           wire:navigate>
            <x-heroicon-o-chart-bar class="w-6 h-6 flex-shrink-0"/>
            <span x-show="!collapsed" class="truncate">Dashboard</span>
        </a>

        {{-- Boards Index --}}
        <a href="{{ route('cms.boards.index') }}"
           class="relative d-flex items-center p-2 my-1 rounded-md font-medium transition"
           :class="[
               window.location.pathname.includes('/my-tasks') || 
               window.location.pathname.endsWith('/my-tasks')
                   ? 'bg-primary text-on-primary shadow-md'
                   : 'text-black hover:bg-primary-10 hover:text-primary hover:shadow-md',
               collapsed ? 'justify-center' : 'gap-3'
           ]"
           wire:navigate>
            <x-heroicon-o-rectangle-stack class="w-6 h-6 flex-shrink-0"/>
            <span x-show="!collapsed" class="truncate">Boards</span>
        </a>

        {{-- Board anlegen --}}
        <a href="#"
           class="relative d-flex items-center p-2 my-1 rounded-md font-medium transition"
           :class="collapsed ? 'justify-center' : 'gap-3'"
           wire:click="createProject">
            <x-heroicon-o-plus class="w-6 h-6 flex-shrink-0"/>
            <span x-show="!collapsed" class="truncate">Board anlegen</span>
        </a>
    </div>

    {{-- Abschnitt: Boards --}}
    <div x-show="!collapsed" x-data="{ openCustomer: (Alpine.store('plannerSidebar')?.openCustomer ?? true), openInternal: (Alpine.store('plannerSidebar')?.openInternal ?? true) }" x-init="Alpine.store('plannerSidebar') || Alpine.store('plannerSidebar', { openCustomer: openCustomer, openInternal: openInternal }); $watch('openCustomer', v => Alpine.store('plannerSidebar').openCustomer = v); $watch('openInternal', v => Alpine.store('plannerSidebar').openInternal = v)">
        {{-- Beispiel: Boards (Platzhalter) --}}
        <div class="mt-2">
            <button type="button" class="w-full d-flex items-center justify-between px-3 py-2 text-sm uppercase text-secondary hover:bg-muted-5 rounded" @click="openCustomer = !openCustomer">
                <span>Boards</span>
                <x-heroicon-o-chevron-down class="w-4 h-4" x-show="!openCustomer"/>
                <x-heroicon-o-chevron-up class="w-4 h-4" x-show="openCustomer"/>
            </button>
            <div x-show="openCustomer" class="mt-1">
                @foreach(($boards ?? []) as $board)
                    <a href="{{ route('cms.boards.show', ['cmsBoard' => $board]) }}"
                       class="relative d-flex items-center p-2 my-1 rounded-md font-medium transition gap-3"
                       :class="[
                           window.location.pathname.includes('/projects/{{ $project->id }}/') || 
                           window.location.pathname.includes('/projects/{{ $project->uuid }}/') ||
                           window.location.pathname.endsWith('/projects/{{ $project->id }}') ||
                           window.location.pathname.endsWith('/projects/{{ $project->uuid }}') ||
                           (window.location.pathname.split('/').length === 2 && window.location.pathname.endsWith('/{{ $project->id }}')) ||
                           (window.location.pathname.split('/').length === 2 && window.location.pathname.endsWith('/{{ $project->uuid }}'))
                               ? 'bg-primary text-on-primary shadow-md'
                               : 'text-black hover:bg-primary-10 hover:text-primary hover:shadow-md'
                       ]"
                       wire:navigate>
                        <x-heroicon-o-folder class="w-6 h-6 flex-shrink-0"/>
                        <span class="truncate">{{ $board->name ?? 'Board' }}</span>
                    </a>
                @endforeach
                @if(empty($boards))
                    <div class="px-3 py-1 text-xs text-muted">Keine Boards</div>
                @endif
            </div>
        </div>

        {{-- weitere Bereiche folgen --}}
    </div>
</div>