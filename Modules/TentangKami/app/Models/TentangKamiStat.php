<?php

namespace Modules\TentangKami\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Modules\TentangKami\Database\Factories\TentangKamiStatFactory;

class TentangKamiStat extends Model
{
    use HasFactory;

    protected $table = 'tentangkami_stats';

    protected $fillable = [
        'tentangkami_id',
        'tentangkami_stats_label',
        'tentangkami_stats_angka',
        'tentangkami_stats_gambar',
    ];

    public function tentangKami(): BelongsTo
    {
        return $this->belongsTo(TentangKami::class, 'tentangkami_id');
    }
}
