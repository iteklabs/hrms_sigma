<?php

namespace App\Classes;

use App\Models\Account;
use App\Models\AccountEntry;
use App\Models\Appreciation;
use App\Models\Asset;
use App\Models\StaffMember;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Examyou\RestAPI\Exceptions\ApiException;
use App\Models\Holiday;
use App\Models\LeaveType;
use App\Models\Attendance;
use App\Models\Deposit;
use App\Models\Expense;
use App\Models\Generate;
use App\Models\Leave;
use App\Models\Payroll;
use App\Models\PrePayment;
use App\Models\Shift;
use App\Models\Company;
use App\Models\Attendance_detl;
use App\Models\OverideShift;
use DateTime;

class CommonHrm
{
    // public static function getMinutesFromTimes($startTime, $endTime)
    // {
    //     $timeArray = self::getDetailsArrayFromTimes($startTime, $endTime);
    //     $isNextDayForTime = self::isTimeForNextDate($startTime, $endTime);

    //     if ($isNextDayForTime) {
    //         $totalMinutes = ((24 - $timeArray['start_hours'] - 1) * 60) + (60 - $timeArray['start_minutes']) +  ($timeArray['end_hours'] * 60) +  $timeArray['end_minutes'];
    //     } else {
    //         $totalMinutes =  (($timeArray['end_hours'] - $timeArray['start_hours'] - 1) * 60) + (60 - $timeArray['start_minutes']) +  $timeArray['end_minutes'];
    //     }

    //     return $totalMinutes;
    // }

    
    public static function getMinutesFromTimes($startTime, $endTime, $dateIn = null, $dateOut = null)
    {
        // If dateIn and dateOut are provided, use Carbon for accurate calculation
        if ($dateIn && $dateOut) {
            $start = \Carbon\Carbon::parse($dateIn . ' ' . $startTime);
            $end = \Carbon\Carbon::parse($dateOut . ' ' . $endTime);
            return abs($end->diffInMinutes($start));
        }

        // Fallback to original logic if only times are provided
        $timeArray = self::getDetailsArrayFromTimes($startTime, $endTime);
        $isNextDayForTime = self::isTimeForNextDate($startTime, $endTime);

        if ($isNextDayForTime) {
            $totalMinutes = ((24 - $timeArray['start_hours'] - 1) * 60) + (60 - $timeArray['start_minutes']) +  ($timeArray['end_hours'] * 60) +  $timeArray['end_minutes'];
        } else {
            $totalMinutes =  (($timeArray['end_hours'] - $timeArray['start_hours'] - 1) * 60) + (60 - $timeArray['start_minutes']) +  $timeArray['end_minutes'];
        }

        return $totalMinutes;
    }

