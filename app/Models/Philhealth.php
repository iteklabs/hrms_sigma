<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Philhealth extends Model
{
    protected $table = 'philhealth';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable = [
        'min_salary',
        'max_salary',
        'EE_share_fixed',
        'ER_share_fixed',
        'EE_share_percentage',
        'ER_share_percentage',
        'total_share_percentage',
    ];

    protected $casts = [
        'min_salary' => 'double',
        'max_salary' => 'double',
        'EE_share_fixed' => 'double',
        'ER_share_fixed' => 'double',
        'EE_share_percentage' => 'double',
        'ER_share_percentage' => 'double',
        'total_share_percentage' => 'double',
    ];

    protected $hidden = ['id'];
}
