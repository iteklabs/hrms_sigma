<?php

namespace App\Http\Controllers\Api;

use App\Classes\CommonHrm;
use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\Holiday\IndexRequest;
use App\Http\Requests\Api\Holiday\StoreRequest;
use App\Http\Requests\Api\Holiday\UpdateRequest;
use App\Http\Requests\Api\Holiday\DeleteRequest;
use App\Http\Requests\Api\Holiday\MarkHolidayRequest;

use App\Models\Holiday;

use Carbon\Carbon;
use Examyou\RestAPI\ApiResponse;
use Examyou\RestAPI\Exceptions\ApiException;

class HolidayController extends ApiBaseController
{
    protected $model = Holiday::class;

    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;
    protected $markHolidayRequest = MarkHolidayRequest::class;


    protected function modifyIndex($query)
    {
        $request = request();

        // isWeekend Filters
        if ($request->has('holiday_type') && $request->holiday_type != "all") {
            $holidayType = $request->holiday_type == 'holiday' ? 0 : 1;
            $query = $query->where('holidays.is_weekend', $holidayType);
        };

        if ($request->has('month')) {
            $query = $query->whereMonth('date', $request->month);
        }

        if ($request->has('year')) {
            $query = $query->whereYear('date', $request->year);
        }

        return  $query;
    }

    public function storing($holiday)
    {
        $loggedUser = user();

        $holiday->created_by = $loggedUser->id;
        $holiday = $this->storingAndUpdating($holiday);

        return $holiday;
    }

    public function updating($holiday)
    {
        $holiday = $this->storingAndUpdating($holiday);

        return $holiday;
    }

    public function storingAndUpdating($holiday)
    {
        $request = request();

        $holidayFound = Holiday::whereDate('date', $request->date)->first();

        if ($holidayFound) {
            $holiday = $holidayFound;
        }

        $holiday->name = $request->name;
        $holiday->year = Carbon::createFromFormat('Y-m-d', $request->date)->year;
        $holiday->month = Carbon::createFromFormat('Y-m-d', $request->date)->month;
        $holiday->holiday_type = $request->holiday_type;

        return $holiday;
    }

    public function addQuickHoliday()
    {
        $request = request();
        if ($request->has('holidayData') &&  count($request->holidayData) > 0) {
            $holidays = $request->holidayData;
            if ($holidays) {
                foreach ($holidays as $holiday) {
                    $holidayFound = Holiday::whereDate('date', $holiday['date'])->first();
                    if (!$holidayFound) {
                        $oldHoliday = new Holiday;
                    } else {
                        $oldHoliday = $holidayFound;
                    }
                    $oldHoliday->name = $holiday['name'];
                    $oldHoliday->year = Carbon::createFromFormat('Y-m-d', $holiday['date'])->year;
                    $oldHoliday->month = Carbon::createFromFormat('Y-m-d', $holiday['date'])->month;
                    $oldHoliday->is_weekend = 0;
                    $oldHoliday->date = $holiday['date'];
                    $oldHoliday->save();
                }
            }
        };

        return ApiResponse::make('Success');
    }

    public function getHolidayDataByYear()
    {
        $request = request();
        if ($request->has('year')) {
            $yearHoliday = Holiday::where('year', $request->year)->where('is_weekend', 0)->get();
        }
        return ApiResponse::make('Success', ["data" => $yearHoliday]);
    }

    public function markHoliday(MarkHolidayRequest $markHolidayRequest)
    {
        $loggedUser = user();

        if (!$loggedUser->ability('admin', 'mark_weekend_holiday')) {
            throw new ApiException("Not have valid permission");
        }

        $request = request();

        $startDate = $request->date_from;
        $endDate = $request->date_to;
        $markDayName = $request->name;
        $ocassionName = $request->ocassion_name;

        CommonHrm::markWeekend($markDayName, $startDate, $endDate, $ocassionName);

        return ApiResponse::make('Holiday Successfully', []);
    }
}