    public static function createUpdateGenerate($object)
    {
        $request = request();
        $loggedUser = user();

        // Previous no letter head template selected but now selected
        if ($object->getOriginal('letterhead_template_id') == '' && $object->letterhead_template_id != '' && $request->letterhead_description != '') {
            $generate = new Generate();
            $generate->user_id = $object->user_id;
            $generate->letterhead_template_id = $object->letterhead_template_id;
            $generate->title = $request->letterhead_title;
            $generate->description = $request->letterhead_description;
            $generate->admin_id = $loggedUser->id;
            $generate->save();

            $object->generates_id = $generate->id;
        } else if ($object->letterhead_template_id && $request->letterhead_description != '' && $object->generates_id) {
            // Previous letter head template selected and now new one selected
            $generate = Generate::find($object->generates_id);
            $generate->user_id = $object->user_id;
            $generate->letterhead_template_id = $object->letterhead_template_id;
            $generate->title = $request->letterhead_title;
            $generate->description = $request->letterhead_description;
            $generate->save();
        } else if ($object->getOriginal('letterhead_template_id') != '' && $object->letterhead_template_id == '' && $object->generates_id) {
            // Previous letter head template selected and generate exists but now letterhead templated not selected
            Generate::destroy($object->generates_id);
        }

        return $object;
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

    public static function updateAccountAmount($id)
    {

        $totalPrePayment = PrePayment::where('account_id', $id)->sum('amount');

        $totalDeposite = Deposit::where('account_id', $id)->sum('amount');

        $totalPayRoll = Payroll::where('account_id', $id)->where('status', 'paid')->sum('net_salary');

        $totalExpense = Expense::where('account_id', $id)->where('status', 'approved')
            ->sum('amount');

        $totalAsset = Asset::where('account_id', $id)
            ->sum('price');

        $totalAppreciation = Appreciation::where('account_id', $id)
            ->sum('price_amount');

        $totalAccountSum = Account::where('accounts.id', $id)
            ->sum('initial_balance');


        // Calculate the final total amount
        $totalAmount =  $totalAccountSum - $totalExpense - $totalPayRoll +  $totalDeposite - $totalPrePayment - $totalAsset - $totalAppreciation;
        $AccountUpdate = Account::find($id);

        if ($AccountUpdate) {
            $AccountUpdate->balance = $totalAmount;
            $AccountUpdate->save();
        }
    }

    public static function insertAccountEntries($accountId, $oldAccountId, $type, $date, $id, $amount)
    {
        if ($type == 'payroll') {
            $accountEntries = AccountEntry::where('account_id', $accountId)->where('type', $type)->where('payroll_id', $id)->first();
            if (!$accountEntries) {
                $accountEntries = new AccountEntry();
            };
            $accountEntries->payroll_id = $id;
            $accountEntries->is_debit = 1;
        } else if ($type == 'pre_payment') {
            $accountEntries = AccountEntry::where('type', $type)->where('pre_payment_id', $id)->first();
            if (!$accountEntries) {
                $accountEntries = new AccountEntry();
            };
            $accountEntries->pre_payment_id = $id;
            $accountEntries->is_debit = 1;
        } else if ($type == 'expense') {
            $accountEntries = AccountEntry::where('type', $type)->where('expense_id', $id)->first();
            if (!$accountEntries) {
                $accountEntries = new AccountEntry();
            };
            $accountEntries->expense_id = $id;
            $accountEntries->is_debit = 1;
        } else if ($type == 'asset') {
            if ($oldAccountId == null) {
                $accountEntries = new AccountEntry();
            } else {
                $accountEntries = AccountEntry::where('account_id', $oldAccountId)->where('type', $type)->where('asset_id', $id)->first();
            };
            $accountEntries->asset_id = $id;
            $accountEntries->is_debit = 1;
        } else if ($type == 'appreciation') {
            if ($oldAccountId == null) {
                $accountEntries = new AccountEntry();
            } else {
                $accountEntries = AccountEntry::where('account_id', $oldAccountId)->where('type', $type)->where('appreciation_id', $id)->first();
            };
            $accountEntries->appreciation_id = $id;
            $accountEntries->is_debit = 1;
        } else if ($type == 'deposit') {
            if ($oldAccountId == null) {
                $accountEntries = new AccountEntry();
            } else {
                $accountEntries = AccountEntry::where('account_id', $oldAccountId)->where('type', $type)->where('deposit_id', $id)->first();
            };
            $accountEntries->deposit_id = $id;
            $accountEntries->is_debit = 0;
        } else if ($type == 'initial_balance') {
            $accountEntries = AccountEntry::where('account_id', $accountId)->where('type', $type)->where('initial_account_id', $id)->first();
            if (!$accountEntries) {
                $accountEntries = new AccountEntry();
            };
            $accountEntries->initial_account_id = $id;
            $accountEntries->is_debit = 0;
        }
        $accountEntries->account_id = $accountId;
        $accountEntries->date = $date;
        $accountEntries->type = $type;
        $accountEntries->amount = $amount;
        $accountEntries->save();
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

    public static function getFincialYearStartEndDate($year)
    {
        $company = company();
        $startMonth = (int)$company->leave_start_month;

        $startDate = Carbon::create($year, $startMonth, 1, 0, 0, 0)->setTimezone('UTC')->startOfDay();
        $endDate = $startDate->copy()->addYear()->subDay();

        return [
            'startDate' => $startDate,
            'endDate'   => $endDate
        ];
    }

    public static function isPaidLeaveOrNot($date, $userId, $leaveTypeId)
    {
        $isPaid = true;
        $isHoliday = Holiday::whereDate('date', $date)->count();
        $leaveType = LeaveType::find($leaveTypeId);
        $maxLeavePerMonth = $leaveType->max_leaves_per_month;

        // Getting Fincial Year
        $fincialYear = self::getFincialYearFromDate($date);

        $dateDetails = self::getDateDetails($date);
        // Total Leave This Month
        $leaveDateMonth = $dateDetails['month'];
        $paidLeaveTakenThisMonth = self::totalPaidLeavesByYearMonth($leaveType->id, $userId, $fincialYear, $leaveDateMonth);
        $totalLeaveTakenThisYear = self::totalPaidLeavesByYear($leaveType->id, $userId, $fincialYear);

        if ($isHoliday == 0) {
            // Total leaves taken in this year (finical year)
            if ($totalLeaveTakenThisYear >= $leaveType->total_leaves || ($maxLeavePerMonth != null && $paidLeaveTakenThisMonth >= $maxLeavePerMonth)) {
                $isPaid = false;
            } else {
                $isPaid = true;
            }
        }

        // If leave is unpaid then directly set to unpaid
        // Otherwise according to above condition
        $isPaid = $leaveType->is_paid == 0 ? 0 : $isPaid;

        return [
            'isHoliday' => $isHoliday > 0 ? true : false,
            'isPaid'   => $isPaid,
            'paidLeaveTakenThisMonth' => $paidLeaveTakenThisMonth,
            'totalLeaveTakenThisYear' => $totalLeaveTakenThisYear,
            'totalLeaves' => $leaveType->total_leaves,
            'maxLeavePerMonth' => $maxLeavePerMonth,
        ];
    }

    public static function getDateDetails($date)
    {
        $dateArray = explode('-', $date);

        return [
            'year' => $dateArray[0],
            'month' => $dateArray[1],
            'day' => $dateArray[2],
        ];
    }

    // Get Fincial Year from a date
    public static function getFincialYearFromDate($date)
    {
        $dateDetails = self::getDateDetails($date);
        $company = company();
        $dateYear = $dateDetails['year'];
        $startMonth = (int)$company->leave_start_month;

        // Set Date as Date Object
        $dateObject = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 00:00:00', $company->timezone);
        $startDate = Carbon::create($dateYear, $startMonth, 1)->setTimezone($company->timezone)->startOfDay();
        $endDate = $startDate->copy()->addYear()->subDay();

        // If current
        if (!$dateObject->between($startDate, $endDate)) {
            $dateYear -= 1;
        }

        return $dateYear;
    }

    public static function totalPaidLeavesByYearMonth($leaveTypeId, $userId, $year, $month)
    {
        $totalFullDayLeavesCount = Attendance::join('leaves', 'leaves.id', '=', 'attendances.leave_id')
            ->whereNotNull('attendances.leave_id')
            ->whereYear('attendances.date', $year)
            ->whereMonth('attendances.date', $month)
            ->where('leaves.leave_type_id', $leaveTypeId)
            ->where('attendances.user_id', $userId)
            ->where('attendances.is_half_day', 0)
            ->where('attendances.is_paid', 1)
            ->count();

        $totalHalfDayLeavesCount = Attendance::join('leaves', 'leaves.id', '=', 'attendances.leave_id')
            ->whereNotNull('attendances.leave_id')
            ->whereYear('attendances.date', $year)
            ->whereMonth('attendances.date', $month)
            ->where('leaves.leave_type_id', $leaveTypeId)
            ->where('attendances.user_id', $userId)
            ->where('attendances.is_half_day', 1)
            ->where('attendances.is_paid', 1)
            ->count();

        $totalLeaves = ($totalHalfDayLeavesCount / 2) + $totalFullDayLeavesCount;

        return $totalLeaves;
    }

    public static function totalPaidLeavesByYear($leaveTypeId, $userId, $year)
    {
        $fincialDates = self::getFincialYearStartEndDate($year);
        $startDate = $fincialDates['startDate'];
        $endDate = $fincialDates['endDate'];

        $totalFullDayLeavesCount = Attendance::join('leaves', 'leaves.id', '=', 'attendances.leave_id')
            ->whereNotNull('attendances.leave_id')
            ->whereBetween('attendances.date', [$startDate, $endDate])
            ->where('leaves.leave_type_id', $leaveTypeId)
            ->where('attendances.user_id', $userId)
            ->where('attendances.is_half_day', 0)
            ->where('attendances.is_paid', 1)
            ->count();

        $totalHalfDayLeavesCount = Attendance::join('leaves', 'leaves.id', '=', 'attendances.leave_id')
            ->whereNotNull('attendances.leave_id')
            ->whereBetween('attendances.date', [$startDate, $endDate])
            ->where('leaves.leave_type_id', $leaveTypeId)
            ->where('attendances.user_id', $userId)
            ->where('attendances.is_half_day', 1)
            ->where('attendances.is_paid', 1)
            ->count();

        $totalLeaves = ($totalHalfDayLeavesCount / 2) + $totalFullDayLeavesCount;

        return $totalLeaves;
    }

    public static function checkIfAttendanceAlreadyExists($userId, $startDate, $endDate = null)
    {
        if ($endDate != null) {
            $allDates = CarbonPeriod::create($startDate, $endDate);

            foreach ($allDates as $allDate) {
                $attendaceCount = Attendance::where('user_id', $userId)->whereDate('date', $allDate->format("Y-m-d"))->count();

                if ($attendaceCount > 0) {
                    throw new ApiException("Attendance already exists for date " . $allDate->format("Y-m-d"));
                }
            }
        } else {
            $attendaceCount = Attendance::where('user_id', $userId)->whereDate('date', $startDate)->count();

            if ($attendaceCount > 0) {
                throw new ApiException("Attendance already exists for date " . $startDate);
            }
        }
    }

    public static function getIpAddress()
    {
        $ipaddress = '';

        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if (isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';

        return $ipaddress;
    }
    

    public static function getTodayAttendanceDetails()
    {
        $request = request();
        $company = company();
        $user = user();

        $shiftClockInTime = self::getUserClockingTime($user->id);
        $currentDateTimeObject = Carbon::now($company->timezone);
        $currentTime = $currentDateTimeObject->copy()->format('H:i:s');
        $currentDate = $currentDateTimeObject->copy()->format('Y-m-d');

        $earlyClockInMinutes = $shiftClockInTime && $shiftClockInTime['early_clock_in_time'] ? $shiftClockInTime['early_clock_in_time'] : 0;
        $clockOutAfterInMinutes = $shiftClockInTime && $shiftClockInTime['allow_clock_out_till'] ? $shiftClockInTime['allow_clock_out_till'] : 0;

        $showClockedInButton = false;
        $showClockedOutButton = false;
        $officeHoursExpired = false;

        // Early Office Start Time
        $earlyOfficeStartTime = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate . ' ' . $shiftClockInTime['clock_in_time'], $company->timezone)
            ->subMinutes($earlyClockInMinutes);
        // Office hours passed
        $maxOfficeEndTime = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate . ' ' . $shiftClockInTime['clock_out_time'], $company->timezone)
            ->addMinutes($clockOutAfterInMinutes);

        // If current time is greater than office early time
        // Then show clock in button
        if ($currentDateTimeObject->copy()->gte($earlyOfficeStartTime)) {
            $showClockedInButton = true;
        }

        // If current time is greate than max time of office
        // It mean office hours passed and cannot login and logout
        if ($currentDateTimeObject->copy()->lte($maxOfficeEndTime)) {
            $showClockedInButton = true;
            $showClockedOutButton = true;
        } else {
            $showClockedInButton = false;
            $showClockedOutButton = false;

            $officeHoursExpired = true;
        }

        $isUserAttendanceExists = Attendance::whereDate('attendances.date', $currentDate)
            ->where('attendances.user_id', $user->id)
            ->first();

        $isOnFullDayLeave = false;
        $isClockedIn = false;
        $isClockedOut = false;
        $clockInTime = null;
        $clockInDateTime = null;
        $clockOutTime = null;
        $clockOutDateTime = null;
        if ($isUserAttendanceExists) {

            if ($isUserAttendanceExists->is_leave && $isUserAttendanceExists->is_half_day == 0) {
                $showClockedInButton = false;
                $showClockedOutButton = false;

                $isOnFullDayLeave = true;
            } else {
                // If user clocked in then don't show clock in button
                if ($isUserAttendanceExists->clock_in_time) {
                    $isClockedIn = true;
                    $showClockedInButton = false;
                }

                if ($isUserAttendanceExists->clock_out_time) {
                    $isClockedOut = true;
                    $showClockedOutButton = false;
                }
            }

            $clockInTime = $isUserAttendanceExists->clock_in_time;
            $clockInDateTime = $isUserAttendanceExists->clock_in_date_time;
            $clockOutTime = $isUserAttendanceExists->clock_out_time;
            $clockOutDateTime = $isUserAttendanceExists->clock_out_date_time;
        }

        return [
            'date' => $currentDate,
            'time' => $currentTime,
            'is_on_full_day_leave' => $isOnFullDayLeave,
            'is_clocked_in' => $isClockedIn,
            'is_clocked_out' => $isClockedOut,
            'office_hours_expired' => $officeHoursExpired,
            'clock_in_time' => $clockInTime,
            'clock_in_date_time' => $clockInDateTime,
            'clock_out_time' => $clockOutTime,
            'clock_out_date_time' => $clockOutDateTime,
            'show_clock_in_button' => $showClockedInButton,
            'show_clock_out_button' => $showClockedOutButton,
            'office_clock_in_time' => $shiftClockInTime['clock_in_time'],
            'office_clock_out_time' => $shiftClockInTime['clock_out_time'],
            'self_clocking' => $shiftClockInTime['self_clocking'],
        ];
    }

    // public static function getMonthYearAttendanceDetails($userId, $month, $year)
    // {
    //     $company = company();
    //     $user = StaffMember::select('id', 'name', 'shift_id')->with(['shift'])->find($userId);

    //     $currentDateTime = Carbon::now($company->timezone);
    //     $today = Carbon::now($company->timezone)->startOfDay();
    //     $startDate = Carbon::createFromDate($year, $month, 1, $company->timezone)->startOfDay();
    //     $endDate = $startDate->copy()->endOfMonth()->startOfDay();

    //     $attendanceData = [];
    //     $lateTime = 0;
    //     $clockedInDuration = 0;
    //     $totalLateDays = 0;
    //     $totalHalfDays = 0;
    //     $totalPaidLeave = 0;
    //     $paidLeaveCount = 0;
    //     $totalUnPaidLeave = 0;
    //     $totalHolidayCount = 0;
    //     $totalDays = 0;

    //     $shiftDetails = self::getUserClockingTime($userId);

    //     $shiftClockInTime = $shiftDetails['clock_in_time'];
    //     $shiftClockOutTime = $shiftDetails['clock_out_time'];
    //     $officeHoursInMinutes = self::getMinutesFromTimes($shiftClockInTime, $shiftClockOutTime);

    //     $allAttendances = Attendance::with(['leave:id,leave_type_id,reason', 'leave.leaveType:id,name', 'holiday'])
    //         ->whereBetween('attendances.date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
    //         ->where('attendances.user_id', $userId)
    //         ->get();
    //     $allHolidays = Holiday::whereBetween('holidays.date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
    //         ->get();

    //     $totalWorkingDays = 0;
    //     $totalPresentDays = 0;

    //     while ($endDate->gte($startDate)) {
    //         $currentDate = $endDate->copy();

    //         $isAttendanceDataFound = $allAttendances->filter(function ($allAttendance) use ($currentDate) {
    //             return $allAttendance->date->format('Y-m-d') == $currentDate->format('Y-m-d');
    //         })->first();

    //         $isHolidayDataFound = $allHolidays->filter(function ($allHoliday) use ($currentDate) {
    //             return $allHoliday->date->format('Y-m-d') == $currentDate->format('Y-m-d');
    //         })->first();

    //         $totalWorkDurationInMinutes = 0;
    //         $isHoliday = false;
    //         $isLeave = false;
    //         $holidayName = '';
    //         $leaveName = '';
    //         $leaveReason = '';

    //         if ($currentDate->gt($today)) {
    //             $status = 'upcoming';
    //         } else if ($isHolidayDataFound) {
    //             $status = 'holiday';
    //             $isHoliday = true;
    //             $holidayName = $isHolidayDataFound->name;

    //             $totalHolidayCount += 1;
    //         } else if ($isAttendanceDataFound) {
    //             if ($isAttendanceDataFound->leave_type_id && $isAttendanceDataFound->is_half_day) {
    //                 $status = 'half_day';
    //                 $isLeave = true;
    //                 $leaveName = $isAttendanceDataFound->leave && $isAttendanceDataFound->leave->leaveType ? $isAttendanceDataFound->leave->leaveType->name : '';
    //                 $leaveReason = $isAttendanceDataFound->leave ? $isAttendanceDataFound->leave->reason : '';

    //                 $totalPresentDays += 0.5;
    //                 $totalHalfDays += 1;
    //             } else if ($isAttendanceDataFound->leave_type_id) {
    //                 $status = 'absent';
    //                 $isLeave = true;
    //                 $leaveName = $isAttendanceDataFound->leave && $isAttendanceDataFound->leave->leaveType ? $isAttendanceDataFound->leave->leaveType->name : '';
    //                 $leaveReason = $isAttendanceDataFound->leave ? $isAttendanceDataFound->leave->reason : '';
    //             } else {
    //                 $status = 'present';

    //                 $totalPresentDays += 1;
    //             }

    //             if ($status == 'half_day' || $status == 'present') {

    //                 // If attendance date is same as today date
    //                 // And user not clocked out
    //                 // Then we will calcualte the difference
    //                 if ($isAttendanceDataFound->clock_in_date_time && $isAttendanceDataFound->clock_in_date_time->format('Y-m-d') == $today->format('Y-m-d') && $isAttendanceDataFound->clock_out_date_time == null) {
    //                     $totalWorkDurationInMinutes = $currentDateTime->diffInMinutes($isAttendanceDataFound->clock_in_date_time);
    //                 } else if ($isAttendanceDataFound->clock_in_date_time != null && $isAttendanceDataFound->clock_out_date_time != null) {
    //                     // User have clock in and clock out time
    //                     $totalWorkDurationInMinutes = $isAttendanceDataFound->clock_out_date_time->diffInMinutes($isAttendanceDataFound->clock_in_date_time);
    //                 } else if ($user && $user->shift) {
    //                     $clockOutTimeDateFormatForAttendance = Carbon::createFromFormat('Y-m-d H:i:s', $isAttendanceDataFound->clock_in_date_time->format('Y-m-d') . '' . $user->shift->clock_out_time);

    //                     // If user assigned a shift
    //                     $totalWorkDurationInMinutes = $clockOutTimeDateFormatForAttendance->diffInMinutes($isAttendanceDataFound->clock_in_date_time);
    //                 } else {
    //                     // If shift not defined then take setting from company
    //                 }

    //                 if ($isAttendanceDataFound->is_late) {
    //                     $isLateClockedIn = CommonHrm::isLateClockedIn($isAttendanceDataFound->office_clock_in_time, $isAttendanceDataFound->clock_in_time);

    //                     if ($isLateClockedIn) {
    //                         $lateTime =  CommonHrm::getMinutesFromTimes($isAttendanceDataFound->office_clock_in_time, $isAttendanceDataFound->clock_in_time);
    //                     } else {
    //                         $lateTime = 0;
    //                     }
    //                 } else {
    //                     $lateTime = 0;
    //                 }
    //             }


    //             if ($isAttendanceDataFound->is_paid) {
    //                 $totalPaidLeave += $isAttendanceDataFound->is_half_day ? 0.5 : 1;

    //                 if ($isAttendanceDataFound->leave_type_id) {
    //                     $paidLeaveCount += $isAttendanceDataFound->is_half_day ? 0.5 : 1;
    //                 }
    //             } else {
    //                 $totalUnPaidLeave += $isAttendanceDataFound->is_half_day ? 0.5 : 1;
    //             }

    //             $totalWorkingDays += 1;
    //         } else {
    //             $status = 'not_marked';
    //             $totalWorkingDays += 1;
    //         }

    //         if ($status != 'upcoming') {
    //             $isUserLate = $isAttendanceDataFound && $isAttendanceDataFound->is_late ? $isAttendanceDataFound->is_late : 0;

    //             $attendanceData[] = [
    //                 'date' => $currentDate->format('Y-m-d'),
    //                 'status' => $status,
    //                 'is_holiday' => $isHoliday,
    //                 'holiday_name' => $holidayName,
    //                 'is_leave' => $isLeave,
    //                 'leave_name' => $leaveName,
    //                 'is_late'   => $isUserLate,
    //                 'late_time' => $lateTime,
    //                 'clock_in'  => $isAttendanceDataFound ? $isAttendanceDataFound->clock_in_date_time : '',
    //                 'clock_out'  => $isAttendanceDataFound ? $isAttendanceDataFound->clock_out_date_time : '',
    //                 'clock_in_ip'  => $isAttendanceDataFound ? $isAttendanceDataFound->clock_in_ip_address : '',
    //                 'clock_out_ip'  => $isAttendanceDataFound ? $isAttendanceDataFound->clock_out_ip_address : '',
    //                 'leave_reason'  => $leaveReason,
    //                 'worked_time'  => $totalWorkDurationInMinutes
    //             ];

    //             $totalLateDays += $isUserLate;
    //             $clockedInDuration += $totalWorkDurationInMinutes;
    //         }

    //         $totalDays += 1;

    //         $endDate->subDay();
    //     }

    //     return [
    //         'data'  => $attendanceData,
    //         'total_days'  => $totalDays,
    //         'working_days'  => $totalWorkingDays,
    //         'present_days'  => $totalPresentDays,
    //         'paid_leaves'  => $paidLeaveCount,
    //         'half_days'  => $totalHalfDays,
    //         'office_time'  => $officeHoursInMinutes,
    //         'total_office_time'  => $totalWorkingDays * $officeHoursInMinutes,
    //         'clock_in_duration'  => $clockedInDuration,
    //         'total_late_days'  => $totalLateDays,
    //         'total_paid_leaves' => $totalPaidLeave,
    //         'total_unpaid_leaves' => $totalUnPaidLeave,
    //         'holiday_count' => $totalHolidayCount,
    //     ];
    // }

    public static function getMonthYearAttendanceDetails($userId, $month, $year, $cut_off)
    {
        $company = company();
        $user = StaffMember::select('id', 'name', 'shift_id')->with(['shift'])->find($userId);

        $currentDateTime = Carbon::now($company->timezone);
        $today = Carbon::now($company->timezone)->startOfDay();

        // Determine cut-off range
        if ($cut_off === 'A') {
            $startDate = Carbon::createFromDate($year, $month, 1, $company->timezone)->startOfDay();
            $endDate = $startDate->copy()->addDays(14)->startOfDay(); // 1 to 15
        } elseif ($cut_off === 'B') {
            $startDate = Carbon::createFromDate($year, $month, 16, $company->timezone)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->startOfDay(); // 16 to 30/31
        } else {
            // Default: whole month
            $startDate = Carbon::createFromDate($year, $month, 1, $company->timezone)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->startOfDay();
        }

        $attendanceData = [];
        $lateTime = 0;
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
                        $totalWorkDurationInMinutes = $currentDateTime->diffInMinutes($isAttendanceDataFound->clock_in_date_time);
                    } else if ($isAttendanceDataFound->clock_in_date_time != null && $isAttendanceDataFound->clock_out_date_time != null) {
                        // User have clock in and clock out time
                        $totalWorkDurationInMinutes = $isAttendanceDataFound->clock_out_date_time->diffInMinutes($isAttendanceDataFound->clock_in_date_time);
                    } else if ($user && $user->shift) {
                        $clockOutTimeDateFormatForAttendance = Carbon::createFromFormat('Y-m-d H:i:s', $isAttendanceDataFound->clock_in_date_time->format('Y-m-d') . '' . $user->shift->clock_out_time);

                        // If user assigned a shift
                        $totalWorkDurationInMinutes = $clockOutTimeDateFormatForAttendance->diffInMinutes($isAttendanceDataFound->clock_in_date_time);
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
        ];
    }

