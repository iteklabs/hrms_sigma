<?php

namespace App\Models;
use App\Casts\Hash;
// use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class SalaryAdjustment extends BaseModel
{
    protected $table = "salary_adjustment";

    protected $default = ["xid", "x_user_id"];

    protected $guarded = [
        'id',
        'name',
        'process_payment',
        'amount',
        'type',
        'adjustment_type',
        'start_cut_off_specific',
        'start_month_specific',
        'start_year_specific',
        'end_cut_off_specific',
        'end_month_specific',
        'end_year_specific',
        'cut_off_specific',
        'month_specific',
        'year_specific',
    ];

    protected $hidden = ['id', 'user_id', 'created_by', 'updated_by'];
    
    protected $appends = ['xid', 'x_user_id'];

     protected $hashableGetterFunctions = [
        'getXUserIdAttribute' => 'user_id',
     ];

     protected $casts = [
        'user_id' => Hash::class . ':hash',
        'name' => 'string'
     ];

    protected $fillable = [
        'name',
        'process_payment',
        'amount',
        'user_id',
        'type',
        'adjustment_type',
        'start_cut_off_specific',
        'start_month_specific',
        'start_year_specific',
        'end_cut_off_specific',
        'end_month_specific',
        'end_year_specific',
        'cut_off_specific',
        'month_specific',
        'year_specific',
    ];
public function user()
{
    return $this->hasOne(StaffMember::class, 'id', 'user_id');
}

}
