<?php

namespace Modules\Keunggulan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Keunggulan extends Model {
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'image',
        'urutan',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    
}