    public static function attendanceDetailByMonth()
    {
        $request = request();

        $month = (int) $request->month;
        $year = (int) $request->year;
        $loggedUser = user();
        $company = company();

        if ($request->has('user_id') && $loggedUser->ability('admin', 'attendances_view')) {
            $userId = Common::getIdFromHash($request->user_id);
        } else {
            $userId = $loggedUser->id;
        }

        $resultData = self::getMonthYearAttendanceDetails($userId, $month, $year);

        return $resultData;
    }

    public static function applyVisibility($query, $joinTableName = 'users', $joinTableFieldName = 'user_id')
    {
        $user = user();

        // If user is not admin then
        // Users lists will be based on his visibility
        // don't show any user if deperatment, location or report_to is null assigned to user
        if ($user->role->name != 'admin') {
            if (in_array($user->visibility, ['department', 'location', 'manager']) && $joinTableName != 'users') {
                $query->join('users', 'users.id', '=', $joinTableName . '.' . $joinTableFieldName);
            }

            if ($user->visibility == 'department') {
                $query->where(function ($newQuery) use ($user, $query) {
                    $newQuery->where('users.department_id', $user->department_id)
                        ->whereNotNull('users.department_id');
                });
            } else if ($user->visibility == 'location') {
                $query->where(function ($newQuery) use ($user, $query) {
                    $newQuery->where('users.location_id', $user->location_id)
                        ->whereNotNull('users.location_id');
                });
            } else if ($user->visibility == 'manager') {
                $query->where(function ($newQuery) use ($user, $query) {
                    $newQuery->where('users.report_to', $user->id)
                        ->whereNotNull('users.report_to');
                });
            }
        }

        return $query;
    }

