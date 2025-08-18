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
            $EarnTax = $data->PayrollDetl->where('identity', 'earn')->where('isTaxable', true);
            $EarnNonTax = $data->PayrollDetl->where('identity', 'earn')->where('isTaxable', false);
            $DedcTax = $data->PayrollDetl->where('identity', 'dedc')->where('isTaxable', true);
            $DedcNonTax = $data->PayrollDetl->where('identity', 'dedc')->where('isTaxable', false);
            // echo "<pre>";
            // print_r($data->PayrollDetl->where('types', 'EARN')->where('isTaxable', true)->sum('amount'));
            // exit;
            $pdf = Pdf::loadView('payslip', [
                'data' => $data,
                'EarnTax' => $EarnTax,
                'EarnNonTax' => $EarnNonTax,
                'DedcTax' => $DedcTax,
                'DedcNonTax' => $DedcNonTax,
            ])->setPaper('a4', 'landscape');

            return $pdf->stream(); 
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
