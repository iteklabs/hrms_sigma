<?php

namespace App\Http\Controllers\Api\Self;

use App\Http\Controllers\ApiBaseController;
use Examyou\RestAPI\ApiResponse;
use Examyou\RestAPI\Exceptions\ApiException;
use App\Classes\CommonHrm;
use App\Http\Requests\Api\Self\Leave\IndexRequest;
use App\Http\Requests\Api\Self\Leave\StoreRequest;
use App\Http\Requests\Api\Self\Leave\UpdateRequest;
use App\Http\Requests\Api\Self\Leave\DeleteRequest;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Attendance;

class LeaveController extends ApiBaseController
{
    protected $model = Leave::class;

    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    protected function modifyIndex($query)
    {
        $loggedUser = user();
        $request = request();

        // status Filters
        if ($request->has('status') && $request->status != "all") {
            $leaveStatus = $request->status;
            $query = $query->where('leaves.status', $leaveStatus);
        }

        $query = $query->where('leaves.user_id', $loggedUser->id);

        // leaveType Filters
        if ($request->has('leave_type_id') && $request->leave_type_id != "") {
            $leaveTypeId = $this->getIdFromHash($request->leave_type_id);
            $query = $query->where('leaves.leave_type_id', $leaveTypeId);
        }

        if ($request->has('year')) {
            $query->where(function ($query) use ($request) {
                $query->whereYear('start_date', $request->year)
                  ->orWhereYear('end_date', $request->year);
            });
        }

        if ($request->has('month')) {
            $query->where(function ($query) use ($request) {
                $query->whereMonth('start_date', $request->month)
                  ->orWhereMonth('end_date', $request->month);
            });
        }

        return  $query;
    }

    public function storing(Leave $leave)
    {
        $loggedUser = user();
        $request = request();

        $leave->status = 'pending';

        $leave->user_id = $loggedUser->id;

        // Throw exception if attendance already exists
        CommonHrm::checkIfAttendanceAlreadyExists($leave->user_id, $leave->start_date, $leave->end_date);

        return $leave;
    }

    public function updating(Leave $leave)
    {
        $loggedUser = user();
        $request = request();

        // Can not update the leave if it is already approved or rejcted
        if ($request->has('status') && $leave->getOriginal('status') == 'approved' || $leave->getOriginal('status') == 'rejected') {
            throw new ApiException("Leave already apporved or rejected");
        }

        // If not admin or not having persmission of leaves_approve_reject
        // Then cannot edit other user
        if ($leave->user_id != $loggedUser->id) {
            throw new ApiException("Not have valid permission");
        }

        return $leave;
    }

    public function destroying(Leave $leave)
    {
        $loggedUser = user();

        // Cannot delete approved or rejected leaves
        if ($leave->status == 'approved' || $leave->status == 'rejected') {
            throw new ApiException("Not have valid permission");
        }

        // Cannot delete other user leave if not have permission
        if ($leave->user_id != $loggedUser->id) {
            throw new ApiException("Not have valid permission");
        }

        return $leave;
    }

    public function getLeavesType()
    {
        $allLeaveTypes = LeaveType::select('id', 'name')->get();

        return ApiResponse::make('Success', [
            'data' => $allLeaveTypes
        ]);
    }

    public function remainingLeaves()
    {
        $request = request();

        // Check if user have permssion to view all leaves
        $loggedUser = user();
        $userId = $loggedUser->id;

        $allLeaveTypes = LeaveType::select('id', 'name', 'total_leaves', 'is_paid')->get();
        $year = $request->year;

        // If request year is same as current year
        $fincialDates = CommonHrm::getFincialYearStartEndDate($year);
        $startDate = $fincialDates['startDate'];
        $endDate = $fincialDates['endDate'];

        foreach ($allLeaveTypes as $allLeaveType) {
            $totalFullDayLeavesCount = Attendance::where('attendances.is_leave', 1)
                ->where('is_holiday', 0)
                ->whereBetween('attendances.date', [$startDate, $endDate])
                ->where('attendances.leave_type_id', $allLeaveType->id)
                ->where('attendances.user_id', $userId)
                ->where('attendances.is_half_day', 0);

            if ($allLeaveType->is_paid == 1) {
                $totalFullDayLeavesCount = $totalFullDayLeavesCount->where('attendances.is_paid', 1);
            }
            $totalFullDayLeavesCount = $totalFullDayLeavesCount->count();

            $totalHalfDayLeavesCount = Attendance::where('attendances.is_leave', 1)
                ->where('is_holiday', 0)
                ->whereBetween('attendances.date', [$startDate, $endDate])
                ->where('attendances.leave_type_id', $allLeaveType->id)
                ->where('attendances.user_id', $userId)
                ->where('attendances.is_half_day', 1);

            if ($allLeaveType->is_paid == 1) {
                $totalHalfDayLeavesCount = $totalHalfDayLeavesCount->where('attendances.is_paid', 1);
            }
            $totalHalfDayLeavesCount = $totalHalfDayLeavesCount->count();

            $totalLeaves = ($totalHalfDayLeavesCount / 2) + $totalFullDayLeavesCount;
            $allLeaveType->remaining_leaves = $allLeaveType->total_leaves - $totalLeaves;
        }

        return ApiResponse::make('Data fetched', [
            'data' => $allLeaveTypes
        ]);
    }

    public function unpaidLeaves()
    {
        $company = company();
        $startMonth = (int)$company->leave_start_month;
        $request = request();

        // Check if user have permssion to view all leaves
        $loggedUser = user();
        $userId = $loggedUser->id;
        $year = (int)$request->year;

        $yearMonths = [];

        $monthCounter = 0;
        for ($i = $startMonth; $i <= 12; $i++) {
            $yearMonths[] = [
                'month' => $i,
                'year'  => $year
            ];

            $monthCounter += 1;
        }

        for ($i = 1; $i <= 12 - $monthCounter; $i++) {
            $yearMonths[] = [
                'month' => $i,
                'year'  => $year + 1
            ];
        }

        $unpaidLeaveData = [];
        foreach ($yearMonths as $yearMonth) {
            $totalFullDayLeavesCount = Attendance::where('attendances.is_leave', 1)
                ->whereNotNull('attendances.leave_type_id')
                ->whereMonth('attendances.date', $yearMonth['month'])
                ->whereYear('attendances.date', $yearMonth['year'])
                ->where('attendances.user_id', $userId)
                ->where('attendances.is_half_day', 0)
                ->where('attendances.is_paid', 0)
                ->count();

            $totalHalfDayLeavesCount = Attendance::where('attendances.is_leave', 1)
                ->whereNotNull('attendances.leave_type_id')
                ->whereMonth('attendances.date', $yearMonth['month'])
                ->whereYear('attendances.date', $yearMonth['year'])
                ->where('attendances.user_id', $userId)
                ->where('attendances.is_half_day', 1)
                ->where('attendances.is_paid', 0)
                ->count();

            $totalLeaves = ($totalHalfDayLeavesCount / 2) + $totalFullDayLeavesCount;

            $unpaidLeaveData[] = [
                'month' => $yearMonth['month'] < 10 ? '0' . $yearMonth['month'] : '' . $yearMonth['month'],
                'year'  => $yearMonth['year'],
                'unpaid_leaves' => $totalLeaves
            ];
        }

        return ApiResponse::make('Data fetched', [
            'data' => $unpaidLeaveData
        ]);
    }
}