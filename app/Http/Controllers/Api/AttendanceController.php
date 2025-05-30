<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use Carbon\Carbon;
use Examyou\RestAPI\ApiResponse;
use App\Classes\CommonHrm;
use App\Http\Requests\Api\Attendance\IndexRequest;
use App\Http\Requests\Api\Attendance\StoreRequest;
use App\Http\Requests\Api\Attendance\UpdateRequest;
use App\Http\Requests\Api\Attendance\DeleteRequest;
use App\Models\Attendance;

class AttendanceController extends ApiBaseController
{
    protected $model = Attendance::class;

    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;


    protected function modifyIndex($query)
    {
        $loggedUser = user();
        $request = request();

        if ($request->has('date') && $request->date != '') {
            $date = explode(',', $request->date);
            $startDate = $date[0];
            $endDate = $date[1];

            $query = $query->whereRaw('attendances.date >= ?', [$startDate])
                ->whereRaw('attendances.date <= ?', [$endDate]);
        }

        if ($loggedUser->ability('admin', 'attendances_view')) {
            $query = $this->applyVisibility($query, 'attendances');

            if ($request->has('user_id')) {
                $userId = $this->getIdFromHash($request->user_id);
                $query = $query->where('attendances.user_id', $userId);
            }
        } else {
            $query = $query->where('attendances.user_id', $loggedUser->id);
        }

        if ($request->has('status') && $request->status != "all") {
            $attendance = $request->status;
            $query = $query->where('attendances.status', $attendance);
        };

        return  $query;
    }

    public function storing($attendance)
    {
        return $this->updateAddEditing($attendance);
    }

    public function updating($attendance)
    {
        return $this->updateAddEditing($attendance, 'edit');
    }

    public function updateAddEditing($attendance, $addEditType = 'add')
    {
        $company = company();
        $request = request();

        $userId = $this->getIdFromHash($request->user_id);
        $date = $request->date;

        // Throw exception if attendance already exists
        if ($addEditType == 'add' || ($attendance->isDirty('date') && $addEditType == 'edit')) {
            CommonHrm::checkIfAttendanceAlreadyExists($userId, $date);
        }

        $clockIn = $request->clock_in_time;
        $clockOut = $request->clock_out_time;

        $officeShiftTiming = CommonHrm::getUserClockingTime($userId);
        $attendance->office_clock_in_time = $officeShiftTiming['clock_in_time'];
        $attendance->office_clock_out_time = $officeShiftTiming['clock_out_time'];


        // For inserting the clocking date time
        $clockInDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $clockIn, $company->timezone)
            ->setTimezone('UTC');

        $totalMinutes = CommonHrm::getMinutesFromTimes($clockIn, $clockOut);
        $clockoutDateTime = $clockInDateTime->copy()->addMinutes($totalMinutes);
        $attendance->clock_in_date_time = $clockInDateTime->copy()->format('Y-m-d H:i:s');
        $attendance->clock_out_date_time = $clockoutDateTime->format('Y-m-d H:i:s');
        $attendance->total_duration = $totalMinutes;

        // No need of timezone because we need the date object

        if ($request->has('is_half_day') && $request->is_half_day == 1 && $request->has('leave_type_id') && $request->leave_type_id != '') {
            $leaveTypeId = $this->getIdFromHash($request->leave_type_id);
            $attendance->is_leave = 1;
            $attendance->is_half_day = 1;
            $attendance->leave_type_id = $leaveTypeId;

            // Check and update leave type is paid or unpaid
            $isPaidOrNot = CommonHrm::isPaidLeaveOrNot($date, $userId, $leaveTypeId);
            $attendance->is_paid = $isPaidOrNot['isPaid'];
        } else {

            $attendance->is_leave = 0;
            $attendance->is_half_day = 0;
            $attendance->leave_type_id = null;
            $attendance->is_paid = 1;
        }

        // For changing the status
        if ($attendance->is_half_day) {
            $attendance->status = "half_day";
        } else if ($attendance->leave_type_id) {
            $attendance->status = "on_leave";
        } else {
            $attendance->status = "present";
        }

        return $attendance;
    }

    public function attendanceSummary()
    {
        $detail = CommonHrm::attendanceDetail();

        return ApiResponse::make('Data fetched', [
            'columns' => $detail['columns'],
            'dateRange' => $detail['dateRange'],
            'data' => $detail['data'],
            'meta'  => [
                'paging' => [
                    'total' => $detail['totalRecords']
                ]
            ]
        ]);
    }

    public function attendanceSummaryByMonth()
    {
        $result = CommonHrm::attendanceDetailByMonth();

        return ApiResponse::make('Data fetched', $result);
    }
}
