<?php

namespace Platform\Cms\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Cms\Models\CmsProject;
use Platform\Cms\Models\CmsBoard;
use Platform\Cms\Models\CmsContent;

class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        $team = $user->currentTeam;
        $projects = CmsProject::where('team_id', $team->id)->orderBy('name')->get();
        $boards = CmsBoard::where('team_id', $team->id)->orderBy('name')->get();
        $contents = CmsContent::where('team_id', $team->id)->latest()->limit(10)->get();

        $stats = [
            'projects_total' => $projects->count(),
            'boards_total' => $boards->count(),
            'contents_total' => CmsContent::where('team_id', $team->id)->count(),
            'contents_published' => CmsContent::where('team_id', $team->id)->where('status', 'published')->count(),
            'contents_draft' => CmsContent::where('team_id', $team->id)->where('status', 'draft')->count(),
        ];

        return view('cms::livewire.dashboard', [
            'currentDate' => now()->format('d.m.Y'),
            'currentDay' => now()->format('l'),
            'stats' => $stats,
            'projects' => $projects->take(5),
            'boards' => $boards->take(5),
            'recentContents' => $contents,
        ])->layout('platform::layouts.app');
    }
}