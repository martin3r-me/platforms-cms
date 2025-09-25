<?php

namespace Platform\Cms\Models;

use Illuminate\Database\Eloquent\Model;

class CmsBoardSlot extends Model
{
    protected $table = 'cms_board_slots';

    protected $fillable = [
        'board_id', 'name', 'order', 'user_id', 'team_id',
    ];

    public function board()
    {
        /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Platform\Cms\Models\CmsBoard> */
        return $this->belongsTo(CmsBoard::class, 'board_id');
    }

    public function contents()
    {
        /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Platform\Cms\Models\CmsContent> */
        return $this->hasMany(CmsContent::class, 'slot_id');
    }
}


