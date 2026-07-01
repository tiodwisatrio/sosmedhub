<?php

namespace Modules\Layanan\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Layanan\Database\Factories\LayananFactory;

class Layanan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'image',
        'urutan',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'urutan' => 'integer',
    ];

    protected static function newFactory(): LayananFactory
    {
        return LayananFactory::new();
    }
}
