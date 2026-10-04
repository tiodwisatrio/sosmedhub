<?php

namespace Modules\Scheduler\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Scheduler\Database\Factories\ScheduledPostFactory;
use Modules\SocialAccount\Models\SocialAccount;

class ScheduledPost extends Model
{
    use HasFactory;

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_PUBLISHING = 'publishing';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    public const WIB = 'Asia/Jakarta';

    protected $fillable = [
        'user_id',
        'social_account_id',
        'caption',
        'scheduled_at',
        'status',
        'ig_media_id',
        'error_message',
        'published_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    protected static function newFactory()
    {
        return ScheduledPostFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ScheduledPostMedia::class)->orderBy('position');
    }

    /**
     * Media pertama dipakai sebagai thumbnail agar tampilan lama yang
     * masih memakai $post->media_path tetap berfungsi.
     */
    public function getMediaPathAttribute(): ?string
    {
        return $this->media->first()?->media_path;
    }

    public function canBeEdited(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function formattedScheduledAt(): string
    {
        return $this->scheduled_at?->setTimezone(self::WIB)->format('d M Y H:i') ?? '';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_SCHEDULED => 'Terjadwal',
            self::STATUS_PUBLISHING => 'Menerbitkan',
            self::STATUS_PUBLISHED => 'Terbit',
            self::STATUS_FAILED => 'Gagal',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }
}
