<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;

class PayrollsDetl extends BaseModel
{
    protected $table = 'payrolls_detl';
    protected $default = ['xid'];
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $hidden = ['id', 'payroll_id', 'created_by', 'updated_by', 'salary_adjustment_id'];
    protected $appends = ['xid', 'x_payroll_id', 'x_created_by', 'x_updated_by', 'x_salary_adjustment_id'];

    protected $hashableGetterFunctions = [
        'getXPayrollIdAttribute' => 'payroll_id',
        'getXSalaryAdjustmentIdAttribute' => 'salary_adjustment_id',
        'getXCreatedByAttribute' => 'created_by',
        'getXUpdatedByAttribute' => 'updated_by',
    ];


    protected $casts = [
        'payroll_id' => Hash::class . ':hash',
        'salary_adjustment_id' => Hash::class . ':hash',
        'created_by' => Hash::class . ':hash',
        'updated_by' => Hash::class . ':hash',
        'title' => 'string',
        'amount' => 'double',
        'types' => 'string',
        'isTaxable' => 'boolean',
        'identity' => 'string', // 'earn' or 'dedc'
    ];

    public function PayrollMain()
    {
        return $this->hasOne(Payroll::class, 'id', 'payroll_id');
    }

    public function SalaryAdjustment()
    {
        return $this->hasOne(SalaryAdjustment::class, 'id', 'salary_adjustment_id');
    }
}
