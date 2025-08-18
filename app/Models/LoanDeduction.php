<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Casts\Hash;

class LoanDeduction extends BaseModel
{
    protected $table = 'loan_deduction';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $hidden = ['id', 'created_by', 'updated_by', 'deleted_by', 'user_id', 'location_id'];
    protected $appends = ['xid', 'x_user_id', 'x_location_id'];
    protected $hashableGetterFunctions = [
        'getXUserIdAttribute' => 'user_id',
        'getXLocationIdAttribute' => 'location_id',
    ];
    protected $casts = [
        'user_id' => Hash::class . ':hash',
        'location_id' => Hash::class . ':hash',
        'loan_id' => 'string',
        'loan_name' => 'string',
        'type_of_loan' => 'string',
        'total_amount_loan' => 'double',
        'amount_per_payroll' => 'double',
        'payroll_deduction' => 'double',
        'type_of_deduction' => 'string',
        'sched_of_deduction' => 'string',
        'start_year_specific' => 'string',
        'start_month_specific' => 'string',
        'start_batch_specific' => 'string',
        'end_year_specific' => 'string',
        'end_month_specific' => 'string',
        'end_batch_specific' => 'string',
        'no_deductions' => 'integer',
        'start_pause' => 'boolean',
        'status' => 'string',
        'remarks' => 'string',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
        'created_by' => Hash::class . ':hash',
        'updated_by' => Hash::class . ':hash',
        'deleted_by' => Hash::class . ':hash',
    ];


    protected static function booted(){
        static::creating(function ($loan) {
            if (empty($loan->loan_id)) {
                $latestId = self::max('id') + 1;
                $loan->loan_id = 'LOAN-' . str_pad($latestId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function user()
    {
        return $this->hasOne(StaffMember::class, 'id', 'user_id');
    }

    public function location()
    {
        return $this->hasOne(Location::class, 'id', 'location_id');
    }
}
