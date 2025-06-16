<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxBIR extends Model
{
    protected $table = 'tax_bir';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $hidden = ['id'];

    protected $appends = ['xid'];

    protected $casts = [
        'tax_percentage' => 'double',
        'fixed_amount' => 'double',
        'max_salary' => 'double',
        'min_salary' => 'double',
    ];

    public function getXidAttribute()
    {
        return $this->attributes['id'];
    }
}
