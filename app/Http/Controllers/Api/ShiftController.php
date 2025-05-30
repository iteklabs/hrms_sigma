<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Models\StaffMember;
use Examyou\RestAPI\Exceptions\ApiException;
use App\Http\Requests\Api\Shift\IndexRequest;
use App\Http\Requests\Api\Shift\StoreRequest;
use App\Http\Requests\Api\Shift\UpdateRequest;
use App\Http\Requests\Api\Shift\DeleteRequest;
use App\Models\Shift;

class ShiftController extends ApiBaseController
{
    protected $model = Shift::class;

    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function destroying($shift)
    {
        $assignedShiftCount = StaffMember::where("users.shift_id", $shift->id)->count();

        if ($assignedShiftCount > 0) {
            throw new ApiException("Shift cann't be deleted its assigned to the staffMembers");
        }
        return $shift;
    }
}
