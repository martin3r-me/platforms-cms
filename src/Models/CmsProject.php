<?php

namespace Platform\Cms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\Uid\UuidV7;
use Illuminate\Support\Facades\Auth;

class CmsProject extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cms_projects';

    protected $fillable = [
        'uuid', 'name', 'description', 'order', 'is_active',
        'user_id', 'team_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            do {
                $uuid = UuidV7::generate();
            } while (self::where('uuid', $uuid)->exists());
            $model->uuid = $uuid;
            if (!$model->user_id) {
                $model->user_id = Auth::id();
            }
            if (!$model->team_id && Auth::user()) {
                $model->team_id = Auth::user()->currentTeam->id ?? null;
            }
        });
    }

    public function boards()
    {
        /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Platform\Cms\Models\CmsBoard> */
        return $this->hasMany(CmsBoard::class, 'project_id');
    }

    public function projectUsers()
    {
        /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Platform\Cms\Models\CmsProjectUser> */
        return $this->hasMany(CmsProjectUser::class, 'project_id');
    }

    public function customerProject()
    {
        /** @return \Illuminate\Database\Eloquent\Relations\HasOne<\Platform\Cms\Models\CmsCustomerProject> */
        return $this->hasOne(\Platform\Cms\Models\CmsCustomerProject::class, 'project_id');
    }
}
