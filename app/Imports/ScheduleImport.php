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
        foreach ($collection as $row) {
            $emp_no = $row['employee_number'] ?? null;
            $data_emp = StaffMember::select('employee_number', 'name')->where('employee_number', $emp_no)->first();
            \Log::info($row['scheduled_id'] ?? null);
            if(!empty($row['employee_number']) && $data_emp){
                
                $this->data[] = [
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
