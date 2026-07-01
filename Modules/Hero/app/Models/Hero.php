<?php

namespace Modules\Hero\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hero extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'judul_hero',
        'deskripsi_hero',
        'button_hero',
    ];
}
