<?php

namespace Platform\Cms\Livewire;

use Livewire\Component;
use Platform\Cms\Models\CmsProject;
use Platform\Cms\Models\CmsBoard;

class Project extends Component
{
    public $cmsProject;

    public function mount($cmsProject)
    {
        $this->cmsProject = $cmsProject;
    }

    public function render()
    {
        $project = CmsProject::findOrFail($this->cmsProject);
        $boards = CmsBoard::where('project_id', $project->id)->orderBy('order')->get();
        return view('cms::livewire.project', compact('project', 'boards'))->layout('platform::layouts.app');
    }
}


