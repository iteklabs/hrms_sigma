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
                        ->select('attendances.date', 'attendances.in_time', 'attendances.date_out', 'attendances.out_time', 'users.emp_no', 'users.name', 'attendances.id AS LogID')
                        ->where('attendances.isSentToHCS_in', false)
                        ->where('attendances.isSentToHCS_out', false)
                        ->orderByDesc('attendances.date')
                        ->get();

            $users = User::with(['shift'])->get()->keyBy('employee_number');
            foreach ($logs as $log) {
                $users_data = $users[$log->emp_no] ?? null;
                if($users_data){
                    if ($log->date && $log->date_out) {
                        // Both In date and Out date are present
                        // \Log::info('Both In date and Out date exist for ' . $log->emp_no);

                        $in_date = $log->date;
                        $in_time = $log->in_time;
                        $out_date = $log->date_out;
                        $out_time = $log->out_time;
                        $user_id = $users_data->id;


                        $timestamp_in = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $in_date . ' ' . $in_time, 'Asia/Manila')->setTimezone('UTC');
                        $timestamp_out = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $out_date . ' ' . $out_time, 'Asia/Manila')->setTimezone('UTC');

                        $totalMinutes = CommonHrm::getMinutesFromTimes($in_time, $out_time);
                        $clockoutDateTime = $timestamp_in->copy()->addMinutes($totalMinutes);
                        $data_in = $timestamp_in->copy()->format('Y-m-d H:i:s');
                        $data_out = $clockoutDateTime->format('Y-m-d H:i:s');
                    

                        $data = new Attendance();
                        $data->user_id = $user_id;
                        $data->company_id = $users_data->company_id;
                        $data->date = $in_date;
                        $data->clock_in_date_time = $data_in;
                        $data->total_duration = $totalMinutes;
                        $data->clock_out_date_time = $data_out;
                        $data->created_at = now();
                        $data->updated_at = now();
                        $data->is_paid = 1;
                        $data->office_clock_in_time = $users_data->shift?->clock_in_time;
                        $data->office_clock_out_time = $users_data->shift?->clock_out_time;
                        $data->is_holiday = 0;
                        $data->is_leave = 0;
                        $data->leave_type_id = null;
                        $data->status = 'present';
                        $data->is_half_day = 0;
                        $data->holiday_id = null;
                        $data->leave_id = null;
                        $data->clock_in_ip_address = 'BMS';
                        $data->clock_out_ip_address = 'BMS';
                        $data->save();

                       
                        $log_id = $log->LogID;

                        $log_updated = DB::connection('external_logs')
                        ->table('attendances')
                        ->where('id', $log_id)
                        ->update([
                            'isSentToHCS_in' => true,
                            'isSentToHCS_out' => true
                        ]);

                         \Log::info($log_updated . " <> " . $user_id);


                        // $logMessage = 'In date: ' . $in_date . ', In time: ' . $data_in . ', Out date: ' . $out_date . ', Out time: ' . $data_out . ', User ID: ' . $user_id . ', Total Minutes: ' . $totalMinutes;
                        // \Log::info($log_updated);
                        // \Log::info('My command ran at ' . $log->date . ' for employee ' . $log->emp_no . ' <> '. $log->name . ' with in time ' . $log->in_time . ' and out time '. $log->date_out  . " < > " . $log->out_time . ' and user id ' . $users_data?->name);
                    }
                }
            }

            $this->info("Synced " . $logs->count() . " attendance records.");
        //
    }
}
