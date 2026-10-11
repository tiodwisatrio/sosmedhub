<?php

namespace Modules\Scheduler\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    // Libur nasional.
    public const TYPE_HOLIDAY = 'holiday';

    // Cuti bersama; bagi pekerja swasta bersifat opsional.
    public const TYPE_JOINT_LEAVE = 'joint_leave';

    // Hari besar umum yang bukan hari libur.
    public const TYPE_OBSERVANCE = 'observance';

    // Urutan warna hari besar; kelas lengkapnya ada di palet view kalender.
    public const HUES = ['red', 'blue', 'green', 'purple', 'orange', 'pink'];

    protected $fillable = ['date', 'name', 'type'];

    protected $casts = [
        'date' => 'date',
    ];
}
