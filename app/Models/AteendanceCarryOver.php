<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AteendanceCarryOver extends Model
{
    protected $table = 'attendance_carry_overs';
    protected $fillable = [
        'user_id',
        'source_date',
        'apply_to_date',
        'minutes',
        'type'
    ];
}
