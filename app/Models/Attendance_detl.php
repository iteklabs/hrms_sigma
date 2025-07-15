<?php

namespace App\Models;

use App\Models\BaseModel;

class Attendance_detl extends BaseModel
{
    protected $table = 'attendances_detl';

    protected $fillable = [
        'is_late',
        'is_undertime',
        'is_absent',
        'is_holiday',
        'is_weekend',
        'is_leave',
        'is_overtime',
        'is_present',
        'date',
        'no_of_hrs',
        'attendance_id',
        'user_id'
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
