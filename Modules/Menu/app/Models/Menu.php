<?php

namespace Modules\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'parent_id',
        'label',
        'route_name',
        'route_params',
        'active_pattern',
        'permission',
        'icon',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'route_params' => 'array',
        'urutan' => 'integer',
        'is_active' => 'integer',
    ];

    /**
     * Daftar icon heroicon (style outline) yang valid untuk sidebar.
     * Nama harus cocok dengan komponen <x-heroicon-o-{nama}>.
     *
     * @return array<string, string>
     */
    public static function iconOptions(): array
    {
        return [
            'home' => 'Home',
            'squares-2x2' => 'Dashboard',
            'user' => 'User',
            'user-group' => 'Tim',
            'users' => 'Users',
            'briefcase' => 'Layanan',
            'star' => 'Unggulan',
            'tag' => 'Kategori',
            'bars-3' => 'Menu',
            'shield-check' => 'Role',
            'cog-6-tooth' => 'Setting',
            'document-text' => 'Artikel',
            'photo' => 'Media',
            'archive-box' => 'Data',
            'chart-bar' => 'Statistik',
            'bell' => 'Notifikasi',
            'map-pin' => 'Lokasi',
            'envelope' => 'Email',
            'phone' => 'Telepon',
            'globe-alt' => 'Website',
            'cube' => 'Produk',
            'wrench-screwdriver' => 'Tools',
        ];
    }

    /**
     * Nama icon yang dijamin ada sebagai komponen heroicon outline.
     * Mencegah seluruh sidebar 500 hanya karena satu icon salah ketik.
     */
    public function safeIcon(string $fallback = 'archive-box'): string
    {
        $icon = $this->icon ?: $fallback;
        $path = base_path("vendor/blade-ui-kit/blade-heroicons/resources/svg/o-{$icon}.svg");

        return is_file($path) ? $icon : $fallback;
    }

    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('urutan');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function routeUrl(): string
    {
        if (! $this->route_name) {
            return '#';
        }

        try {
            return route($this->route_name, $this->route_params ?? []);
        } catch (\Exception) {
            return '#';
        }
    }

    public function isActive(): bool
    {
        if (! $this->active_pattern) {
            return false;
        }

        if (! $this->matchesPattern()) {
            return false;
        }

        foreach ($this->route_params ?? [] as $key => $value) {
            if ((string) request()->query($key) !== (string) $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Pola dipisah koma. Awalan "!" mengecualikan: menu aktif bila cocok dengan salah satu
     * pola biasa dan tidak cocok dengan pola pengecualian mana pun.
     * Contoh: "admin.scheduled-posts.*,!admin.scheduled-posts.create".
     */
    private function matchesPattern(): bool
    {
        $patterns = array_filter(array_map('trim', explode(',', (string) $this->active_pattern)));

        $include = array_filter($patterns, fn (string $pattern) => ! str_starts_with($pattern, '!'));
        $exclude = array_map(
            fn (string $pattern) => substr($pattern, 1),
            array_filter($patterns, fn (string $pattern) => str_starts_with($pattern, '!'))
        );

        if ($include === [] || ! request()->routeIs(...$include)) {
            return false;
        }

        return $exclude === [] || ! request()->routeIs(...$exclude);
    }

    public function isParentActive(): bool
    {
        return $this->children->contains(fn ($child) => $child->isActive());
    }

    public function canSee(): bool
    {
        if (! $this->permission) {
            return true;
        }

        return auth()->user()?->can($this->permission) ?? false;
    }
}
