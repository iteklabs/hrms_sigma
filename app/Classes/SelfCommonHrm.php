<?php

namespace App\Classes;

use App\Models\StaffMember;
use Carbon\Carbon;
use App\Models\Holiday;
use App\Models\Attendance;

class SelfCommonHrm
{
    public static function getMinutesFromTimes($startTime, $endTime)
    {
        $timeArray = self::getDetailsArrayFromTimes($startTime, $endTime);
        $isNextDayForTime = self::isTimeForNextDate($startTime, $endTime);

        if ($isNextDayForTime) {
            $totalMinutes = ((24 - $timeArray['start_hours'] - 1) * 60) + (60 - $timeArray['start_minutes']) +  ($timeArray['end_hours'] * 60) +  $timeArray['end_minutes'];
        } else {
            $totalMinutes =  (($timeArray['end_hours'] - $timeArray['start_hours'] - 1) * 60) + (60 - $timeArray['start_minutes']) +  $timeArray['end_minutes'];
        }

        return $totalMinutes;
    }

    public static function getDetailsArrayFromTimes($startTime, $endTime)
    {
        $startTimeArray = explode(':', $startTime);
        $endTimeArray = explode(':', $endTime);

        $startTimeHour = $startTimeArray[0];
        $startTimeMinute = $startTimeArray[1];

        $endTimeHour = $endTimeArray[0];
        $endTimeMinute = $endTimeArray[1];

        return [
            'start_hours' => (int) $startTimeHour,
            'start_minutes' => (int) $startTimeMinute,
            'end_hours' => (int) $endTimeHour,
            'end_minutes' => (int) $endTimeMinute,
        ];
    }

    public static function isTimeForNextDate($startTime, $endTime)
    {
        $timeArray = self::getDetailsArrayFromTimes($startTime, $endTime);

        return $timeArray['start_hours'] > $timeArray['end_hours'] ? true : false;
    }

    public static function isLateClockedIn($officeStartTime, $clockInTime)
    {
        $isLate = false;
        $timeArray = self::getDetailsArrayFromTimes($officeStartTime, $clockInTime);

        if ($timeArray['end_hours'] > $timeArray['start_hours']) {
            $isLate = true;
        } else if ($timeArray['end_hours'] == $timeArray['start_hours']) {
            $isLate =  $timeArray['end_minutes'] <= $timeArray['start_minutes'] ? false : true;
        }

        return $isLate;
    }

    public static function getUserClockingTime($userId)
    {
        $company = company();

        $user = StaffMember::select('id', 'name', 'shift_id')->with(['shift'])
            ->where('company_id', $company->id)
            ->find($userId);

        $allIps = [];
        $allowedIpAddress = $user && $user->shift ? $user->shift->allowed_ip_address : $company->allowed_ip_address;
        if ($allowedIpAddress) {
            foreach ($allowedIpAddress as $allIpAddress) {
                $allIps[] = $allIpAddress['allowed_ip_address'];
            }
        }

        $clockInTime = $user && $user->shift ? $user->shift->clock_in_time : $company->clock_in_time;
        $clockOutTime = $user && $user->shift ? $user->shift->clock_out_time : $company->clock_out_time;
        if (!$clockInTime) {
            $clockInTime = "09:30:00";
        }
        if (!$clockOutTime) {
            $clockOutTime = "18:00:00";
        }

        // If user have shift then shift time will be return
        // Else company time will be return
        return [
            'clock_in_time' => $clockInTime,
            'clock_out_time' => $clockOutTime,
            'early_clock_in_time' => $user && $user->shift ? $user->shift->early_clock_in_time : $company->early_clock_in_time,
            'allow_clock_out_till' => $user && $user->shift ? $user->shift->allow_clock_out_till : $company->allow_clock_out_till,
            'late_mark_after' => $user && $user->shift ? $user->shift->late_mark_after : $company->late_mark_after,
            'self_clocking' => $user && $user->shift ? $user->shift->self_clocking : $company->self_clocking,
            'allowed_ip_address' => $allIps,
        ];
    }

