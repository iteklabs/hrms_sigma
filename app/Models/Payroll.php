<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;

class Payroll extends BaseModel
{
    protected $table = 'payrolls';

    protected $default = ['xid'];

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $hidden = ['id', 'user_id', 'created_by', 'updated_by', 'account_id'];

    protected $appends = ['xid', 'x_user_id', 'x_created_by', 'x_updated_by', 'x_account_id'];

    protected $filterable = ['user_id'];

    protected $hashableGetterFunctions = [
        'getXUserIdAttribute' => 'user_id',
        'getXAccountIdAttribute' => 'account_id',
        'getXCreatedByAttribute' => 'created_by',
        'getXUpdatedByAttribute' => 'updated_by',
    ];

    protected $casts = [
        'account_id' => Hash::class . ':hash',
        'user_id' => Hash::class . ':hash',
        'created_by' => Hash::class . ':hash',
        'updated_by' => Hash::class . ':hash',
        'payment_date' => 'date',
        'salary_amount' => 'double',
        'pre_payment_amount' => 'double',
        'cut_off' => 'string',
        'sss_share_ee' => 'double',
        'sss_share_er' => 'double',
        'sss_mpf_ee' => 'double',
        'sss_mpf_er' => 'double',
        'sss_ec_er' => 'double',
        'pagibig_share_ee' => 'double',
        'pagibig_share_er' => 'double',
        'philhealth_share_ee' => 'double',
        'philhealth_share_er' => 'double',
        'taxable_income' => 'double',
        'tax_withheld' => 'double',
        'net_salary' => 'double',
        'total_office_time' => 'integer',
        'total_worked_time' => 'integer',
        'half_days' => 'integer',
        'regular_ot_amount' => 'double',
        'rest_day_amount' => 'double',
        'rest_day_ot_amount' => 'double',
        'legal_holiday_amount' => 'double',
        'legal_holiday_ot_amount' => 'double',
        'night_differential_amount' => 'double',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function user()
    {
        return $this->hasOne(StaffMember::class, 'id', 'user_id');
    }

    public function payrollComponents()
    {
        return $this->hasMany(PayrollComponent::class, 'payroll_id', 'id');
    }

    public function PayrollDetl(){
        return $this->hasMany(PayrollsDetl::class, 'payroll_id', 'id');
    }
}
