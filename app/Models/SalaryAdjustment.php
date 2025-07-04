<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryAdjustment extends Model
{
    protected $table = "salary_adjustment";
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
