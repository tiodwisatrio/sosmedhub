<?php

namespace Modules\Paket\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nama_paket',
        'deskripsi_paket',
        'harga_paket',
        'gambar_paket',
        'urutan',
        'status',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'status' => 'integer',
    ];
}
