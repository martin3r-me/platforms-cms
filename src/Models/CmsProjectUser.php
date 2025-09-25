<?php

namespace Platform\Cms\Models;

use Illuminate\Database\Eloquent\Model;

class CmsProjectUser extends Model
{
    protected $table = 'cms_project_users';

    protected $fillable = [
        'project_id', 'user_id', 'role',
    ];
}


