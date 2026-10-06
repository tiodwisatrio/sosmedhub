<?php

namespace Modules\Scheduler\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledPostMedia extends Model
{
    protected $table = 'scheduled_post_media';

    protected $fillable = [
        'scheduled_post_id',
        'format',
        'type',
        'duration_ms',
        'mime',
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
        // Video tidak punya thumbnail gambar; tampilan daftar memakai ubin ikon putar.
        return $this->thumbnail_path ?: ($this->isVideo() ? null : $this->media_path);
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    public function durationSeconds(): ?float
    {
        return $this->duration_ms === null ? null : $this->duration_ms / 1000;
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
