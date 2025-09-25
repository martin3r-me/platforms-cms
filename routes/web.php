<?php

use Platform\Cms\Livewire\Dashboard;
use Platform\Cms\Livewire\BoardsIndex;
use Platform\Cms\Livewire\Board;
use Platform\Cms\Livewire\Content;
use Platform\Cms\Livewire\Project;

Route::get('/', Dashboard::class)->name('cms.dashboard');
Route::get('/boards', BoardsIndex::class)->name('cms.boards.index');
Route::get('/boards/{cmsBoard}', Board::class)->name('cms.boards.show');
Route::get('/content/{cmsContent}', Content::class)->name('cms.contents.show');
Route::get('/projects/{cmsProject}', Project::class)->name('cms.projects.show');
