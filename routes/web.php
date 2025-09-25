<?php

use Platform\Cms\Livewire\Dashboard;
use Platform\Cms\Livewire\BoardsIndex;
use Platform\Cms\Livewire\Board;
use Platform\Cms\Livewire\Content;

Route::get('/', Dashboard::class)->name('cms.dashboard');
Route::get('/boards', BoardsIndex::class)->name('cms.boards.index');
Route::get('/boards/{cmsBoard}', Board::class)->name('cms.boards.show');
Route::get('/content/{cmsContent}', Content::class)->name('cms.contents.show');
