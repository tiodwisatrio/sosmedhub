<?php

namespace Modules\Scheduler\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Scheduler\Database\Factories\ScheduledPostFactory;
use Modules\SocialAccount\Models\SocialAccount;

class ScheduledPost extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

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

    private const ALLOWED_SLOTS = [1, 5, 10, 15, 20, 30, 60];

    public static function slotMinutes(): int
    {
        $slot = (int) config('scheduler.slot_minutes', 15);

        return in_array($slot, self::ALLOWED_SLOTS, true) ? $slot : 15;
    }

    /**
     * Slot jadwal harus sejajar dengan jam cron. Karena slot selalu pembagi 60 dan
     * WIB berselisih tepat 7 jam dari UTC, sejajar di WIB berarti sejajar di UTC.
     */
    public static function isOnSlot(Carbon $time): bool
    {
        return $time->second === 0 && $time->minute % self::slotMinutes() === 0;
    }

    /**
     * Slot pertama yang jatuh setelah $from (default: sekarang).
     */
    public static function nextSlot(?Carbon $from = null): Carbon
    {
        $slot = self::slotMinutes();
        $time = ($from ?? now())->copy()->startOfMinute()->addMinute();
        $remainder = $time->minute % $slot;

        return $remainder === 0 ? $time : $time->addMinutes($slot - $remainder);
    }

    /**
     * Kalimat bantuan di bawah pemilih jam. Slot 1 menit berarti bebas memilih menit,
     * jadi kalimat "kelipatan 1 menit" dihindari.
     */
    public static function slotHint(): string
    {
        $slot = self::slotMinutes();

        if ($slot === 1) {
            return 'Pilih menit bebas. Postingan terbit pada menit yang dipilih, bisa mundur kurang dari satu menit.';
        }

        return sprintf(
            'Menit kelipatan %d (%s). Postingan terbit pada jam yang dipilih, bisa mundur beberapa menit.',
            $slot,
            self::slotExamples()
        );
    }

    /**
     * Contoh jam untuk pesan bantuan, misalnya "09.00, 09.15, 09.30, atau 09.45".
     */
    public static function slotExamples(): string
    {
        $slot = self::slotMinutes();
        $times = collect(range(0, 3))
            ->map(fn (int $i) => Carbon::createFromTime(9, 0)->addMinutes($i * $slot)->format('H.i'))
            ->unique()
            ->values();

        return $times->count() > 1
            ? $times->slice(0, -1)->implode(', ').', atau '.$times->last()
            : $times->first();
    }

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

    /**
     * Post gagal dan draf boleh diubah lalu dijadwalkan ulang.
     */
    /**
     * Thumbnail media pertama untuk tampilan daftar (kalender, riwayat).
     */
    public function getThumbnailPathAttribute(): ?string
    {
        return $this->media->first()?->displayPath();
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_SCHEDULED, self::STATUS_FAILED, self::STATUS_DRAFT], true);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_SCHEDULED, self::STATUS_DRAFT], true);
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
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
            self::STATUS_DRAFT => 'Draf',
            self::STATUS_SCHEDULED => 'Terjadwal',
            self::STATUS_PUBLISHING => 'Menerbitkan',
            self::STATUS_PUBLISHED => 'Terbit',
            self::STATUS_FAILED => 'Gagal',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }
}
