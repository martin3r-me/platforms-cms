<?php

namespace Platform\Cms\Livewire;

use Livewire\Component;
use Platform\Cms\Models\CmsBoard;

class BoardsIndex extends Component
{
    public function render()
    {
        $boards = CmsBoard::query()
            ->where('team_id', auth()->user()?->currentTeam->id ?? null)
            ->orderBy('name')
            ->get();
        return view('cms::livewire.boards-index', compact('boards'))->layout('platform::layouts.app');
    }
}


