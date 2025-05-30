<?php

namespace App\Models;

use App\Models\BaseModel;

class SubscriptionPlan extends BaseModel
{
    protected $table = 'subscription_plans';

    protected $default = ['id', 'xid', 'name', 'description', 'modules'];

    protected $guarded = ['id', 'created_at', 'updated_at', 'default'];

    protected $hidden = ['id'];

    protected $appends = ['xid'];

    protected $filterable = ['id', 'name'];

    protected $hashableGetterFunctions = [];

    protected $casts = [
        'modules' => 'array',
        'features' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
    }
}
