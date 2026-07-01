<?php

namespace Modules\SiteSetting\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'app_name',
        'deskripsi',
        'alamat',
        'no_telp',
        'no_whatsapp',
        'email',
        'logo_atas',
        'logo_bawah',
        'icon',
        'og_image',
        'iframe_map',
        'instagram_nama',
        'instagram_link',
        'facebook_nama',
        'facebook_link',
        'tiktok_nama',
        'tiktok_link',
        'youtube_nama',
        'youtube_link',
        'x_nama',
        'x_link',
        'shopee_nama',
        'shopee_link',
        'tokopedia_nama',
        'tokopedia_link',
        'blibli_nama',
        'blibli_link',
    ];

    public static function current(): self
    {
        return self::firstOrCreate([], ['app_name' => config('app.name')]);
    }
}
