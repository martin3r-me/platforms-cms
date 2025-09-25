<?php

namespace Platform\Cms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\Uid\UuidV7;
use Illuminate\Support\Facades\Auth;

class CmsBoard extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cms_boards';

    protected $fillable = [
        'uuid', 'project_id', 'name', 'slug', 'description', 'order', 'settings',
        'user_id', 'team_id',
    ];

    protected $casts = [
        'settings' => 'array',
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

    public function project()
    {
        /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Platform\Cms\Models\CmsProject> */
        return $this->belongsTo(CmsProject::class, 'project_id');
    }

    public function contents()
    {
        /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Platform\Cms\Models\CmsContent> */
        return $this->hasMany(CmsContent::class, 'board_id');
    }
}
