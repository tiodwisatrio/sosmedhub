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
        'thumbnail_path',
        'width',
        'height',
        'size',
        'media_pruned_at',
        'position',
    ];

    protected $casts = [
        'media_pruned_at' => 'datetime',
    ];

    /**
     * Gambar untuk tampilan daftar: thumbnail bila ada, kalau tidak versi terbit (foto lama).
     */
    public function displayPath(): ?string
    {
        return $this->thumbnail_path ?: $this->media_path;
    }

    public function hasPublishFile(): bool
    {
        return (bool) $this->media_path;
    }

    public function scheduledPost(): BelongsTo
    {
        return $this->belongsTo(ScheduledPost::class);
    }
}
