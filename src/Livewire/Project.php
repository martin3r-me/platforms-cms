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
    public ?int $selectedBoardId = null;

    public function mount($cmsProject)
    {
        $this->cmsProject = $cmsProject;
    }

    public function render()
    {
        $project = CmsProject::findOrFail($this->cmsProject);
        $boards = CmsBoard::where('project_id', $project->id)->orderBy('order')->get();
        if ($this->selectedBoardId === null) {
            $this->selectedBoardId = $boards->first()->id ?? null;
        }
        $selectedBoard = $this->selectedBoardId
            ? $boards->firstWhere('id', $this->selectedBoardId)
            : null;

        $groups = collect();
        if ($selectedBoard) {
            $slots = CmsBoardSlot::where('board_id', $selectedBoard->id)->orderBy('order')->get();
            $backlogItems = CmsContent::where('board_id', $selectedBoard->id)
                ->whereNull('slot_id')->orderBy('order')->get();
            $groups = collect([
                (object) ['id' => null, 'label' => 'Backlog', 'isBacklog' => true, 'items' => $backlogItems],
            ])->concat(
                $slots->map(function($s){
                    return (object) [
                        'id' => $s->id,
                        'label' => $s->name,
                        'isBacklog' => false,
                        'items' => CmsContent::where('slot_id', $s->id)->orderBy('order')->get(),
                    ];
                })
            );
        }

        // einfache Kennzahlen analog Planner (angepasst für CMS)
        $openCount = $groups->sum(fn($g) => collect($g->items)->count());

        return view('cms::livewire.project', [
            'project' => $project,
            'boards' => $boards,
            'selectedBoard' => $selectedBoard,
            'groups' => $groups,
            'openCount' => $openCount,
        ])->layout('platform::layouts.app');
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

    public function createSlot(): void
    {
        if (!$this->selectedBoardId) return;
        $max = CmsBoardSlot::where('board_id', $this->selectedBoardId)->max('order') ?? 0;
        CmsBoardSlot::create([
            'board_id' => $this->selectedBoardId,
            'name' => 'Neue Spalte',
            'order' => $max + 1,
            'user_id' => auth()->id(),
            'team_id' => auth()->user()?->currentTeam?->id,
        ]);
    }

    public function selectBoard(int $boardId): void
    {
        $this->selectedBoardId = $boardId;
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


