<?php

namespace App\Models;
use App\Casts\Hash;
// use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class SalaryAdjustment extends BaseModel
{
    protected $table = "salary_adjustment";

    protected $default = ["xid"];

    protected $hidden = ['id', 'user_id', 'created_by', 'updated_by'];

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $appends = ['xid', 'x_user_id'];

     protected $hashableGetterFunctions = [
        'getXUserIdAttribute' => 'user_id',
     ];

     protected $casts = [
        'user_id' => Hash::class . ':hash',
     ];

    protected $fillable = [
        'name',
        'process_payment',
        'cut_off',
        'month',
        'year',
        'date_from',
        'date_to',
        'amount',
        'type',
        'user_id'
    ];
public function user()
{
    return $this->hasOne(StaffMember::class, 'id', 'user_id');
}

}
