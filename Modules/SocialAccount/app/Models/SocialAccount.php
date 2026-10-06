<?php

namespace Modules\SocialAccount\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model
{
    use HasFactory;

    public const PLATFORM_INSTAGRAM = 'instagram';

    public const PLATFORM_FACEBOOK = 'facebook';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_DISCONNECTED = 'disconnected';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'user_id',
        'platform',
        'provider_account_id',
        'username',
        'display_name',
        'avatar_url',
        'access_token',
        'token_expires_at',
        'status',
        'metadata',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'token_expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function label(): string
    {
        $name = $this->display_name ?: $this->username;

        // Halaman Facebook tidak punya handle seperti Instagram, jadi cukup namanya.
        if ($this->platform === self::PLATFORM_FACEBOOK) {
            return $name;
        }

        return trim($name.' (@'.$this->username.')');
    }
}
