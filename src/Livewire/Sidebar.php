<?php

namespace Platform\Cms\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Cms\Models\CmsProject as Project;
use Livewire\Attributes\On; 


class Sidebar extends Component
{

    #[On('updateSidebar')] 
    public function updateSidebar()
    {
        
    }

    public function createProject()
    {
        $user = Auth::user();
        $teamId = $user->currentTeam->id;

        // 1. Neues Projekt anlegen
        $project = new Project();
        $project->name = 'Neues Projekt';
        $project->user_id = $user->id;
        $project->team_id = $teamId;
        $project->order = Project::where('team_id', $teamId)->max('order') + 1;
        $project->save();

        // --> ProjectUser als Owner anlegen
        $project->projectUsers()->create([
            'user_id' => $user->id,
            'role' => 'owner',
        ]);
        // Alternativ, falls du direkt das Model nutzen möchtest:
        // \Platform\Planner\Models\PlannerProjectUser::create([
        //     'project_id' => $project->id,
        //     'user_id' => $user->id,
        //     'role' => \Platform\Planner\Enums\ProjectRole::OWNER->value,
        // ]);

        return redirect()->route('cms.boards.index');
    }

    public function render()
    {
        // Dynamische Projects: team-basiert und Mitgliedschaft
        $uid = auth()->id();
        $tid = auth()->user()?->currentTeam->id ?? null;
        $projects = Project::query()
            ->with('customerProject')
            ->where('team_id', $tid)
            ->where(function($q) use ($uid){
                $q->where('user_id', $uid)
                  ->orWhereHas('projectUsers', fn($qq) => $qq->where('user_id', $uid));
            })
            ->orderBy('name')
            ->get();

        // Gruppierung analog Planner: nach Kunde (Resolver-basierte Anzeige)
        $groups = collect();
        foreach ($projects as $p) {
            $cp = $p->customerProject;
            $label = 'Ohne Kunde';
            try {
                if ($cp && $cp->customer_model === 'crm.companies' && $cp->customer_id) {
                    $label = app(\Platform\Core\Contracts\CrmCompanyResolverInterface::class)->displayName((int)$cp->customer_id) ?: 'Firma #'.$cp->customer_id;
                } elseif ($cp && $cp->customer_model === 'crm.contacts' && $cp->customer_id) {
                    $label = app(\Platform\Core\Contracts\CrmContactResolverInterface::class)->displayName((int)$cp->customer_id) ?: 'Kontakt #'.$cp->customer_id;
                } elseif ($cp && $cp->company_id) {
                    $label = app(\Platform\Core\Contracts\CrmCompanyResolverInterface::class)->displayName((int)$cp->company_id) ?: 'Firma #'.$cp->company_id;
                }
            } catch (\Throwable $e) {
                $label = 'Ohne Kunde';
            }
            if (!isset($groups[$label])) {
                $groups[$label] = collect();
            }
            $groups[$label]->push($p);
        }

        // Sortiere Gruppen alphabetisch und Projekte innerhalb der Gruppen
        $groups = collect($groups)->sortKeys()->map(function($items){
            return $items->sortBy('name')->values();
        });

        return view('cms::livewire.sidebar', [
            'groups' => $groups,
        ]);
    }
}
