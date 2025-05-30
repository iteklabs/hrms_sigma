<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\LeaveType\IndexRequest;
use App\Http\Requests\Api\LeaveType\StoreRequest;
use App\Http\Requests\Api\LeaveType\UpdateRequest;
use App\Http\Requests\Api\LeaveType\DeleteRequest;
use App\Models\LeaveType;

class LeaveTypeController extends ApiBaseController
{
    protected $model = LeaveType::class;

    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function storing(LeaveType $leaveType)
    {
        $request = request();
        $loggedUser = user();

        $leaveType->created_by = $loggedUser->id;
        $leaveType->max_leaves_per_month = $leaveType->is_paid ? $request->max_leaves_per_month : null;

        return $leaveType;
    }

    public function updating(LeaveType $leaveType)
    {
        $request = request();
        $leaveType->max_leaves_per_month = $leaveType->is_paid ? $request->max_leaves_per_month : null;

        return $leaveType;
    }
}
