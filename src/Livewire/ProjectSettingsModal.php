<?php

namespace Platform\Cms\Livewire;

use Livewire\Component;
use Platform\Cms\Models\CmsProject;
use Livewire\Attributes\On;

class ProjectSettingsModal extends Component
{
    public bool $show = false;
    public ?int $projectId = null;
    public string $name = '';
    public ?string $description = null;

    #[On('open-modal-cms-project-settings')]
    public function open(int $projectId): void
    {
        $this->loadProject($projectId);
        $this->show = true;
    }

    public function loadProject(int $projectId): void
    {
        $this->projectId = $projectId;
        $p = CmsProject::findOrFail($projectId);
        $this->name = (string)$p->name;
        $this->description = $p->description;
    }

    public function save(): void
    {
        if (!$this->projectId) return;
        $this->validate([
            'name' => ['required','string','max:255'],
        ]);
        CmsProject::where('id', $this->projectId)->update([
            'name' => $this->name,
            'description' => $this->description,
        ]);
        $this->dispatch('updateProject');
        $this->show = false;
    }

    public function render()
    {
        return view('cms::livewire.project-settings-modal');
    }
}


