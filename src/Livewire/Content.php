<?php

namespace Platform\Cms\Livewire;

use Livewire\Component;
use Platform\Cms\Models\CmsContent;

class Content extends Component
{
    public $cmsContent;

    public function mount($cmsContent)
    {
        $this->cmsContent = $cmsContent;
    }

    public function render()
    {
        $content = CmsContent::findOrFail($this->cmsContent);
        return view('cms::livewire.content', compact('content'))->layout('platform::layouts.app');
    }
}


