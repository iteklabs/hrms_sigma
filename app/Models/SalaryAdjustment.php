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
        'cut_off',
        'month',
        'year',
        'date_from',
        'date_to',
        'amount',
        'type',
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
        'cut_off',
        'month',
        'year',
        'date_from',
        'date_to',
        'amount',
        'user_id',
        'type',
    ];
public function user()
{
    return $this->hasOne(StaffMember::class, 'id', 'user_id');
}

}
