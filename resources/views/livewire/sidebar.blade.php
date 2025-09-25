{{-- CMS Sidebar (analog Planner, Entry über Projekte) --}}
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
               window.location.pathname.endsWith('/cms') || 
               window.location.pathname.endsWith('/cms/')
                   ? 'bg-primary text-on-primary shadow-md'
                   : 'text-black hover:bg-primary-10 hover:text-primary hover:shadow-md',
               collapsed ? 'justify-center' : 'gap-3'
           ]"
           wire:navigate>
            <x-heroicon-o-chart-bar class="w-6 h-6 flex-shrink-0"/>
            <span x-show="!collapsed" class="truncate">Dashboard</span>
        </a>

        {{-- Projekte --}}
        <a href="{{ route('cms.projects.index') }}"
           class="relative d-flex items-center p-2 my-1 rounded-md font-medium transition"
           :class="[
               window.location.pathname.includes('/cms/projects')
                   ? 'bg-primary text-on-primary shadow-md'
                   : 'text-black hover:bg-primary-10 hover:text-primary hover:shadow-md',
               collapsed ? 'justify-center' : 'gap-3'
           ]"
           wire:navigate>
            <x-heroicon-o-folder class="w-6 h-6 flex-shrink-0"/>
            <span x-show="!collapsed" class="truncate">Projekte</span>
        </a>

        {{-- Projekt anlegen --}}
        <a href="#"
           class="relative d-flex items-center p-2 my-1 rounded-md font-medium transition"
           :class="collapsed ? 'justify-center' : 'gap-3'"
           wire:click="createProject">
            <x-heroicon-o-plus class="w-6 h-6 flex-shrink-0"/>
            <span x-show="!collapsed" class="truncate">Projekt anlegen</span>
        </a>
    </div>

    {{-- Abschnitt: Projekte --}}
    <div x-show="!collapsed">
        <div class="mt-2">
            <button type="button" class="w-full d-flex items-center justify-between px-3 py-2 text-sm uppercase text-secondary hover:bg-muted-5 rounded" @click="openProjects = !openProjects" x-data="{ openProjects: true }">
                <span>Projekte</span>
                <x-heroicon-o-chevron-down class="w-4 h-4" x-show="!openProjects"/>
                <x-heroicon-o-chevron-up class="w-4 h-4" x-show="openProjects"/>
            </button>
            <div x-show="openProjects" class="mt-1">
                @foreach(($projects ?? []) as $project)
                    <a href="{{ route('cms.projects.show', ['cmsProject' => $project]) }}"
                       class="relative d-flex items-center p-2 my-1 rounded-md font-medium transition gap-3"
                       :class="[
                           window.location.pathname.includes('/cms/projects/{{ $project->id }}') || 
                           window.location.pathname.endsWith('/cms/projects/{{ $project->id }}')
                               ? 'bg-primary text-on-primary shadow-md'
                               : 'text-black hover:bg-primary-10 hover:text-primary hover:shadow-md'
                       ]"
                       wire:navigate>
                        <x-heroicon-o-folder class="w-6 h-6 flex-shrink-0"/>
                        <span class="truncate">{{ $project->name }}</span>
                    </a>
                @endforeach
                @if(($projects ?? collect())->isEmpty())
                    <div class="px-3 py-1 text-xs text-muted">Keine Projekte</div>
                @endif
            </div>
        </div>
    </div>
</div>