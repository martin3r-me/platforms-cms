<?php

namespace Platform\Cms\Livewire;

use Livewire\Component;
use Platform\Cms\Models\CmsProject;

class ProjectsIndex extends Component
{
    public function render()
    {
        $projects = CmsProject::query()
            ->where('team_id', auth()->user()?->currentTeam->id ?? null)
            ->orderBy('name')
            ->get();
        return view('cms::livewire.projects-index', compact('projects'))->layout('platform::layouts.app');
    }
}


