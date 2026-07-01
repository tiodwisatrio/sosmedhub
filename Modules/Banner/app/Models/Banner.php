<?php

namespace Modules\Banner\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nama_banner',
        'deskripsi_banner',
        'gambar_banner',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];
}
