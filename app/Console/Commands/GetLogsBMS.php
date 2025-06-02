<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Attendance;
use App\Classes\CommonHrm;

use Illuminate\Console\Command;

class GetLogsBMS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-logs-b-m-s';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get logs from BMS and upload or insert locally';

    /**
     * Execute the console command.
     */
    public function handle()
    {
            $logs = DB::connection('external_logs')
                        ->table('attendances')  // first get query builder from the connection
                        ->join('users', 'attendances.worker_id', '=', 'users.id')  // now join is valid
                        ->select('attendances.date', 'attendances.in_time', 'attendances.date_out', 'attendances.out_time', 'users.emp_no', 'users.name') // example additional select
                        ->orderByDesc('attendances.date')
                        ->get();

            $users = User::with(['shift'])->get()->keyBy('employee_number');

            
            // $this->info('Running sync...');

            foreach ($logs as $log) {
                $users_data = $users[$log->emp_no] ?? null;
                if($users_data){
                    if ($log->date && $log->date_out && $log->emp_no == "90351567") {
                        // Both In date and Out date are present
                        // \Log::info('Both In date and Out date exist for ' . $log->emp_no);

                        $in_date = $log->date;
                        $in_time = $log->in_time;
                        $out_date = $log->date_out;
                        $out_time = $log->out_time;
                        $user_id = $users_data->id;
                        // $timestamp_in = \Carbon\Carbon::parse($in_date . ' ' . $in_time)->format('Y-m-d H:i:s');
                        // $timestamp_out = \Carbon\Carbon::parse($out_date . ' ' . $out_time)->format('Y-m-d H:i:s');
                        // $timestamp_in = \Carbon\Carbon::parse($in_date . ' ' . $in_time);
                        // $timestamp_out = \Carbon\Carbon::parse($out_date . ' ' . $out_time);
                        $timestamp_in = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $in_date . ' ' . $in_time, 'Asia/Manila')->setTimezone('UTC');
                        $timestamp_out = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $out_date . ' ' . $out_time, 'Asia/Manila')->setTimezone('UTC');
                        // $timestamp_in = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $in_date . ' ' . $in_time);
                        // $timestamp_out = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $out_date . ' ' . $out_time);

                        $totalMinutes = CommonHrm::getMinutesFromTimes($in_time, $out_time);
                        $clockoutDateTime = $timestamp_in->copy()->addMinutes($totalMinutes);
                        $data_in = $timestamp_in->copy()->format('Y-m-d H:i:s');
                        $data_out = $clockoutDateTime->format('Y-m-d H:i:s');
                        // $clockoutDateTime = $clockInDateTime->copy()->addMinutes($totalMinutes);
                        // $attendance->clock_in_date_time = $clockInDateTime->copy()->format('Y-m-d H:i:s');
                        // $attendance->clock_out_date_time = $clockoutDateTime->format('Y-m-d H:i:s');
                        // $attendance->total_duration = $totalMinutes;

                        // Insert or update the attendance record in the local database
                        // Attendance::create(
                        //     [
                        //         'user_id' => $user_id,
                        //         'company_id' => $users_data->company_id,
                        //         'date' => $in_date,
                        //         'clock_in_date_time' => $data_in,
                        //         'total_duration' => $totalMinutes,
                        //         'clock_out_date_time' => $data_out,
                        //         'created_at' => now(),
                        //         'updated_at' => now(),
                        //         'is_paid' => 1, // Assuming is_paid is always true for this example
                        //         'office_clock_in_time' => $users_data->shift?->clock_in_time,
                        //         'office_clock_out_time' => $users_data->shift?->clock_out_time,
                        //         'is_holiday' => 0, // Assuming is_holiday is always false for this example
                        //         'is_leave' => 0, // Assuming is_leave is always false for this example
                        //         'leave_type_id' => null, // Assuming no leave type for this example
                        //         'status' => 'present', // Assuming status is always present for this example
                        //         'is_half_day' => 0, // Assuming is_half_day is always false for this example
                        //         'holiday_id' => null, // Assuming no holiday for this example
                        //         'leave_id' => null, // Assuming no leave for this example
                                
                        //     ]
                        // );

                            $data = new Attendance();
                            $data->user_id = $user_id;
                            $data->company_id = $users_data->company_id;
                            $data->date = $in_date;
                            $data->clock_in_date_time = $data_in;
                            $data->total_duration = $totalMinutes;
                            $data->clock_out_date_time = $data_out;
                            $data->created_at = now();
                            $data->updated_at = now();
                            $data->is_paid = 1; // Assuming is_paid is always true for this example
                            $data->office_clock_in_time = $users_data->shift?->clock_in_time;
                            $data->office_clock_out_time = $users_data->shift?->clock_out_time;
                            $data->is_holiday = 0; // Assuming is_holiday is always false for this example
                            $data->is_leave = 0; // Assuming is_leave is always false for this example
                            $data->leave_type_id = null; // Assuming no leave type for this example
                            $data->status = 'present'; // Assuming status is always present for this example
                            $data->is_half_day = 0; // Assuming is_half_day is always false for this example
                            $data->holiday_id = null; // Assuming no holiday for this example
                            $data->leave_id = null; // Assuming no leave for this example.
                            $data->clock_in_ip_address = 'BMS';
                            $data->clock_out_ip_address = 'BMS';
                            $data->save();

                        $logMessage = 'In date: ' . $in_date . ', In time: ' . $data_in . ', Out date: ' . $out_date . ', Out time: ' . $data_out . ', User ID: ' . $user_id . ', Total Minutes: ' . $totalMinutes;
                        \Log::info($logMessage);
                        // \Log::info('My command ran at ' . $log->date . ' for employee ' . $log->emp_no . ' <> '. $log->name . ' with in time ' . $log->in_time . ' and out time '. $log->date_out  . " < > " . $log->out_time . ' and user id ' . $users_data?->name);
                    }
                    // } else {
                    //     \Log::warning('Missing In date or Out date for ' . $log->emp_no);
                    // }
                    // \Log::info('My command ran at ' . $log->date . ' for employee ' . $log->emp_no . ' <> '. $log->name . ' with in time ' . $log->in_time . ' and out time '. $log->date_out  . " < > " . $log->out_time . ' and user id ' . $users_data?->name);

                }
                // Upload or insert locally
                // Here we insert into a local "synced_attendances" table for example:
                // DB::table('synced_attendances')->updateOrInsert(
                //     ['date' => $log->date, 'in_time' => $log->in_time],
                //     [
                //         'date_out' => $log->date_out,
                //         'out_time' => $log->out_time,
                //         'created_at' => now(),
                //         'updated_at' => now(),
                //     ]
                // );
            }

            $this->info("Synced " . $logs->count() . " attendance records.");
        //
    }
}
