<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pagibig extends Model
{
    protected $table = 'pagibig';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable = [
        'min_salary',
        'max_salary',
        'employer_share',
        'employee_share',
        'employer_share_percentage',
        'employee_share_percentage',
        'total_share_percentage'
    ];

    protected $casts = [
        'min_salary' => 'double',
        'max_salary' => 'double',
        'employer_share' => 'double',
        'employee_share' => 'double',
        'employer_share_percentage' => 'double',
        'employee_share_percentage' => 'double',
    ];

    protected $hidden = ['id'];
}
