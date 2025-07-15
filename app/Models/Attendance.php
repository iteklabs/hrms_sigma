<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;

class Attendance extends BaseModel
{
    protected $table = 'attendances';

    protected $default = ['xid', 'x_user_id', 'x_holiday_id', 'x_leave_id', 'x_leave_type_id'];

    protected $guarded = [
        'id',
        'is_holiday',
        'is_leave',
        'holiday_id',
        'clock_in_date_time',
        'clock_out_date_time',
        'is_paid',
        'office_clock_in_time',
        'office_clock_out_time',
        'created_at',
        'updated_at',
        'regular_ot',
        'legal_holiday',
        'legal_holiday_ot',
        'special_holiday',
        'special_holiday_ot',
        'rest_day',
        'rest_day_ot',
        'rest_day_special_holiday',
        'rest_day_special_holiday_ot',
        'night_differential',
        'no_of_hrs_undertime',
        'no_of_hrs_late',
        // 'date_out'
    ];

    protected $hidden = ['id', 'user_id', 'leave_id', 'leave_type_id', 'holiday_id'];

    protected $appends = ['xid', 'x_user_id', 'x_leave_id', 'x_leave_type_id', 'x_holiday_id'];

    protected $filterable = ['name'];

    protected $hashableGetterFunctions = [
        'getXUserIdAttribute' => 'user_id',
        'getXLeaveIdAttribute' => 'leave_id',
        'getXLeaveTypeIdAttribute' => 'leave_type_id',
        'getXHolidayIdAttribute' => 'holiday_id',
    ];

    protected $casts = [
        'user_id' => Hash::class . ':hash',
        'leave_id' => Hash::class . ':hash',
        'leave_type_id' => Hash::class . ':hash',
        'holiday_id' => Hash::class . ':hash',
        'date' => 'date:Y-m-d',
        'date_out' => 'date:Y-m-d',
        'clock_in_date_time' => 'datetime',
        'clock_out_date_time' => 'datetime',
        'is_paid' => 'boolean'
    ];
    protected $permissions = ['leaves_view'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function user()
    {
        return $this->hasOne(StaffMember::class, 'id', 'user_id');
    }

    public function leave()
    {
        return $this->hasOne(Leave::class, 'id', 'leave_id');
    }

    public function leaveType()
    {
        return $this->hasOne(LeaveType::class, 'id', 'leave_type_id');
    }

    public function holiday()
    {
        return $this->hasOne(Holiday::class, 'id', 'holiday_id');
    }
}
