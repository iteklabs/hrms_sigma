<?php

namespace App\Http\Controllers\Api\Self;

use App\Http\Controllers\ApiBaseController;
use App\Models\StaffMember;
use Examyou\RestAPI\ApiResponse;

class UsersController extends ApiBaseController
{
    public function index()
    {
        $allUsers = StaffMember::select('users.id', 'users.name', 'users.profile_image', 'users.location_id', 'users.designation_id')
            ->with(['location:id,name', 'designation:id,name'])
            ->get()->toArray();

        return ApiResponse::make('Success', $allUsers);
    }
}
