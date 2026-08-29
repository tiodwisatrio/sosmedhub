<?php

namespace Modules\TentangKami\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// use Modules\TentangKami\Database\Factories\TentangKamiFactory;

class TentangKami extends Model
{
    use HasFactory;

    protected $table = 'tentangkami';

    protected $fillable = [
        'tentangkami_tagline',
        'tentangkami_judul',
        'tentangkami_deskripsi',
        'tentangkami_gambar',
        'tentangkami_visi',
        'tentangkami_misi',
    ];

    public function stats(): HasMany
    {
        return $this->hasMany((TentangKamiStat::class), 'tentangkami_id');
    }

    public static function current(): self
    {
        return self::firstOrCreate([], [
            'tentangkami_judul' => 'Tentang Kami',
            'tentangkami_deskripsi' => '',
        ]);
    }

    /**
     * The attributes that are mass assignable.
     */

    // protected static function newFactory(): TentangKamiFactory
    // {
    //     // return TentangKamiFactory::new();
    // }
}
