<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Payroll;
use App\Classes\Common;
class GeneratePDFPaySlip extends Controller
{
    public function payslip($id){
        try {
            $newID = Common::getIdFromHash($id);
            $data = Payroll::with(['user', 'PayrollDetl'])->find($newID);
            

            $pdf = Pdf::loadView('payslip', ['data' => $data]);

            return $pdf->stream(); 
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
