<?php

namespace Modules\Scheduler\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledPostMedia extends Model
{
    protected $table = 'scheduled_post_media';

    protected $fillable = [
        'scheduled_post_id',
        'media_path',
        'position',
    ];

    public function scheduledPost(): BelongsTo
    {
        return $this->belongsTo(ScheduledPost::class);
    }
}
