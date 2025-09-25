<?php

namespace Platform\Cms\Livewire;

use Livewire\Component;
use Platform\Cms\Models\CmsBoardSlot;
use Livewire\Attributes\On;

class BoardSlotSettingsModal extends Component
{
    public bool $show = false;
    public ?int $slotId = null;
    public string $name = '';

    #[On('open-modal-cms-board-slot-settings')]
    public function open(int $slotId): void
    {
        $this->loadSlot($slotId);
        $this->show = true;
    }

    public function loadSlot(int $slotId): void
    {
        $this->slotId = $slotId;
        $s = CmsBoardSlot::findOrFail($slotId);
        $this->name = (string)$s->name;
    }

    public function save(): void
    {
        if (!$this->slotId) return;
        $this->validate([
            'name' => ['required','string','max:255'],
        ]);
        CmsBoardSlot::where('id', $this->slotId)->update([
            'name' => $this->name,
        ]);
        $this->dispatch('sprintSlotUpdated');
        $this->show = false;
    }

    public function render()
    {
        return view('cms::livewire.board-slot-settings-modal');
    }
}


