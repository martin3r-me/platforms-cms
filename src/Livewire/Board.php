<?php

namespace Platform\Cms\Livewire;

use Livewire\Component;
use Platform\Cms\Models\CmsBoard;
use Platform\Cms\Models\CmsContent;

class Board extends Component
{
    public $cmsBoard;

    public function mount($cmsBoard)
    {
        $this->cmsBoard = $cmsBoard;
    }

    public function render()
    {
        $board = CmsBoard::findOrFail($this->cmsBoard);
        $contents = CmsContent::where('board_id', $board->id)->orderBy('order')->get();
        return view('cms::livewire.board', compact('board', 'contents'))->layout('platform::layouts.app');
    }
}


