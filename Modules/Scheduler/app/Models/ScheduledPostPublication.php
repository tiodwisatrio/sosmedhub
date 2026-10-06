<?php

namespace Modules\Scheduler\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Penerbitan satu format (Feed, Story, atau Reels) dari sebuah jadwal.
 */
class ScheduledPostPublication extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PUBLISHING = 'publishing';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'scheduled_post_id',
        'format',
        'status',
        'ig_media_id',
        'state',
        'error_message',
        'share_to_feed',
        'published_at',
    ];

    protected $casts = [
        'state' => 'array',
        'share_to_feed' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function scheduledPost(): BelongsTo
    {
        return $this->belongsTo(ScheduledPost::class);
    }

    /**
     * Menyiapkan percobaan ulang setelah gagal. Item yang sudah terbit dipertahankan agar tidak
     * terbit dua kali; container lama dilepas karena bisa sudah kedaluwarsa atau galat.
     */
    public function resetForRetry(): void
    {
        $items = collect($this->state['items'] ?? [])
            ->map(fn (array $item) => $item['published_id'] ? $item : [...$item, 'container_id' => null])
            ->all();

        $this->update([
            'status' => self::STATUS_PENDING,
            'error_message' => null,
            'state' => $items === [] ? null : ['items' => $items, 'children' => []],
        ]);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Sudah selesai, berhasil maupun gagal; tidak ada pekerjaan tersisa.
     */
    public function isFinal(): bool
    {
        return in_array($this->status, [self::STATUS_PUBLISHED, self::STATUS_FAILED], true);
    }

    public function label(): string
    {
        return ScheduledPost::formatLabel($this->format);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PUBLISHED => 'Terbit',
            self::STATUS_FAILED => 'Gagal',
            self::STATUS_PUBLISHING => 'Menerbitkan',
            default => 'Menunggu',
        };
    }
}
