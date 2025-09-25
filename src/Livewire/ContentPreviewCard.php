<?php

namespace Platform\Cms\Livewire;

use Livewire\Component;
use Platform\Cms\Models\CmsContent;

class ContentPreviewCard extends Component
{
    public CmsContent $content;

    public function mount(CmsContent $content)
    {
        $this->content = $content;
    }

    public function render()
    {
        return view('cms::livewire.content-preview-card');
    }
}


