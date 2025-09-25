<?php

namespace Platform\Cms\Livewire;

use Livewire\Component;
use Platform\Cms\Models\CmsProject;
use Platform\Cms\Models\CmsBoard;
use Platform\Cms\Models\CmsContent;
use Platform\Cms\Models\CmsBoardSlot;

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
        $boards = CmsBoard::where('project_id', $project->id)
            ->orderBy('order')
            ->get()
            ->map(function($b){
                $slots = CmsBoardSlot::where('board_id', $b->id)->orderBy('order')->get();
                $backlogItems = CmsContent::where('board_id', $b->id)->whereNull('slot_id')->orderBy('order')->get();
                $b->kanban = collect(array_merge([
                    (object) ['id' => null, 'label' => 'Backlog', 'items' => $backlogItems, 'isBacklog' => true],
                ], $slots->map(function($s){
                    $s->label = $s->name;
                    $s->items = CmsContent::where('slot_id', $s->id)->orderBy('order')->get();
                    $s->isBacklog = false;
                    return $s;
                })->all()));
                return $b;
            });
        return view('cms::livewire.project', compact('project', 'boards'))->layout('platform::layouts.app');
    }

    public function createBoard(): void
    {
        $project = CmsProject::findOrFail($this->cmsProject);
        $maxOrder = CmsBoard::where('project_id', $project->id)->max('order') ?? 0;
        CmsBoard::create([
            'project_id' => $project->id,
            'name' => 'Neues Board',
            'order' => $maxOrder + 1,
            'user_id' => auth()->id(),
            'team_id' => auth()->user()?->currentTeam?->id,
        ]);
    }

    public function createContent(int $boardId): void
    {
        $board = CmsBoard::findOrFail($boardId);
        $maxOrder = CmsContent::where('board_id', $board->id)->whereNull('slot_id')->max('order') ?? 0;
        CmsContent::create([
            'project_id' => $board->project_id,
            'board_id' => $board->id,
            'title' => 'Neuer Inhalt',
            'status' => 'draft',
            'order' => $maxOrder + 1,
            'user_id' => auth()->id(),
            'team_id' => auth()->user()?->currentTeam?->id,
        ]);
    }

    public function updateSlotOrder($orderedIds): void
    {
        foreach ($orderedIds as $idx => $slotId) {
            if (!$slotId) continue;
            CmsBoardSlot::where('id', $slotId)->update(['order' => $idx + 1]);
        }
    }

    public function updateContentOrder($grouped): void
    {
        foreach ($grouped as $slotId => $ids) {
            foreach (array_values($ids) as $pos => $contentId) {
                CmsContent::where('id', $contentId)->update([
                    'slot_id' => $slotId ?: null,
                    'order' => $pos + 1,
                ]);
            }
        }
    }
}


