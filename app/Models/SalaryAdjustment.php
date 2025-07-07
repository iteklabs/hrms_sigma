<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class SalaryAdjustment extends BaseModel
{
    protected $table = "salary_adjustment";

    protected $default = ["xid"];
    
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $appends = ['xid'];

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
    ];


}
