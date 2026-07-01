<?php

namespace Modules\Klien\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Klien extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nama_klien',
        'logo_klien',
        'urutan',
        'status',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'status' => 'integer',
    ];
}
