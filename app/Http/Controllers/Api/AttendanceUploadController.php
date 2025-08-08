<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use Carbon\Carbon;
use Examyou\RestAPI\ApiResponse;
use App\Classes\CommonHrm;
use App\Models\OverideShift;
use App\Exports\ScheduleTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ScheduleImport;
use Illuminate\Http\Request;

class AttendanceUploadController extends ApiBaseController
{
    protected $model = OverideShift::class;

    protected function modifyIndex($query)
    {
        $loggedUser = user();
        $request = request();

        if ($loggedUser->ability('admin', 'attendance_upload_view')) {
            $query = $this->applyVisibility($query, 'overide_shifts');

            if ($request->has('user_id')) {
                $userId = $this->getIdFromHash($request->user_id);
                $query = $query->where('overide_shifts.user_id', $userId);
            }
        } else {
            $query = $query->where('overide_shifts.schedule_location_id', $loggedUser->location->id);
        }


        $query = $query->orderBy('overide_shifts.date', 'asc')
                ->orderBy('overide_shifts.time_in', 'desc');

        return  $query;
    }

    public function downloadScheduleTemplate()
    {
        return Excel::download(new ScheduleTemplateExport, 'schedule_template.xlsx');
    }


    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $import = new ScheduleImport();
        Excel::import($import, $request->file('file'));

        $data = $import->getData();

        if (empty($data)) {
            return response()->json([
                'message' => 'The uploaded file contains no data.',
            ], 422); // 422 = Unprocessable Entity
        }

        return response()->json([
            'data' => $data,
        ]);
    }


    public function saved(Request $request){
        try {
            //code...

            if(count($request->data) > 0){
                foreach ($request->data as $key => $value) {
                    $shift = OverideShift::updateOrCreate(
                        [
                            'user_id' => $value['employee_id'],
                            'date' => $value['date'],
                            'schedule_type' => $value['scheduled_id']
                        ],
                        [
                            'time_in' => $value['time_in'],
                            'time_out' => $value['time_out'],
                            'schedule_location_id' => $value['location_id'],
                            'user_id' => $value['employee_id'],
                            'shift_id' => 1,
                            'date' => $value['date'],
                            'date_to' => $value['date_to'],
                            'schedule_type' => $value['scheduled_id']
                        ]
                    );
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Override shift(s) saved successfully.',
                ], 200);

            }else{
                return response()->json([
                    'status' => 'error',
                    'message' => 'No data provided.',
                ], 400);
            }

            
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong while saving override shifts.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
