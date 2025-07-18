<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;

class OverideShift extends BaseModel
{
    protected $table = 'overide_shifts';
    protected $default = ["xid", "x_user_id", "x_shift_id", "date", "time_in", "time_out", "schedule_type", "x_schedule_location_id"];
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $hidden = ['id', 'user_id', 'schedule_location_id', 'shift_id'];
    protected $appends = ['xid', 'x_user_id', 'x_shift_id', 'x_schedule_location_id'];

    protected $hashableGetterFunctions = [
        'getXUserIdAttribute' => 'user_id',
        'getXShiftIdAttribute' => 'shift_id',
        'getXScheduleLocationIdAttribute' => 'schedule_location_id',
    ];

    protected $casts = [
        'user_id' => Hash::class . ':hash',
        'created_by' => Hash::class . ':hash',
        'updated_by' => Hash::class . ':hash',
        'shift_id' => Hash::class . ':hash',
        'schedule_location_id' => Hash::class . ':hash',
        'date' => 'date',
        'time_in' => 'string',
        'time_out' => 'string',
        'schedule_type' => 'string',
    ];

    protected $defaultRelations = ['scheduleLocation'];
    protected $allowedRelations = ['scheduleLocation'];



    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function scheduleLocation()
    {
        return $this->belongsTo(Location::class, 'schedule_location_id');
    }



    // public function OverideShift()
    // {
    //     return $this->hasMany(OverideShift::class, 'user_id', 'user_id');
    // }

}
