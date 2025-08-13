<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Attendance;
use App\Classes\CommonHrm;
use \Carbon\Carbon;

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

                        // if($users_data->id == 12){

                        
                        $in_date = $log->date;
                        $in_time = $log->in_time;
                        $out_date = $log->date_out;
                        $out_time = $log->out_time;
                        $user_id = $users_data->id;
                        $get_nd = CommonHrm::getNightDifferentialMinutes($in_date, $out_date, $in_time, $out_time, $users_data->company_id);
                        $get_holiday = CommonHrm::getAttendanceHoursByHolidayType($in_date, $out_date, $in_time, $out_time);
                        // \Log::info($get_holiday);
                        $timestamp_in = Carbon::createFromFormat('Y-m-d H:i:s', $in_date . ' ' . $in_time, 'Asia/Manila')->setTimezone('UTC');
                        $timestamp_out = Carbon::createFromFormat('Y-m-d H:i:s', $out_date . ' ' . $out_time, 'Asia/Manila')->setTimezone('UTC');
                        
                        $special_working_h =0;
                        $special_non_working_h = 0;
                        $regular_working_h = 0;
                        $is_holiday = $get_holiday['is_holiday'];
                        $holiday_id = $get_holiday['holiday_id'];

                        $totalMinutes = CommonHrm::getMinutesFromTimes($in_time, $out_time, $in_date, $out_date);

                        
                        $clockoutDateTime = $timestamp_in->copy()->addMinutes($totalMinutes);
                        $data_in = $timestamp_in->copy()->format('Y-m-d H:i:s');
                        $data_out = $clockoutDateTime->format('Y-m-d H:i:s');


                        // if($users_data->id == 12 && $in_date == "2025-06-29"){
                        //     \Log::info($totalMinutes . " <> " . $in_date . " <> " . $out_date . " <> " . $data_out . ' <h> '. ($totalMinutes / 60));
                        //     exit;
                        // }

                        if($get_holiday['is_holiday']){
                            

                            foreach ($get_holiday as $index => $holiday_date) {
                                if ($index !== 'is_holiday' && !empty($get_holiday[$index])) {
                                    // \Log::info($get_holiday[$index][0]['hours']);
                                    switch ($index) {
                                        case 'SW':
                                            // Handle Special Working Holiday
                                                $special_working_h = $get_holiday[$index][0]['hours'];
                                            break;
                                        case 'SNW':
                                            // Handle Special Non-Working Holiday
                                                $special_non_working_h = $get_holiday[$index][0]['hours'];
                                            break;
                                        case 'RH':
                                            // Handle Regular Holiday
                                                $regular_working_h = $get_holiday[$index][0]['hours'];
                                            break;
                                    }
                                }
                            }
                        }
                        
                    

                        $data = new Attendance();
                        $data->user_id = $user_id;
                        $data->company_id = $users_data->company_id;
                        $data->date = $in_date;
                        $data->date_out = $out_date;
                        $data->clock_in_date_time = $data_in;
                        $data->total_duration = $totalMinutes;
                        $data->clock_out_date_time = $data_out;
                        $data->created_at = now();
                        $data->updated_at = now();
                        $data->is_paid = 1;
                        // $data->office_clock_in_time = $users_data->shift?->clock_in_time;
                        // $data->office_clock_out_time = $users_data->shift?->clock_out_time;
                        $data->is_holiday = $is_holiday;
                        $data->is_leave = 0;
                        $data->leave_type_id = null;
                        $data->status = 'present';
                        $data->is_half_day = 0;
                        $data->holiday_id = $holiday_id;
                        $data->leave_id = null;
                        $data->clock_in_ip_address = 'BMS';
                        $data->clock_out_ip_address = 'BMS';
                        // $data->night_differential = $get_nd;
                        // $data->legal_holiday = $regular_working_h;
                        // $data->special_holiday = $special_working_h;

                        // $data->save();
                        // \Log::info($data);
                        $log_id = $log->LogID;

                        $log_updated = DB::connection('external_logs')
                        ->table('attendances')
                        ->where('id', $log_id)
                        ->update([
                            'isSentToHCS_in' => true,
                            'isSentToHCS_out' => true
                        ]);

                        \Log::info($log_updated . " <> " . $user_id);
                        // }
                    }
                }
            }

            $this->info("Synced " . $logs->count() . " attendance records.");
        //
    }
}
