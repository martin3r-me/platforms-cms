<?php

namespace Platform\Cms\Models;

use Illuminate\Database\Eloquent\Model;

class CmsCustomerProject extends Model
{
    protected $fillable = [
        'project_id','team_id','user_id','company_id',
        'customer_model','customer_id',
    ];

    public function project()
    {
        return $this->belongsTo(CmsProject::class, 'project_id');
    }
}

 

