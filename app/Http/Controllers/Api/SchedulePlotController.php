<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use Illuminate\Http\Request;
use App\Classes\Common;
use App\Models\OverideShift;

class SchedulePlotController extends ApiBaseController
{
    public function addOverrideShift(Request $request)
    {
        try {
             // Validate the request data
            $validatedData = $request->validate([
                'user_id' => 'required',
                'shift_id' => 'required',
                'date' => 'required',
                'time_in' => 'required',
                'time_out' => 'required',
                'schedule_type' => 'required|string|max:255',
                'schedule_location_id' => 'required',
            ]);

            $validatedData['user_id'] = Common::getIdFromHash($validatedData['user_id']);
            $validatedData['shift_id'] = Common::getIdFromHash($validatedData['shift_id']);
            $validatedData['schedule_location_id'] = Common::getIdFromHash($validatedData['schedule_location_id']);
            // echo "<pre>";
            // print_r($validatedData);
            // exit;
            // Create a new override shift
            $overrideShift = OverideShift::create($validatedData);

            return response()->json(['message' => 'Override shift added successfully', 'data' => $overrideShift], 201);
        } catch (\Throwable $th) {
            // Handle validation or other exceptions
            // The following line returns the error response and includes the exception line number.
            return response()->json([
                'error' => 'Failed to add override shift',
                'message' => $th->getMessage(),
                'line' => $th->getLine()
            ], 400);
            // Optionally, you can log the error
            //throw $th;
        }
    }


    public function editOverrideShift(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'xid' => 'required',
                'user_id' => 'required',
                'shift_id' => 'required',
                'date' => 'required',
                'time_in' => 'required',
                'time_out' => 'required',
                'schedule_type' => 'required|string|max:255',
                'schedule_location_id' => 'required',
            ]);
            $validatedData['xid'] = Common::getIdFromHash($validatedData['xid']);
            $validatedData['user_id'] = Common::getIdFromHash($validatedData['user_id']);
            $validatedData['shift_id'] = Common::getIdFromHash($validatedData['shift_id']);
            $validatedData['schedule_location_id'] = Common::getIdFromHash($validatedData['schedule_location_id']);
            $overrideShift = OverideShift::findOrFail($validatedData['xid']);
            $overrideShift->update($validatedData);
            return response()->json(['message' => 'Override shift updated successfully', 'data' => $overrideShift], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Failed to update override shift',
                'message' => $th->getMessage(),
                'line' => $th->getLine()
            ], 400);
        }
    }

    public function getOverideShiftByUser(Request $request)
    {
        try {
            $userId = Common::getIdFromHash($request->user_id);
            $overrides = OverideShift::with('scheduleLocation', 'shift')
                ->where('user_id', $userId)
                ->orderBy('id', 'desc')
                ->get();
            return response()->json(['data' => $overrides], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Failed to retrieve override shifts',
                'message' => $th->getMessage(),
                'line' => $th->getLine()
            ], 400);
        }
    }
}
