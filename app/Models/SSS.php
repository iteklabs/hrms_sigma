<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SSS extends Model
{
    protected $table = 'sss';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable = [
        'min_salary',
        'max_salary',
        'employer_share',
        'mpf_yer',
        'ec_yer',
        'employee_share',
        'mpf_ee',
    ];

    protected $casts = [
        'min_salary' => 'double',
        'max_salary' => 'double',
        'employer_share' => 'double',
        'mpf_yer' => 'double',
        'ec_yer' => 'double',
        'employee_share' => 'double',
        'mpf_ee' => 'double',
    ];

    protected $hidden = ['id'];

}
