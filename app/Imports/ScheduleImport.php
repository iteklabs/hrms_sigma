<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Models\StaffMember;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;


class ScheduleImport implements ToCollection, WithCalculatedFormulas, WithHeadingRow
{
    protected $data = [];
    public function collection(Collection $collection)
    {
        Calculation::getInstance()->disableCalculationCache(); // optional: for accuracy
        // Filter out completely empty rows
        $filtered = $collection->filter(function ($row) {
            return $row->filter()->isNotEmpty(); // only include rows that have any data
        });
        foreach ($filtered as $row) {

            // if ($row->filter()->isEmpty()) {
            //     $row['employee_number'] = 'NA';
            //     continue;
            // }
            \Log::info($row->filter()->isEmpty());
            $emp_no = $row['employee_number'] ?? null;
            $data_emp = StaffMember::select('employee_number', 'name', 'id')->where('employee_number', $emp_no)->first();
            if(!empty($row['employee_number']) && $data_emp){
                $this->data[] = [
                    'employee_id' => $data_emp->id,
                    'employee_number' => $data_emp->employee_number,
                    'name' => $data_emp->name,
                    'date' => $this->convertDate($row['date']),
                    'date_to' => $this->convertDate($row['date_to']),
                    'time_in' => $this->convertExcelTime($row['time_in']),
                    'time_out' => $this->convertExcelTime($row['time_out']),
                    'location_name' => $row['location_name'],
                    'location_id' => $row['location_id'],
                    'scheduled_type' => $row['scheduled_type'],
                    'scheduled_id' => $row['scheduled_id'],
                    'rest_day' => $row['rest_day'] ?? null,
                    'rest_day_id' => $row['rest_day_id'] ?? null,
                    'status' => 'Found!',
                    'bool' => true
                ];
            }else if(!empty($row['employee_number']) && empty($data_emp)){
                $this->data[] = [
                    'employee_id' => null,
                    'employee_number' => $row['employee_number'] ?? null,
                    'name' => $row['name'] ?? null,
                    'date' => $row['date'] ?? null,
                    'date_to' => $row['date_to'] ?? null,
                    'time_in' => $row['time_in'] ?? null,
                    'time_out' => $row['time_out'] ?? null,
                    'location_name' => $row['location_name'] ?? null,
                    'location_id' => $row['location_id'] ?? null,
                    'scheduled_type' => $row['scheduled_type'] ?? null,
                    'scheduled_id' => $row['scheduled_id'] ?? null,
                    'rest_day' => $row['rest_day'] ?? null,
                    'rest_day_id' => $row['rest_day_id'] ?? null,
                    'status' => 'Not Found!',
                    'bool' => false
                ];
            }
            
        }
    }

    private function convertDate($excelDate)
    {
        if (is_numeric($excelDate)) {
            return Date::excelToDateTimeObject($excelDate)->format('Y-m-d');
        }
        return $excelDate;
    }

    private function convertExcelTime($value)
    {
        if (is_numeric($value)) {
            return Date::excelToDateTimeObject($value)->format('H:i');
        }
        return $value;
    }

    public function getData()
    {
        return $this->data;
    }
}