    public static function attendanceDetail($userId = null)
    {
        $request = request();
        $company = company();

        $month = (int) $request->month;
        $year = (int) $request->year;
        $loggedUser = user();

        $today = Carbon::now($company->timezone);
        $startDate = Carbon::createFromDate($year, $month, 1, $company->timezone)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth();

        $dateRanges = [];
        $columns = [];
        $attendanceData = [];

        $offset = $request->offset;
        $limit = $request->limit;
        $totalRecords = 1;

        $allCompanyUsers = StaffMember::with([
            'location:id,name',
            'designation:id,name'
        ])->select('users.id', 'users.name', 'users.profile_image', 'users.location_id', 'users.designation_id');

        if ($loggedUser->ability('admin', 'attendances_view')) {
            $allCompanyUsers = self::applyVisibility($allCompanyUsers);

            if ($request->has('user_id') || $userId != null) {
                $userId = $request->has('user_id') ? Common::getIdFromHash($request->user_id) : $userId;
                $allCompanyUsers = $allCompanyUsers->where('id', $userId);

                $totalRecords = 1;
            } else {
                $totalRecords = StaffMember::select('users.id');
                $totalRecords = self::applyVisibility($totalRecords)->count();
            }
        } else {
            $userId = $userId != null ? $userId : $loggedUser->id;
            $allCompanyUser = $allCompanyUsers->where('id', $userId);

            $totalRecords = 1;
        }

        $allCompanyUsers = $allCompanyUsers->orderBy('id', 'desc')->skip($offset)->take($limit)->get();

        $allAttendances = Attendance::with(['leave:id,leave_type_id,reason', 'leave.leaveType:id,name', 'holiday']);

        if ($userId != null) {
            $allAttendances = $allAttendances->where('attendances.user_id', $userId);
        }
        $allAttendances = $allAttendances->whereBetween('attendances.date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();
        $allHolidays = Holiday::whereBetween('holidays.date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();


        while ($startDate->lte($endDate)) {
            $dateRanges[] = $startDate->copy();
            $columns[] = $startDate->format('d');

            $startDate->addDay();
        }

        foreach ($allCompanyUsers as $allCompanyUser) {
            $userAttendanceData = [
                'name' => $allCompanyUser->name,
                'user' => $allCompanyUser,
            ];

            $totalWorkingDays = 0;
            $totalPresentDays = 0;
            foreach ($dateRanges as $dateRange) {
                $isAttendanceDataFound = $allAttendances->filter(function ($allAttendance) use ($dateRange, $allCompanyUser) {
                    return $allAttendance->date->format('Y-m-d') == $dateRange->format('Y-m-d') && $allCompanyUser->id == $allAttendance->user_id;
                })->first();
                $isHolidayDataFound = $allHolidays->filter(function ($allHoliday) use ($dateRange) {
                    return $allHoliday->date->format('Y-m-d') == $dateRange->format('Y-m-d');
                })->first();

                $isHoliday = false;
                $isLeave = false;
                $holidayName = '';
                $leaveName = '';
                $leaveReason = '';
                if ($dateRange->gt($today)) {
                    $status = 'upcoming';
                } else if ($isHolidayDataFound) {
                    $status = 'holiday';
                    $isHoliday = true;
                    $holidayName = $isHolidayDataFound->name;
                } else if ($isAttendanceDataFound) {

                    if ($isAttendanceDataFound->is_leave && $isAttendanceDataFound->is_half_day) {
                        $status = 'half_day';
                        $isLeave = true;
                        $leaveName = $isAttendanceDataFound->leave && $isAttendanceDataFound->leave->leaveType ? $isAttendanceDataFound->leave->leaveType->name : '';
                        $leaveReason = $isAttendanceDataFound->leave ? $isAttendanceDataFound->leave->reason : '';

                        $totalPresentDays += 0.5;
                    } else if ($isAttendanceDataFound->is_leave) {
                        $status = 'absent';
                        $isLeave = true;
                        $leaveName = $isAttendanceDataFound->leave && $isAttendanceDataFound->leave->leaveType ? $isAttendanceDataFound->leave->leaveType->name : '';
                        $leaveReason = $isAttendanceDataFound->leave ? $isAttendanceDataFound->leave->reason : '';
                    } else {
                        $status = 'present';

                        $totalPresentDays += 1;
                    }

                    $totalWorkingDays += 1;
                } else {
                    $status = 'absent';
                    $totalWorkingDays += 1;
                }
                $userAttendanceData[$dateRange->format('j')] = [
                    'status' => $status,
                    'is_holiday' => $isHoliday,
                    'holiday_name' => $holidayName,
                    'is_leave' => $isLeave,
                    'leave_name' => $leaveName,
                    'clock_in'  => $isAttendanceDataFound ? $isAttendanceDataFound->clock_in_date_time : '',
                    'clock_out'  => $isAttendanceDataFound ? $isAttendanceDataFound->clock_out_date_time : '',
                    'leave_reason'  => $leaveReason,
                ];
            }

            $userAttendanceData['working_days'] = $totalWorkingDays;
            $userAttendanceData['present_days'] = $totalPresentDays;
            $attendanceData[] = $userAttendanceData;
        }

        return [
            'columns' => $columns,
            'dateRange' => $dateRange,
            'data' => $attendanceData,
            'totalRecords' => $totalRecords
        ];
    }

    public static function markWeekend($markDayName, $startDate, $endDate, $ocassionName)
    {
        $periods = CarbonPeriod::create($startDate, $endDate);
        $dates = [];

        // Iterate over the period
        foreach ($periods as $period) {
            $date = $period->format('Y-m-d');
            $dayName = $period->format('l');
            $dates[] = $dayName;

            if (in_array($dayName, $markDayName)) {

                // Check if holiday exists
                $newHoliday = Holiday::whereDate('date', $date)->first();

                if (!$newHoliday) {
                    $newHoliday = new Holiday();
                }
                $newHoliday->date = $date;
                $newHoliday->name = $ocassionName;
                $newHoliday->year = $period->format('Y');
                $newHoliday->month = $period->format('m');
                $newHoliday->is_weekend = 1;
                $newHoliday->save();
            }
        }
    }

    public static function updateLeaveStatus($id)
    {
        $leave = Leave::find($id);

        $leaveDates = CarbonPeriod::create($leave->start_date, $leave->end_date);
        $data = [];
        foreach ($leaveDates as $leaveDate) {
            $isPaidLeaveOrNot = self::isPaidLeaveOrNot($leaveDate->format('Y-m-d'), $leave->user_id, $leave->leave_type_id);

            if ($isPaidLeaveOrNot['isHoliday'] == false) {
                $isPaid = $isPaidLeaveOrNot['isPaid'];

                $data[] = [
                    'date' => $leaveDate,
                    'totalLeaveTakenThisYear' => $isPaidLeaveOrNot['totalLeaveTakenThisYear'],
                    'paidLeaveTakenThisMonth' => $isPaidLeaveOrNot['paidLeaveTakenThisMonth'],
                    'total_leaves' => $isPaidLeaveOrNot['totalLeaves'],
                    'maxLeavePerMonth' => $isPaidLeaveOrNot['maxLeavePerMonth'],
                    'isPaid' => $isPaid,
                ];

                $attendance = new Attendance();
                $attendance->is_leave = 1;
                $attendance->date = $leaveDate->format('Y-m-d');
                $attendance->user_id = $leave->user_id;
                $attendance->leave_id = $leave->id;
                $attendance->leave_type_id = $leave->leave_type_id;
                $attendance->is_half_day = $leave->is_half_day;
                $attendance->is_paid = $isPaid;
                $attendance->status = $leave->is_half_day ? 'half_day' : 'on_leave';
                $attendance->reason = $leave->reason;
                $attendance->save();
            }
        }

        return $data;
    }

    public static function generateDatesFromToday($dayCount)
    {
        $dates = [];
        $today = Carbon::today();

        // Start from the oldest date and move towards today
        for ($i = $dayCount; $i >= 0; $i--) {
            $dates[] = $today->copy()->subDays($i)->toDateString();
        }

        return $dates;
    }


    public static function getScheduleOftaxAndBenifits($company_id){
        $data = Company::find($company_id);

        if ($data) {
            return [
                'tax_schedule' => $data->withholding_tax_processing,
                'benefits_schedule' => $data->gov_benifits,
            ];
        } else {
            throw new ApiException("Company not found");
        }
    }


    public static function getNightDifferentialMinutes($inDate, $outDate, $inTime, $outTime, $company_id)
    {
        $company = Company::find($company_id);

        if (!$company || !$company->night_diff_start_time || !$company->night_diff_end_time) {
            return 0;
        }

        // Compose full datetime for in/out
        $start = Carbon::parse($inDate . ' ' . $inTime);
        $end = Carbon::parse($outDate . ' ' . $outTime);

        // Night diff start/end (e.g. 22:00 to 06:00)
        $nightStart = $company->night_diff_start_time;
        $nightEnd = $company->night_diff_end_time;

        $totalNightMinutes = 0;
        $current = $start->copy();

        // Loop through each day covered by the attendance
        while ($current->lt($end)) {
            // Night diff period for this day
            $nightStartDateTime = Carbon::parse($current->format('Y-m-d') . ' ' . $nightStart);
            // If night diff ends next day
            if (strtotime($nightEnd) <= strtotime($nightStart)) {
            $nightEndDateTime = $nightStartDateTime->copy()->addDay()->setTimeFromTimeString($nightEnd);
            } else {
            $nightEndDateTime = Carbon::parse($current->format('Y-m-d') . ' ' . $nightEnd);
            }

            // Calculate overlap for this night diff period
            $periodStart = $current->greaterThan($nightStartDateTime) ? $current->copy() : $nightStartDateTime->copy();
            $periodEnd = $end->lessThan($nightEndDateTime) ? $end->copy() : $nightEndDateTime->copy();

            $minutes = $periodEnd->gt($periodStart) ? $periodEnd->diffInMinutes($periodStart) : 0;
            $totalNightMinutes += $minutes;

            // Move to next day
            $current = $nightEndDateTime;
        }

        $totalNightHours = abs($totalNightMinutes / 60);
        // \Log::info($inDate . " - " . $inTime ." <> " . $outDate . " - " . $outTime ." <=> " . $totalNightHours . " hours" );
        return $totalNightHours;
    }






    /**
     * Get the number of attendance hours by holiday type for a given attendance period.
     * Handles night shift crossing into a holiday.
     *
     * @param string $inDate  (Y-m-d)
     * @param string $outDate (Y-m-d)
     * @param string $inTime  (H:i:s)
     * @param string $outTime (H:i:s)
     * @return array
     */
    public static function getAttendanceHoursByHolidayType($inDate, $outDate, $inTime, $outTime)
    {
        $start = Carbon::parse($inDate . ' ' . $inTime);
        $end = Carbon::parse($outDate . ' ' . $outTime);

        // Collect all dates covered by the attendance
        $period = CarbonPeriod::create($start->copy()->startOfDay(), $end->copy()->startOfDay());
        
        $result = [
            'RH' => [],
            'SNW' => [],
            'SW' => [],
            'is_holiday' => false,
            'holiday_id' => null
        ];

        foreach ($period as $date) {
            $currentDate = $date->format('Y-m-d');

            // Determine segment start/end for this date
            $segmentStart = $currentDate == $start->format('Y-m-d') ? $start : $date->copy()->startOfDay();
            $segmentEnd = $currentDate == $end->format('Y-m-d') ? $end : $date->copy()->endOfDay();

            // Find holiday type for this date
            $holiday = Holiday::whereDate('date', $currentDate)->first();
            $type = $holiday ? ($holiday->holiday_type ?? 'Unknown') : null;

            // Calculate hours for this segment
            $hours = $segmentEnd->gt($segmentStart) ? $segmentEnd->diffInMinutes($segmentStart) / 60 : 0;

            // Only include if it's a holiday type we care about
            if (in_array($type, ['RH', 'SNW', 'SW'])) {
            $result[$type][] = [
                'date' => $currentDate,
                'hours' => abs($hours)
            ];
            $result['is_holiday'] = true;
            $result['holiday_id'] = $holiday->id;
            }
        }

        return $result;
    }


    public static function getOverideShift($user_id, $date)
    {

        $shift = OverideShift::where('user_id', $user_id)->where('date', $date)->get();
        $shift_main = StaffMember::with('shift')->find($user_id);
        // \Log::info($date . " <> " .count($shift));
        if (count($shift) > 0) {
            return [
                'shift' => $shift,
                'message' => 'Shift found for the user.',
                'bool' => true,
            ];
        }else{
            return [
                'shift' => [],
                'message' => 'Main Shift used as no override shift found.',
                'bool' => false,
            ];
        }
        // else {
        //     return [
        //         'shift' => $shift_main->shift ? $shift_main->shift->toArray() : null,
        //         'message' => 'Main Shift used as no override shift found.',
        //         'bool' => false,
        //     ];
        // }
    }

    // public static function reprocessAttendance($date_from, $date_to, $user_id = null, $status){
    //     $company = company();
    //     $user = StaffMember::find($user_id);

    //     // if (!$user) {
    //     //     throw new ApiException("User not found");
    //     // }

    //     $startDate = Carbon::createFromFormat('Y-m-d', $date_from, $company->timezone)->startOfDay();
    //     $endDate = Carbon::createFromFormat('Y-m-d', $date_to, $company->timezone)->endOfDay();

    //     // Fetch attendance records for the specified date range and user
    //     $attendances = Attendance::when($user_id, function ($query, $user_id) {
    //             return $query->where('user_id', $user_id);
    //         })
    //         ->whereBetween('date', [$startDate, $endDate])
    //         ->get();

            
    //     foreach ($attendances->toArray() as $attendance) {
    //         $attendance_id = Common::getIdFromHash($attendance['xid']);
    //         $user_id = Common::getIdFromHash($attendance['x_user_id']);
    //         $user_data = StaffMember::with('shift')->find($user_id);
    //         $attendanceRecord = Attendance::find($attendance_id);

    //         $time_in = Carbon::parse($attendanceRecord->clock_in_date_time)->setTimezone('Asia/Manila');
    //         $time_out = Carbon::parse($attendanceRecord->clock_out_date_time)->setTimezone('Asia/Manila');


    //         $main_shift_in = Carbon::parse($user_data->shift->clock_in_time)->setTimezone('Asia/Manila');
    //         $main_shift_out = Carbon::parse($user_data->shift->clock_out_time)->setTimezone('Asia/Manila');

    //         // $arrshift = explode(',', $user_data->x_shft_id_list);
    //             if($user_id == "12"){
    //                 $override_sift = self::getOverideShift($user_id, Carbon::parse($attendanceRecord->date)->format('Y-m-d'));
    //                 // \Log::info($override_sift);

    //                 if($override_sift['bool']){
    //                     if($override_sift['shift']['schedule_type'] == 'RVR'){
    //                         $shift = $override_sift['shift'];
    //                         $shiftClockInTime = $shift['time_in'];
    //                         $shiftClockOutTime = $shift['time_out'];

    //                         $shiftStart = new \DateTime(substr($time_in, 0, 10) . ' ' . $shiftClockInTime);
    //                         $shiftEnd = (new \DateTime(substr($time_in, 0, 10) . ' ' . $shiftClockOutTime))->modify('+1 day');

    //                         $actualIn = new \DateTime($time_in);
    //                         $actualOut = new \DateTime($time_out);

    //                         $totalWorkedMinutes = ($actualOut->getTimestamp() - $actualIn->getTimestamp()) / 60;

    //                         $lateMinutes = max(0, ($actualIn->getTimestamp() - $shiftStart->getTimestamp()) / 60);
    //                         $undertimeMinutes = max(0, ($shiftEnd->getTimestamp() - $actualOut->getTimestamp()) / 60);

    //                         // Base computation
    //                         $regularMinutes = min($totalWorkedMinutes, 480);  // 8 hours
    //                         $remainingMinutes = max(0, $totalWorkedMinutes - $regularMinutes);

    //                         $regularOTMinutes = min($remainingMinutes, 240);  // 4 hours regular OT
    //                         $remainingMinutes -= $regularOTMinutes;

    //                         // Add RVR shift as extra Regular OT
    //                         $rvrShiftMinutes = ($shiftEnd->getTimestamp() - $shiftStart->getTimestamp()) / 60;
    //                         $regularOTMinutes += $rvrShiftMinutes;   // Add RVR to regular OT

    //                         // Remaining time after RVR is other OT
    //                         $otherOTMinutes = max(0, $remainingMinutes);

    //                         // \Log::info("Regular OT (Including RVR Shift): " . round($regularOTMinutes / 60, 2) . " hours");
    //                         $regOTOthOT = round((($regularOTMinutes + $otherOTMinutes)/60),2);

    //                         \Log::info("-----------------------------------------------------------");
    //                         \Log::info("Date: " . $attendanceRecord->date);
    //                         \Log::info("Regular Hours: " . round($regularMinutes / 60, 2) . " hours");
    //                         \Log::info("Regular OT (Including RVR Shift): " . round($regularOTMinutes / 60, 2) . " hours");
    //                         \Log::info("Other OT: " . round($otherOTMinutes / 60, 2) . " hours");
    //                         \Log::info("Total OT: " . round($regOTOthOT / 60, 2) . " hours");
    //                         \Log::info("Late: " . round($lateMinutes) . " minutes");
    //                         \Log::info("Undertime: " . round($undertimeMinutes) . " minutes");
    //                         \Log::info("-----------------------------------------------------------");

    //                     }
                        
    //                 }else{
    //                     //Use main shift
    //                 }
    //             }
    //     }

    //     // print_r($attendances->toArray());
    //     exit;

    //     return [
    //         'message' => 'Attendance records updated successfully',
    //         'count' => count($attendances)
    //     ];
    // }

    public static function getWeekDay($date, $userRestDay = null)
{
    $dateparse = Carbon::parse($date);
    $dayOfWeek = $dateparse->dayOfWeek; // 0 (Sunday) to 6 (Saturday)

    // Map Carbon day index to your keys
    $dayKeyMap = [
        0 => 'SU',  // Sunday
        1 => 'M',   // Monday
        2 => 'T',   // Tuesday
        3 => 'W',   // Wednesday
        4 => 'TH',  // Thursday
        5 => 'F',   // Friday
        6 => 'SA',  // Saturday
    ];

    $dayKey = $dayKeyMap[$dayOfWeek];

    // Example: Define your rest days (could come from database)
    $restDays = [$userRestDay];

    $isRestDay = in_array($dayKey, $restDays);
    return $isRestDay;
}


private static function calculateOverlapMinutes($start1, $end1, $start2, $end2)
{
    $latestStart = max($start1->getTimestamp(), $start2->getTimestamp());
    $earliestEnd = min($end1->getTimestamp(), $end2->getTimestamp());

    $overlap = $earliestEnd - $latestStart;

    return max(0, $overlap / 60); // in minutes
}

private static function proccessND($user_id, $time_in, $time_out, $shift_id){

}

private static function proccessMainShift(){
    
}

private static function proccessRVR(){
    
}

private static function proccessOVR(){
    
}

private static function getHoliday($date){
    $holiday = Holiday::where('date', $date)->first();
    // $isHoliday = count($holiday) > 0 ? true : false;

    return [
        'data' => $holiday,
        // 'bool' => $isHoliday
    ];
}


//Original Code for reprocessing attendance
// public static function reprocessAttendance($date_from, $date_to, $user_id = null, $status)
// {
//     $company = company();
//     $user = StaffMember::find($user_id);

//     $startDate = Carbon::createFromFormat('Y-m-d', $date_from, $company->timezone)->startOfDay();
//     $endDate = Carbon::createFromFormat('Y-m-d', $date_to, $company->timezone)->endOfDay();

//     $attendances = Attendance::when($user_id, function ($query, $user_id) {
//             return $query->where('user_id', $user_id);
//         })
//         ->whereBetween('date', [$startDate, $endDate])
//         ->get();
    
        
 
//     $totalNDHrs = 0;
//     foreach ($attendances as $attendanceRecord) {
//         $attendance_id = Common::getIdFromHash($attendanceRecord->xid);
//         $user_data = StaffMember::with('shift')->find($user_id);
//         // \Log::info($user_data->shift->weekdays_day_off);
//         $time_in = Carbon::parse($attendanceRecord->clock_in_date_time)->setTimezone('Asia/Manila');
//         $time_out = Carbon::parse($attendanceRecord->clock_out_date_time)->setTimezone('Asia/Manila');
//         $isRestday = self::getWeekDay($attendanceRecord->date, $user_data->shift->weekdays_day_off);
//         // Prepare shift base date
//         $shiftDate = Carbon::parse($attendanceRecord->date)->format('Y-m-d');
        
//         // MAIN SHIFT TIMES (No timezone conversion)
//         $main_shift_in = Carbon::parse($shiftDate . ' ' . $user_data->shift->clock_in_time);
//         $main_shift_out = Carbon::parse($shiftDate . ' ' . $user_data->shift->clock_out_time);
//         if ($main_shift_out->lessThanOrEqualTo($main_shift_in)) {
//             $main_shift_out->addDay();
//         }
//         // Night Differential Start/End Times
//         $ndStartTime = $company->night_diff_start_time;
//         $ndEndTime = $company->night_diff_end_time;

//         // Actual worked times
//         $actualIn = new \DateTime($time_in);
//         $actualOut = new \DateTime($time_out);

//         // ND START/END TIMES (No timezone conversion)
//         $ndStart = Carbon::parse($shiftDate . ' ' . $ndStartTime);
//         $ndEnd = Carbon::parse($shiftDate . ' ' . $ndEndTime);

//         if ($ndEnd->lessThanOrEqualTo($ndStart)) {
//             $ndEnd->addDay();
//         }


//         $ndMinutesInMainShift = self::calculateOverlapMinutes($main_shift_in, $main_shift_out, $ndStart, $ndEnd);
//         // \Log::info(($ndMinutesInMainShift / 60) . " hours of Night Differential in Main Shift");

//         $totalWorkedMinutes = ($actualOut->getTimestamp() - $actualIn->getTimestamp()) / 60;

//         // Late and Undertime
//         $lateMinutes = max(0, ($actualIn->getTimestamp() - $main_shift_in->getTimestamp()) / 60);
//         $undertimeMinutes = max(0, ($main_shift_out->getTimestamp() - $actualOut->getTimestamp()) / 60);
//         $required_hrs_per_day = ($company->total_hrs_per_day * 60);
        
//         // 8 hours Regular Hours
//         $regularMinutes = min($totalWorkedMinutes, $required_hrs_per_day);

//         // Next 4 hours Regular OT
//         $remainingMinutes = max(0, $totalWorkedMinutes - $regularMinutes);
//         $regularOTMinutes = min($remainingMinutes, 240);
//         $remainingMinutes -= $regularOTMinutes;

//         // Check for RVR shift
//         $override_sift = self::getOverideShift($user_id, $shiftDate);
//         // \Log::info($shiftDate);
//         // \Log::info(count($override_sift['shift']));
        
//         $rvrShiftMinutes = 0;
//         $ndMinutesInRVRShift = 0;
//         // \Log::info($override_sift['bool'] . " <> " . $shiftDate);
//         foreach ($override_sift['shift'] as $key => $value) {
//             // \Log::info($override_sift['bool'] . " <> " . $shiftDate . " <> " . $value['schedule_type']);
//             if($override_sift['bool'] && $value['schedule_type'] == 'RVR'){
//                 //  \Log::info($value);
//                     // $shift = $overvalueride_sift['shift'];
//                     $shiftClockInTime = $value['time_in'];
//                     $shiftClockOutTime = $value['time_out'];

//                     $rvrShiftStart = Carbon::parse($shiftDate . ' ' . $shiftClockInTime);
//                     $rvrShiftEnd = Carbon::parse($shiftDate . ' ' . $shiftClockOutTime);
//                     if ($rvrShiftEnd->lessThanOrEqualTo($rvrShiftStart)) {
//                         $rvrShiftEnd->addDay();
//                     }
//                     $otherOTMinutes = max(0, $remainingMinutes);
//                     // ND Here
//                     $ndMinutesInRVRShift = self::calculateOverlapMinutes($rvrShiftStart, $rvrShiftEnd, $ndStart, $ndEnd);

//                     $RestOThrs = 0;
//                     $reg_hrs = round(($regularMinutes - ($lateMinutes + $undertimeMinutes)) / 60, 2);
//                     $regot_hrs = round($regularOTMinutes / 60, 2);
//                     $totalNDHrs = round(($ndMinutesInMainShift + $ndMinutesInRVRShift) / 60, 2);

//                     if ($isRestday) {
//                         // If it's a rest day, we don't count regular hours
//                         $RestOThrs = max(0, $reg_hrs + $regot_hrs);
//                         $reg_hrs = 0;
//                         $regot_hrs = 0;
//                         $undertimeMinutes = 0;
//                         $lateMinutes = 0;
//                         $totalNDHrs = 0; // No night differential on rest days
//                     }
//                         // $attendanceRecord->rest_day_ot = $RestOThrs;
//                         // $attendanceRecord->regular_hrs = max(0, $reg_hrs);
//                         // $attendanceRecord->regular_ot = max(0, $regot_hrs);
//                         // $attendanceRecord->no_of_hrs_undertime = round($undertimeMinutes / 60, 2);
//                         // $attendanceRecord->no_of_hrs_late = round($lateMinutes / 60, 2);
//                         // $attendanceRecord->night_differential = max(0, $totalNDHrs);
//                         // $attendanceRecord->save();


//                         // Logs
//                         \Log::info("-----------------------------------------------------------");
//                         \Log::info("Date: " . $attendanceRecord->date);
//                         \Log::info("Main Shift: " . $main_shift_in->format('H:i') . " - " . $main_shift_out->format('H:i'));
//                         if ($rvrShiftMinutes > 0) {
//                             \Log::info("RVR Shift: " . $shiftClockInTime . " - " . $shiftClockOutTime . " (" . round($rvrShiftMinutes / 60, 2) . " hrs added to Regular OT)");
//                         }
//                         \Log::info("Clock In: " . $time_in->format('Y-m-d H:i:s'));
//                         \Log::info("Clock Out: " . $time_out->format('Y-m-d H:i:s'));
//                         \Log::info("Total Worked Hours: " . round($totalWorkedMinutes / 60, 2) . " hours");
//                         \Log::info("Regular Hours: " . round($regularMinutes / 60, 2) . " hours");
//                         \Log::info("Regular OT (Including RVR Shift): " . round($regularOTMinutes / 60, 2) . " hours");
//                         \Log::info("Rest Day OT: " . $RestOThrs . " hours");
//                         \Log::info("Night Differential: " . $totalNDHrs . " hours");
//                         \Log::info("Other OT: " . round($otherOTMinutes / 60, 2) . " hours");
//                         \Log::info("Late: " . round($lateMinutes) . " minutes");
//                         \Log::info("Undertime: " . round($undertimeMinutes) . " minutes");
//                         \Log::info("-----------------------------------------------------------");
//                     }
//             }
//         // }
//         // if ($override_sift['bool'] && $override_sift['shift']['schedule_type'] == 'RVR') {
//         //     $shift = $override_sift['shift'];
//         //     $shiftClockInTime = $shift['time_in'];
//         //     $shiftClockOutTime = $shift['time_out'];

//         //     $rvrShiftStart = Carbon::parse($shiftDate . ' ' . $shiftClockInTime);
//         //     $rvrShiftEnd = Carbon::parse($shiftDate . ' ' . $shiftClockOutTime);
//         //     if ($rvrShiftEnd->lessThanOrEqualTo($rvrShiftStart)) {
//         //         $rvrShiftEnd->addDay();
//         //     }
//         //     // ND Here
//         //     $ndMinutesInRVRShift = self::calculateOverlapMinutes($rvrShiftStart, $rvrShiftEnd, $ndStart, $ndEnd);
//         //     // \Log::info( "Date : ". $shiftDate . " <> ". ($ndMinutesInRVRShift / 60) . " hours of Night Differential in RVR Shift");


//         //     $rvrShiftMinutes = ($rvrShiftEnd->getTimestamp() - $rvrShiftStart->getTimestamp()) / 60;

//         //     $regularOTMinutes += $rvrShiftMinutes; // Add RVR as Regular OT
//         // }else if ($override_sift['bool'] && $override_sift['shift']['schedule_type'] == 'OVD') {
//         //     $shift = $override_sift['shift'];
//         //     \Log::info($shift);
//         // }

//         // $otherOTMinutes = max(0, $remainingMinutes);

//         // $RestOThrs = 0;
//         // $reg_hrs = round(($regularMinutes - ($lateMinutes + $undertimeMinutes)) / 60, 2);
//         // $regot_hrs = round($regularOTMinutes / 60, 2);
//         // $totalNDHrs = round(($ndMinutesInMainShift + $ndMinutesInRVRShift) / 60, 2);
//         // if ($isRestday) {
//         //     // If it's a rest day, we don't count regular hours
            
//         //     $RestOThrs = max(0, $reg_hrs + $regot_hrs);
//         //     $reg_hrs = 0;
//         //     $regot_hrs = 0;
//         //     $undertimeMinutes = 0;
//         //     $lateMinutes = 0;
//         //     $totalNDHrs = 0; // No night differential on rest days
//         // }

//         // $attendanceRecord->rest_day_ot = $RestOThrs;
//         // $attendanceRecord->regular_hrs = max(0, $reg_hrs);
//         // $attendanceRecord->regular_ot = max(0, $regot_hrs);
//         // $attendanceRecord->no_of_hrs_undertime = round($undertimeMinutes / 60, 2);
//         // $attendanceRecord->no_of_hrs_late = round($lateMinutes / 60, 2);
//         // $attendanceRecord->night_differential = max(0, $totalNDHrs);
//         // $attendanceRecord->save();
//         // // Logs
//         // \Log::info("-----------------------------------------------------------");
//         // \Log::info("Date: " . $attendanceRecord->date);
//         // \Log::info("Main Shift: " . $main_shift_in->format('H:i') . " - " . $main_shift_out->format('H:i'));
//         // if ($rvrShiftMinutes > 0) {
//         //     \Log::info("RVR Shift: " . $shiftClockInTime . " - " . $shiftClockOutTime . " (" . round($rvrShiftMinutes / 60, 2) . " hrs added to Regular OT)");
//         // }
//         // \Log::info("Clock In: " . $time_in->format('Y-m-d H:i:s'));
//         // \Log::info("Clock Out: " . $time_out->format('Y-m-d H:i:s'));
//         // \Log::info("Total Worked Hours: " . round($totalWorkedMinutes / 60, 2) . " hours");
//         // \Log::info("Regular Hours: " . round($regularMinutes / 60, 2) . " hours");
//         // \Log::info("Regular OT (Including RVR Shift): " . round($regularOTMinutes / 60, 2) . " hours");
//         // \Log::info("Rest Day OT: " . $RestOThrs . " hours");
//         // \Log::info("Other OT: " . round($otherOTMinutes / 60, 2) . " hours");
//         // \Log::info("Late: " . round($lateMinutes) . " minutes");
//         // \Log::info("Undertime: " . round($undertimeMinutes) . " minutes");
//         // \Log::info("-----------------------------------------------------------");
//     }

//     exit;

//     return [
//         'message' => 'Attendance records updated successfully',
//         'count' => count($attendances)
//     ];
// }


    private static function logAttendanceComputation($attendance, $mainIn, $mainOut, $timeIn, $timeOut, $totalWorked, $regMin, $regOT, $restOT, $nd, $otherOT, $late, $under, $shifts)
    {
        \Log::info("-----------------------------------------------------------");
        \Log::info("Date: {$attendance->date}");
        \Log::info("Main Shift: {$mainIn->format('H:i')} - {$mainOut->format('H:i')}");

        foreach ($shifts as $shift) {
            if ($shift['schedule_type'] == 'RVR') {
                \Log::info("RVR Shift: {$shift['time_in']} - {$shift['time_out']}");
            }
        }

        \Log::info("Clock In: {$timeIn->format('Y-m-d H:i:s')}");
        \Log::info("Clock Out: {$timeOut->format('Y-m-d H:i:s')}");
        \Log::info("Total Worked Hours: " . round($totalWorked / 60, 2));
        \Log::info("Regular Hours: " . round($regMin / 60, 2));
        \Log::info("Regular OT: " . round($regOT, 2));
        \Log::info("Rest Day OT: " . round($restOT, 2));
        \Log::info("Night Differential: " . round($nd, 2));
        \Log::info("Other OT: " . round($otherOT / 60, 2));
        \Log::info("Late: " . round($late) . " minutes");
        \Log::info("Undertime: " . round($under) . " minutes");
        \Log::info("-----------------------------------------------------------");
    }

    private static function processSingleAttendance($attendance, $user, $company)
    {
        //atten date
        $shiftDate = Carbon::parse($attendance->date)->format('Y-m-d');
        //in and out from BMS
        $timeIn = Carbon::parse($attendance->clock_in_date_time)->setTimezone('Asia/Manila');
        $timeOut = Carbon::parse($attendance->clock_out_date_time)->setTimezone('Asia/Manila');
        //Fething for Override Schedule and RVR schedule
        $override = self::getOverideShift($user->id, $shiftDate);
        $holidaydata = self::getHoliday($shiftDate);
        //Main Shift Schedule
        $mainInTime = $user->shift->clock_in_time;
        $mainOutTime = $user->shift->clock_out_time;
        //Process of Override Schedule if have Main schedule will disregard and get the Override Schedule
        // $totalMinutesSched = 0;
        if ($override['bool']) {
            foreach ($override['shift'] as $ovr) {
                if ($ovr['schedule_type'] === 'OVD') {
                    $mainInTime = $ovr['time_in'];
                    $mainOutTime = $ovr['time_out'];
                    break;
                }
            }
        }
        $isRestDay = self::getWeekDay($attendance->date, $user->shift->weekdays_day_off);
        //Main Shift and Override Shift
        $mainIn = Carbon::parse($shiftDate . ' ' . $mainInTime);
        $mainOut = Carbon::parse($shiftDate . ' ' . $mainOutTime);

        if ($mainOut->lessThanOrEqualTo($mainIn)) {
            $mainOut->addDay();
        }

        $totalMinutesSched = ($mainOut->timestamp - $mainIn->timestamp) / 60;
        //Night Differential
        $ndStart = Carbon::parse($shiftDate . ' ' . $company->night_diff_start_time);
        $ndEnd = Carbon::parse($shiftDate . ' ' . $company->night_diff_end_time);
        if ($ndEnd->lessThanOrEqualTo($ndStart)) {
            $ndEnd->addDay();
        }
        //Actual IN and OUT
        $actualIn = new \DateTime($timeIn);
        $actualOut = new \DateTime($timeOut);

        //Total Worked Hours
        $totalWorked = ($actualOut->getTimestamp() - $actualIn->getTimestamp()) / 60;
        //Late
        $late = max(0, ($actualIn->getTimestamp() - $mainIn->getTimestamp()) / 60);
        //Undertime
        $undertime = max(0, ($mainOut->getTimestamp() - $actualOut->getTimestamp()) / 60);
        
        //required daily for regular hrs
        $requiredDailyMinutes = $company->total_hrs_per_day * 60;
        $shiftOTMain = abs(min(0, ($requiredDailyMinutes-$totalMinutesSched)));
        $regularMinutes = min($totalWorked, $requiredDailyMinutes);
        $remain_actual_min = max(0, $totalWorked - $regularMinutes);
        $regularOT = min($remain_actual_min, $shiftOTMain);
        $ndInMain = self::calculateOverlapMinutes($mainIn, $mainOut, $ndStart, $ndEnd);
        $remain_actual_for_rvr_min = max(0, ($remain_actual_min - $shiftOTMain));
        $ndInRvr = 0;
        $rvrShiftMinutes = 0;

        //rvr process
        foreach ($override['shift'] as $ovr) {
            if ($override['bool'] && $ovr['schedule_type'] === 'RVR') {
                $rvrStart = Carbon::parse($shiftDate . ' ' . $ovr['time_in']);
                $rvrEnd = Carbon::parse($shiftDate . ' ' . $ovr['time_out']);
                if ($rvrEnd->lessThanOrEqualTo($rvrStart)) {
                    $rvrEnd->addDay();
                }

                $ndInRvr = self::calculateOverlapMinutes($rvrStart, $rvrEnd, $ndStart, $ndEnd);
                $rvrShiftMinutes = ($rvrEnd->timestamp - $rvrStart->timestamp) / 60;
            }
        }

        $shifOTRvr = abs(min(0, ($rvrShiftMinutes-$remain_actual_for_rvr_min)));
        $regularMinutesRvr = min($remain_actual_for_rvr_min, $rvrShiftMinutes);
        $ndInRvr = $ndInRvr;

        $status = 'present';
        $regHrs = ($regularMinutes - ($late + $undertime)) / 60;
        $regOT = ($regularOT + $regularMinutesRvr) / 60;
        $nightDiff = ($ndInMain + $ndInRvr) / 60;
        $restOT = 0;
        if ($isRestDay) {
            $restOT = max(0, $regHrs + $regOT);
            $regHrs = $regOT = $undertime = $late = 0;
            $status = 'rest_day';
        }


        $attendance->rest_day_ot = $restOT;
        $attendance->regular_hrs = max(0, $regHrs);
        $attendance->regular_ot = max(0, $regOT);
        $attendance->no_of_hrs_undertime = round($undertime / 60, 2);
        $attendance->no_of_hrs_late = round($late / 60, 2);
        $attendance->night_differential = max(0, $nightDiff);
        $attendance->status = $status;
        $attendance->save();

        \Log::info($holidaydata['data']);
        
        // \Log::info("-------------------------MAIN and OVD----------------------------------");
        // \Log::info("Date: " . $shiftDate);
        // \Log::info("Required Regular Hrs Daily: " . ($requiredDailyMinutes / 60));
        // \Log::info("Required OT Hrs Daily: " . ($shiftOTMain / 60));
        // \Log::info("Required Shift Main and OVR Hrs Daily: " . ($totalMinutesSched / 60));
        // \Log::info("Regular Hrs: " . ($regularMinutes / 60));
        // \Log::info("Late: " . $late);
        // \Log::info("Undertime: " . $undertime);
        // \Log::info("Total worked hrs: " . ($totalWorked / 60));
        // \Log::info("Total remaining worked hrs: " . ($remain_actual_min / 60));
        // \Log::info("Total Regular OT hrs: " . ($regularOT / 60));
        // \Log::info("Total ND MAIN hrs: " . ($ndInMain / 60));
        // \Log::info("Total Rest OT hrs: " . $restOT);
        // \Log::info("Status: " . $status);
        // \Log::info("---------------------------RVR--------------------------------");
        // \Log::info("Remain from Compute in Main to RVR Regular Hrs Daily: " . ($remain_actual_for_rvr_min / 60));
        // \Log::info("Required RVR Regular Hrs Daily: " . ($rvrShiftMinutes / 60));
        // \Log::info("Required OT Hrs Daily: " . ($shifOTRvr / 60));
        // \Log::info("Total ND RVR hrs: " . ($ndInRvr / 60));
        // \Log::info("---------------------------END--------------------------------");

        // $status = 'present';
        // $regHrs = round(($regularMinutes - ($late + $undertime)) / 60, 2);
        // $regOT = round($regularOT / 60, 2);
        // $nightDiff = round(($ndInMain + $ndInRvr) / 60, 2);
        // $restOT = 0;

        // if ($isRestDay) {
        //     $restOT = max(0, $regHrs + $regOT);
        //     $regHrs = $regOT = $undertime = $late = 0;
        //     $status = 'rest_day';
        // }

        // // \Log::info("Processing attendance for user ID: {$user->id} on date: {$attendance->date} with regular hours: {$regHrs}");
        // if($regHrs < 0) {
        //     // \Log::info("here");
        //     $regHrs = 0;
        //     $regOT = 0;
        //     $undertime = 0;
        //     $late = 0;
        //     $nightDiff = 0;
        //     $status = 'absent';
        // }

        // // Optionally update attendance record in database
        // // $attendance->update([
        // //     'rest_day_ot' => $restOT,
        // //     'regular_hrs' => max(0, $regHrs),
        // //     'regular_ot' => max(0, $regOT),
        // //     'no_of_hrs_undertime' => round($undertime / 60, 2),
        // //     'no_of_hrs_late' => round($late / 60, 2),
        // //     'night_differential' => max(0, $nightDiff),
        // // ]);

        // $attendance->rest_day_ot = $restOT;
        // $attendance->regular_hrs = max(0, $regHrs);
        // $attendance->regular_ot = max(0, $regOT);
        // $attendance->no_of_hrs_undertime = round($undertime / 60, 2);
        // $attendance->no_of_hrs_late = round($late / 60, 2);
        // $attendance->night_differential = max(0, $nightDiff);
        // $attendance->status = $status;
        // $attendance->save();

        // self::logAttendanceComputation($attendance, $mainIn, $mainOut, $timeIn, $timeOut, $totalWorked, $regularMinutes, $regularOT, $restOT, $nightDiff, $remaining, $late, $undertime, $override['shift']);
    }


    public static function reprocessAttendance($date_from, $date_to, $user_id = null, $status)
    {
        $company = company();
        $user = StaffMember::with('shift')->find($user_id);
        $startDate = Carbon::createFromFormat('Y-m-d', $date_from, $company->timezone)->startOfDay();
        $endDate = Carbon::createFromFormat('Y-m-d', $date_to, $company->timezone)->endOfDay();

        $attendances = Attendance::when($user_id, fn($q) => $q->where('user_id', $user_id))
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        foreach ($attendances as $attendance) {
            self::processSingleAttendance($attendance, $user, $company);
        }

        return [
            'message' => 'Attendance records updated successfully',
            'count' => count($attendances)
        ];
    }

}
