<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\Location;

class ScheduleTemplateExport implements FromArray, WithHeadings, WithEvents
{
    protected $locations;
    protected $scheduled_type;
    public function __construct()
    {
        $this->locations = Location::select('id', 'name')->get()->map(function ($location) {
            return [
                'id' => $location->id,
                'name' => $location->name,
            ];
        })->toArray();

        $this->scheduled_type = [
            ['val' => 'shift', 'name' => 'Main Shift'],
            ['val' => 'RVR', 'name' => 'Reliever'],
            ['val' => 'OVD', 'name' => 'OverRide Shift'],
        ];
    }

    public function array(): array
    {
        return [];
    }

    public function headings(): array
    {
        return [
            'employee_number',
            'name',
            'date',
            'date_to',
            'time_in',
            'time_out',
            'location_name', // G
            'location_id',   // H - auto-filled and protected
            'scheduled_type', //I
            'scheduled_id' // J
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $spreadsheet = $sheet->getParent();

                $sheet->getColumnDimension('A')->setWidth(20); // employee_number
                $sheet->getColumnDimension('B')->setWidth(25); // name
                $sheet->getColumnDimension('C')->setWidth(15); // date
                $sheet->getColumnDimension('D')->setWidth(15); // date_to
                $sheet->getColumnDimension('E')->setWidth(12); // time_in
                $sheet->getColumnDimension('F')->setWidth(12); // time_out
                $sheet->getColumnDimension('G')->setWidth(25); // location_name
                $sheet->getColumnDimension('H')->setWidth(15); // location_id
                $sheet->getColumnDimension('I')->setWidth(20); // scheduled_type
                $sheet->getColumnDimension('J')->setWidth(15); // scheduled_id

                // Create hidden sheet for dropdown values
                $locationSheet = $spreadsheet->createSheet();
                $locationSheet->setTitle('Locations');

                foreach ($this->locations as $index => $location) {
                    $row = $index + 1;
                    $locationSheet->setCellValue("A{$row}", $location['name']);
                    $locationSheet->setCellValue("B{$row}", $location['id']);
                }

                $scheduledSheet = $spreadsheet->createSheet();
                $scheduledSheet->setTitle('ScheduledTypes');
                foreach ($this->scheduled_type as $index => $type) {
                    $row = $index + 1;
                    $scheduledSheet->setCellValue("A{$row}", $type['name']);
                    $scheduledSheet->setCellValue("B{$row}", $type['val']);
                }

                // Hide the ScheduledTypes sheet
                $spreadsheet->getSheetByName('ScheduledTypes')->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

                // Hide the reference sheet
                $spreadsheet->setActiveSheetIndex(0);
                $spreadsheet->getSheetByName('Locations')->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

                // Loop through 2-100 rows for dropdown and formula
                for ($i = 2; $i <= 100; $i++) {
                    $dropdownCell = "G{$i}";
                    $formulaCell = "H{$i}";
                    $typeDropdownCell = "I{$i}";
                    $typeFormulaCell = "J{$i}";

                    // Set dropdown
                    $validation = $sheet->getCell($dropdownCell)->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(true);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setShowDropDown(true);
                    // $validation->setFormula1('=Locations!$A$1:$A$3');
                    $validation->setFormula1("=Locations!\$A\$1:\$A\$" . count($this->locations));


                    // Set VLOOKUP formula in H column
                    // $sheet->setCellValue($formulaCell, "=IFERROR(VLOOKUP(G{$i},Locations!A:B,2,FALSE), \"\")");
                    $sheet->setCellValue($formulaCell, "=IFERROR(VLOOKUP(G{$i},Locations!A:B,2,FALSE), \"\")");

                    $typeValidation = $sheet->getCell($typeDropdownCell)->getDataValidation();
                    $typeValidation->setType(DataValidation::TYPE_LIST);
                    $typeValidation->setErrorStyle(DataValidation::STYLE_STOP);
                    $typeValidation->setAllowBlank(true);
                    $typeValidation->setShowInputMessage(true);
                    $typeValidation->setShowErrorMessage(true);
                    $typeValidation->setShowDropDown(true);
                    $typeValidation->setFormula1("=ScheduledTypes!\$A\$1:\$A\$" . count($this->scheduled_type));

                    // VLOOKUP for Scheduled Type ID
                    $sheet->setCellValue($typeFormulaCell, "=IFERROR(VLOOKUP(I{$i},ScheduledTypes!A:B,2,FALSE), \"\")");

                    // Protect scheduled_id column
                    $sheet->getStyle($typeFormulaCell)->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
                }

                // Lock column H (location_id)
                for ($i = 2; $i <= 100; $i++) {
                    $sheet->getStyle("H{$i}")->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
                    $sheet->getStyle("J{$i}")->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
                }

                // Unlock all other columns (optional)
                foreach (range('A', 'G') as $col) {
                    for ($row = 2; $row <= 100; $row++) {
                        $sheet->getStyle("{$col}{$row}")->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);
                    }
                }

                foreach (range('A', 'I') as $col) { // Up to column I now
                    for ($row = 2; $row <= 100; $row++) {
                        $sheet->getStyle("{$col}{$row}")->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);
                    }
                }
                // Protect sheet
                $sheet->getProtection()->setPassword('secret'); // Optional
                $sheet->getProtection()->setSheet(true);


                
            }
        ];
    }
}