    public static function getShiftTimeFromDate($date, $userId)
    {
        $company = company();
        $clockTiming = self::getUserClockingTime($userId);

        $clockInDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $clockTiming['clock_in_time'], $company->timezone)
            ->setTimezone('UTC');
        $clockOutDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $clockTiming['clock_out_time'], $company->timezone)
            ->setTimezone('UTC');

        return [
            'clock_in_date_time' => $clockInDateTime,
            'clock_out_date_time' => $clockOutDateTime,
        ];
    }

    public static function attendanceDetailByDate()
    {
        $request = request();

        $loggedUser = user();
        $statusDate = $request->status_date ?? [];
        $startDate = "";
        $endDate = "";

        if ($request->has('status_date') && is_array($request->status_date)) {
            $startDate = Carbon::parse($statusDate[0]);
            $endDate = Carbon::parse($statusDate[1]);
        }
        $userId = $loggedUser->id;
        $resultData = self::getAttendanceDetails($userId, $startDate, $endDate);

        return $resultData;
    }

    public static function getAttendanceDetails($userId, $startDate, $endDate)
    {
        $company = company();
        $user = StaffMember::select('id', 'name', 'shift_id')->with(['shift'])->find($userId);

        $currentDateTime = Carbon::now($company->timezone);
        $today = Carbon::now()->setTimezone($company->timezone)->startOfDay();
        $startDate = Carbon::parse($startDate)->setTimezone($company->timezone)->startOfDay();
        $endDate = Carbon::parse($endDate)->setTimezone($company->timezone)->startOfDay();

        $attendanceData = [];
        $clockedInDuration = 0;
        $totalLateDays = 0;
        $totalHalfDays = 0;
        $totalPaidLeave = 0;
        $paidLeaveCount = 0;
        $totalUnPaidLeave = 0;
        $totalHolidayCount = 0;
        $totalDays = 0;

        $shiftDetails = self::getUserClockingTime($userId);

        $shiftClockInTime = $shiftDetails['clock_in_time'];
        $shiftClockOutTime = $shiftDetails['clock_out_time'];
        $officeHoursInMinutes = self::getMinutesFromTimes($shiftClockInTime, $shiftClockOutTime);

        $allAttendances = Attendance::with(['leave:id,leave_type_id,reason', 'leave.leaveType:id,name', 'holiday'])
            ->whereBetween('attendances.date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('attendances.user_id', $userId)
            ->get();
        $allHolidays = Holiday::whereBetween('holidays.date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        $totalWorkingDays = 0;
        $totalPresentDays = 0;

        while ($endDate->gte($startDate)) {
            $currentDate = $endDate->copy();
            $isAttendanceDataFound = $allAttendances->filter(function ($allAttendance) use ($currentDate) {
                return $allAttendance->date->format('Y-m-d') == $currentDate->format('Y-m-d');
            })->first();

            $isHolidayDataFound = $allHolidays->filter(function ($allHoliday) use ($currentDate) {
                return $allHoliday->date->format('Y-m-d') == $currentDate->format('Y-m-d');
            })->first();

            $totalWorkDurationInMinutes = 0;
            $isHoliday = false;
            $isLeave = false;
            $holidayName = '';
            $leaveName = '';
            $leaveReason = '';
            $totalLateTime = 0;
            $lateTime = 0;

            if ($currentDate->gt($today)) {
                $status = 'upcoming';
            } else if ($isHolidayDataFound) {
                $status = 'holiday';
                $isHoliday = true;
                $holidayName = $isHolidayDataFound->name;

                $totalHolidayCount += 1;
            } else if ($isAttendanceDataFound) {
                if ($isAttendanceDataFound->leave_type_id && $isAttendanceDataFound->is_half_day) {
                    $status = 'half_day';
                    $isLeave = true;
                    $leaveName = $isAttendanceDataFound->leave && $isAttendanceDataFound->leave->leaveType ? $isAttendanceDataFound->leave->leaveType->name : '';
                    $leaveReason = $isAttendanceDataFound->leave ? $isAttendanceDataFound->leave->reason : '';

                    $totalPresentDays += 0.5;
                    $totalHalfDays += 1;
                } else if ($isAttendanceDataFound->leave_type_id) {
                    $status = 'absent';
                    $isLeave = true;
                    $leaveName = $isAttendanceDataFound->leave && $isAttendanceDataFound->leave->leaveType ? $isAttendanceDataFound->leave->leaveType->name : '';
                    $leaveReason = $isAttendanceDataFound->leave ? $isAttendanceDataFound->leave->reason : '';
                } else {
                    $status = 'present';

                    $totalPresentDays += 1;
                }

                if ($status == 'half_day' || $status == 'present') {
                    // If attendance date is same as today date
                    // And user not clocked out
                    // Then we will calcualte the difference
                    if ($isAttendanceDataFound->clock_in_date_time && $isAttendanceDataFound->clock_in_date_time->format('Y-m-d') == $today->format('Y-m-d') && $isAttendanceDataFound->clock_out_date_time == null) {

                        $totalWorkDurationInMinutes = $currentDateTime->diffInMinutes($isAttendanceDataFound->clock_in_time);
                        //if date is less than today date 
                    } else if ($isAttendanceDataFound->clock_in_date_time && $isAttendanceDataFound->clock_in_date_time && Carbon::parse($isAttendanceDataFound->clock_in_date_time)->lt($today) && $isAttendanceDataFound->clock_out_date_time == null) {
                        $totalWorkDurationInMinutes = Carbon::parse($isAttendanceDataFound->office_clock_out_time)->diffInMinutes(Carbon::parse($isAttendanceDataFound->clock_in_time));
                    } else if ($isAttendanceDataFound->clock_in_date_time != null && $isAttendanceDataFound->clock_out_date_time != null) {
                        // User have clock in and clock out time
                        $totalWorkDurationInMinutes =  Carbon::parse($isAttendanceDataFound->clock_out_time)->diffInMinutes(Carbon::parse($isAttendanceDataFound->clock_in_time));
                    } else if ($user && $user->shift) {
                        $shiftClockOutTime =  $user->shift->clock_out_time;

                        // If user assigned a shift
                        $totalWorkDurationInMinutes = $shiftClockOutTime->diffInMinutes(Carbon::parse($isAttendanceDataFound->clock_in_time));
                    } else {
                        // If shift not defined then take setting from company
                    }

                    if ($isAttendanceDataFound->is_late) {
                        $isLateClockedIn = CommonHrm::isLateClockedIn($isAttendanceDataFound->office_clock_in_time, $isAttendanceDataFound->clock_in_time);

                        if ($isLateClockedIn) {
                            $lateTime =  CommonHrm::getMinutesFromTimes($isAttendanceDataFound->office_clock_in_time, $isAttendanceDataFound->clock_in_time);
                        } else {
                            $lateTime = 0;
                        }
                    } else {
                        $lateTime = 0;
                    }
                }


                if ($isAttendanceDataFound->is_paid) {
                    $totalPaidLeave += $isAttendanceDataFound->is_half_day ? 0.5 : 1;

                    if ($isAttendanceDataFound->leave_type_id) {
                        $paidLeaveCount += $isAttendanceDataFound->is_half_day ? 0.5 : 1;
                    }
                } else {
                    $totalUnPaidLeave += $isAttendanceDataFound->is_half_day ? 0.5 : 1;
                }

                $totalWorkingDays += 1;
            } else {
                $status = 'not_marked';
                $totalWorkingDays += 1;
            }

            if ($status != 'upcoming') {
                $isUserLate = $isAttendanceDataFound && $isAttendanceDataFound->is_late ? $isAttendanceDataFound->is_late : 0;

                $attendanceData[] = [
                    'date' => $currentDate->format('Y-m-d'),
                    'status' => $status,
                    'is_holiday' => $isHoliday,
                    'holiday_name' => $holidayName,
                    'is_leave' => $isLeave,
                    'leave_name' => $leaveName,
                    'is_late'   => $isUserLate,
                    'late_time' => $lateTime,
                    'clock_in'  => $isAttendanceDataFound ? $isAttendanceDataFound->clock_in_date_time : '',
                    'clock_out'  => $isAttendanceDataFound ? $isAttendanceDataFound->clock_out_date_time : '',
                    'clock_in_ip'  => $isAttendanceDataFound ? $isAttendanceDataFound->clock_in_ip_address : '',
                    'clock_out_ip'  => $isAttendanceDataFound ? $isAttendanceDataFound->clock_out_ip_address : '',
                    'leave_reason'  => $leaveReason,
                    'worked_time'  => $totalWorkDurationInMinutes
                ];

                $totalLateDays += $isUserLate;
                $clockedInDuration += $totalWorkDurationInMinutes;
            }

            $totalDays += 1;

            $endDate->subDay();
            $totalLateTime = collect($attendanceData)->sum('late_time');
        }

        return [
            'data'  => $attendanceData,
            'total_days'  => $totalDays,
            'working_days'  => $totalWorkingDays,
            'present_days'  => $totalPresentDays,
            'paid_leaves'  => $paidLeaveCount,
            'half_days'  => $totalHalfDays,
            'office_time'  => $officeHoursInMinutes,
            'total_office_time'  => $totalWorkingDays * $officeHoursInMinutes,
            'clock_in_duration'  => $clockedInDuration,
            'total_late_days'  => $totalLateDays,
            'total_paid_leaves' => $totalPaidLeave,
            'total_unpaid_leaves' => $totalUnPaidLeave,
            'holiday_count' => $totalHolidayCount,
            'total_late_time' => $totalLateTime
        ];
    }
}
