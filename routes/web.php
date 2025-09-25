<?php

use Platform\Cms\Livewire\Dashboard;
use Platform\Cms\Livewire\BoardsIndex;
use Platform\Cms\Livewire\Board;
use Platform\Cms\Livewire\Content;
use Platform\Cms\Livewire\Project;
use Platform\Cms\Livewire\ProjectsIndex;

Route::get('/', Dashboard::class)->name('cms.dashboard');
// Projekte
Route::get('/projects', ProjectsIndex::class)->name('cms.projects.index');
Route::get('/projects/{cmsProject}', Project::class)->name('cms.projects.show');
// Boards & Content
Route::get('/boards', BoardsIndex::class)->name('cms.boards.index');
Route::get('/boards/create', BoardsIndex::class)->name('cms.boards.create');
Route::get('/boards/{cmsBoard}', Board::class)->name('cms.boards.show');
Route::get('/content/{cmsContent}', Content::class)->name('cms.contents.show');
