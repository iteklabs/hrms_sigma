<?php

namespace App\Classes;

use App\Models\BasicSalaryDetails;
use App\Models\Expense;
use App\Models\Payroll;
use App\Models\PayrollComponent;
use App\Models\PrePayment;
use App\Models\SalaryGroupComponent;
use App\Models\SalaryGroupUser;
use App\Models\StaffMember;
use Examyou\RestAPI\ApiResponse;
use Examyou\RestAPI\Exceptions\ApiException;
use Illuminate\Support\Facades\DB;
use App\Models\SSS;
use App\Models\Pagibig;
use App\Models\Philhealth;
use App\Models\TaxBIR;
use App\Classes\CommonHrm;
use App\Models\PayrollsDetl;
use App\Models\SalaryAdjustment;
use App\Models\LoanDeduction;


class Payrolls
{
    public static function updateUserSalary($userId, $annualCTC)
    {
        $user = StaffMember::find($userId);
        $monthlySalary = $user->basic_salary;
        $netSalary= $user->net_salary;
        $specialAllowance = 0;
        $annualAmount = $monthlySalary * 12;
        $annualCTC = $monthlySalary * 12;
        $user->update([
            'basic_salary' => $monthlySalary,
            'monthly_amount' => $netSalary,
            'annual_amount' => $annualAmount,
            'annual_ctc' => $annualCTC,
            'special_allowances' => $specialAllowance,
            'net_salary' => $netSalary
        ]);

        // echo "<pre>";
        // print_r($user->toArray());
        // echo "</pre>";
        // exit;
       
        // $calculationType = $user->calculation_type;
        // $ctcValue = (float)$user->ctc_value;
        // if ($ctcValue == 0 || $annualCTC == 0) {
        //     $monthlySalary = 0;
        // } else {
        //     if ($ctcValue == 0 || $ctcValue === null) {
        //         $monthlySalary = 0;
        //     } else {
        //         $monthlySalary = $calculationType === 'fixed'
        //             ? $ctcValue
        //             : ($annualCTC * $ctcValue) / 100 / 12;
        //     }
        // }

        // $annualAmount = $monthlySalary * 12;
        // $monthlyCtc = $annualCTC / 12;
        // $earnings = 0;
        // $deductions = 0;

        // $basicSalaryDetails = $user->basicSalaryDetails ? $user->basicSalaryDetails : [];
        // foreach ($basicSalaryDetails as $group) {
        //     $salaryComponents = $group->salaryComponent ? $group->salaryComponent : [];
        //     foreach ($salaryComponents as $component) {
        //         $amount = 0.0;

        //         switch ($component->value_type) {
        //             case 'fixed':
        //                 $amount = (float) $component->monthly;
        //                 break;
        //             case 'variable':
        //                 $amount = (float) $group['monthly'];
        //                 break;

        //             case 'basic_percent':
        //                 $amount = ($monthlySalary * (float) $component->monthly) / 100;
        //                 break;

        //             case 'ctc_percent':
        //                 if ($ctcValue != 0) {
        //                     $amount = ($monthlySalary * (float) $component->monthly) / $ctcValue;
        //                 } else {
        //                     $amount = 0;
        //                 }
        //                 break;

        //             default:
        //                 $amount = 0.0;
        //                 break;
        //         }

        //         if ($component->type === 'earnings') {
        //             $earnings += (float) $amount;
        //         } elseif ($component->type === 'deductions') {
        //             $deductions += (float) $amount;
        //         }
        //     }
        // }
        // $specialAllowance = number_format(($monthlyCtc - $monthlySalary - $earnings), 2, '.', '');
        // $netSalary = number_format(
        //     ($monthlySalary + $specialAllowance + $earnings - $deductions),
        //     2,
        //     '.',
        //     ''
        // );
        // $user->update([
        //     'basic_salary' => $monthlySalary,
        //     'monthly_amount' => $netSalary,
        //     'annual_amount' => $annualAmount,
        //     'annual_ctc' => $annualCTC,
        //     'special_allowances' => $specialAllowance,
        //     'net_salary'
        //     => $netSalary
        // ]);
    }

    // this is use for change payroll status generate to paid
    public static function updatePayrollStatus($accountReqId, $payrolls, $payrollStatus, $paymentDate)
    {
        $loggedUser = user();
        $accountId = Common::getIdFromHash($accountReqId);

        if (!$loggedUser->ability('admin', 'payrolls_edit')) {
            throw new ApiException("Not have valid permission");
        }
        if ($payrolls) {
            $paymentStatus = $payrollStatus;

            $payrollIds = [];

            foreach ($payrolls as $value) {
                $payrollIds[] = Common::getIdFromHash($value);
            }

            foreach ($payrollIds as $payrollId) {
                $payroll = Payroll::find($payrollId);
                if ($payroll) {
                    $payroll->status = $paymentStatus;

                    if ($paymentStatus == 'paid') {
                        $payroll->payment_date = $paymentDate;
                        $payroll->account_id = $accountId;
                    } else {
                        $payroll->payment_date = null;
                    }

                    $payroll->save();
                    if ($paymentStatus == 'paid') {
                        CommonHrm::insertAccountEntries($payroll->account_id, null, "payroll", $payroll->payment_date, $payroll->id, $payroll->net_salary);
                        CommonHrm::updateAccountAmount($payroll->account_id);
                    }
                }
            }
        }

        return ApiResponse::make('Status Updated Successfully', []);
    }


    // This function is use for update or create basic salary from basic salary and employee modules
    public static function updateEmployeeSalary($id, $basicSalary, $monthlyAmount, $annualAmount, $annualCtc, $ctcValue, $netSalary, $specialAllowances, $salaryComponents, $salaryGroupId, $divisor)
    {
        $user = StaffMember::find($id);
        
        // Update user basic salary details
        $user->basic_salary = $basicSalary;
        $user->monthly_amount = $monthlyAmount;
        $user->annual_amount = $annualAmount;
        $user->annual_ctc = $annualCtc;
        $user->ctc_value = $ctcValue;
        $user->net_salary = $netSalary;
        $user->special_allowances = $specialAllowances;
        $user->divisor = $divisor;
        $user->save();
        self::changeGroup($id, $user->annual_ctc, $salaryGroupId, $salaryComponents);
    }

    public static function changeGroup($userId, $annualCtc, $groupId, $salaryComponents)
    {
        $groupId = Common::getIdFromHash($groupId);

        $user = StaffMember::find($userId);
        $user->salary_group_id = $groupId;
        $user->save();

        SalaryGroupUser::updateOrCreate(
            ['user_id' => $userId],
            ['salary_group_id' => $groupId]
        );

        $salaryGroupComponents = SalaryGroupComponent::where('salary_group_id', $groupId)
            ->with('salaryGroup.salaryGroupComponents')
            ->get();

        BasicSalaryDetails::where('user_id', $userId)->delete();

        $salaryDetails = [];

        $convertedSalaryComponents = [];

        if (!empty($salaryComponents) && is_array($salaryComponents)) {
            $convertedSalaryComponents = array_map(function ($component) {
                return [
                    'id' => Common::getIdFromHash($component['id']),
                    'type' => $component['type'],
                    'value_type' => $component['value_type'],
                    'monthly_value' => $component['monthly_value'],
                ];
            }, $salaryComponents);
        }

        foreach ($salaryGroupComponents as $component) {

            $matchingComponent = collect($convertedSalaryComponents)->firstWhere('id', $component->salary_component_id);

            if ($matchingComponent) {
                $salaryDetails[] = [
                    'user_id' => $userId,
                    'salary_component_id' => $component->salary_component_id,
                    'type' => $matchingComponent['type'],
                    'value_type' => $matchingComponent['value_type'],
                    'monthly_value' => $matchingComponent['monthly_value']
                ];
            }
        }

        if (!empty($salaryDetails)) {
            foreach ($salaryDetails as $salaryDetail) {

                $basicSalaryDetails = new BasicSalaryDetails();
                $basicSalaryDetails->user_id = $salaryDetail['user_id'];
                $basicSalaryDetails->salary_component_id = $salaryDetail['salary_component_id'];
                $basicSalaryDetails->type = $salaryDetail['type'];
                $basicSalaryDetails->value_type = $salaryDetail['value_type'];
                $basicSalaryDetails->monthly =
                    $salaryDetail['monthly_value'];
                $basicSalaryDetails->save();
            }
        }

        self::updateUserSalary($userId, $annualCtc);
    }

    public static function get_prev_salary($id, $month, $year)
    {

        $payroll = Payroll::where('user_id', $id)
            ->where('month', $month)
            ->where('year', $year)
            ->where('cut_off', 'A')
            ->first();
        if ($payroll) {
            return $payroll;
        }

        return [
            'basic_salary' => 0,
            'sss_share_ee' => 0,
            'sss_mpf_ee' => 0,
            'sss_share_er' => 0,
            'sss_mpf_er' => 0,
            'sss_ec_er' => 0,
            'pagibig_share_ee' => 0,
            'pagibig_share_er' => 0,
            'philhealth_share_ee' => 0,
            'philhealth_share_er' => 0,
            'tax_withheld' => 0,
            'taxable_income' => 0,
            'night_differential_amount' => 0,
            'legal_holiday_ot_amount' => 0,
            'legal_holiday_amount' => 0,
            'rest_day_ot_amount' => 0,
            'rest_day_amount' => 0,
            'regular_ot_amount' => 0,
        ];

    }
    public static function get_sss_amount($id , $month, $year, $basisSalary, $calculationType, $cutOff){
        if(!isset($basisSalary) || $basisSalary <= 0){
            return [
                'employer_share' => 0,
                'employee_share' => 0,
                'mpf_yer' => 0,
                'ec_yer' => 0,
                'mpf_ee' => 0,
                'total_ee_share_mpf' => 0,
                'total_er_share_mpf' => 0
            ];
        }
        $sss = SSS::where('min_salary', '<=', $basisSalary)
            ->where('max_salary', '>=', $basisSalary)
            ->first();
        // \Log::info("SSS Details: " . $calculationType);
        // \Log::info("Employer Share: " . $sss->employer_share);
        // \Log::info("Employee Share: " . $sss->employee_share);
        // \Log::info("MPF YER: " . $sss->mpf_yer);
        // \Log::info("MPF EE: " . $sss->mpf_ee);
        // \Log::info("EC YER: " . $sss->ec_yer);

        switch ($calculationType) {
            case 'daily':
            case 's_monthly':
                if ($cutOff == 'A') {
                    $employer_share = $sss->employer_share / 2;
                    $employee_share = $sss->employee_share / 2;
                    $mpf_yer = $sss->mpf_yer / 2;
                    $mpf_ee = $sss->mpf_ee / 2;
                    $ec_yer = $sss->ec_yer / 2;

                    return [
                        'employer_share' => $employer_share,
                        'employee_share' => $employee_share,
                        'mpf_yer' => $mpf_yer,
                        'ec_yer' => $ec_yer,
                        'mpf_ee' => $mpf_ee,
                        'total_ee_share_mpf' => $mpf_ee + $employee_share,
                        'total_er_share_mpf' => $mpf_yer + $employer_share
                    ];

                    // return [
                    //     'employer_share' => 500,
                    //     'employee_share' => 500,
                    //     'mpf_yer' => 0,
                    //     'ec_yer' => 0,
                    //     'mpf_ee' => 0,
                    //     'total_ee_share_mpf' => 0,
                    //     'total_er_share_mpf' => 0
                    // ];
                }else if($cutOff == 'B'){

                    $get_prev_salary = self::get_prev_salary($id, $month, $year);
                    $prev_basic =  $get_prev_salary['basic_salary'] ?? 0;
                    $prev_sss_share_ee = $get_prev_salary['sss_share_ee'] ?? 0;
                    $prev_sss_mpf_ee = $get_prev_salary['sss_mpf_ee'] ?? 0;
                    $prev_sss_share_er = $get_prev_salary['sss_share_er'] ?? 0;
                    $prev_sss_mpf_er = $get_prev_salary['sss_mpf_er'] ?? 0;
                    $prev_sss_ec_er = $get_prev_salary['sss_ec_er'] ?? 0;
                    $prev_pagibig_share_ee = $get_prev_salary['pagibig_share_ee'] ?? 0;
                    $prev_pagibig_share_er = $get_prev_salary['pagibig_share_er'] ?? 0;
                    $prev_philhealth_share_ee = $get_prev_salary['philhealth_share_ee'] ?? 0;
                    $prev_philhealth_share_er = $get_prev_salary['philhealth_share_er'] ?? 0;
                    $prev_tax_withheld = $get_prev_salary['tax_withheld'] ?? 0;
                    // \Log::info("Previous Salary Details: ");
                    // \Log::info("Basic Salary: $prev_basic");
                    // \Log::info("SSS Employee Share: $prev_sss_share_ee");
                    // \Log::info("SSS MPF Employee Share: $prev_sss_mpf_ee");
                    // \Log::info("SSS Employer Share: $prev_sss_share_er");
                    // \Log::info("SSS MPF Employer Share: $prev_sss_mpf_er");
                    // \Log::info("SSS EC Employer Share: $prev_sss_ec_er");
                    // \Log::info("Pagibig Employee Share: $prev_pagibig_share_ee");
                    // \Log::info("Pagibig Employer Share: $prev_pagibig_share_er");
                    // \Log::info("PhilHealth Employee Share: $prev_philhealth_share_ee");
                    // \Log::info("PhilHealth Employer Share: $prev_philhealth_share_er");
                    // \Log::info("Tax Withheld: $prev_tax_withheld");

                    // Calculate the new SSS shares based on the previous salary
                    $employer_share = $sss->employer_share - $prev_sss_share_er;
                    $employee_share = $sss->employee_share - $prev_sss_share_ee;
                    $mpf_yer = $sss->mpf_yer - $prev_sss_mpf_er;
                    $mpf_ee = $sss->mpf_ee - $prev_sss_mpf_ee;
                    $ec_yer = $sss->ec_yer - $prev_sss_ec_er;
                    // \Log::info("SSS Details: " . $basisSalary);
                    return [
                        'employer_share' => $employer_share,
                        'employee_share' => $employee_share,
                        'mpf_yer' => $mpf_yer,
                        'ec_yer' => $ec_yer,
                        'mpf_ee' => $mpf_ee,
                        'total_ee_share_mpf' => $mpf_ee + $employee_share,
                        'total_er_share_mpf' => $mpf_yer + $employer_share
                    ];
                }
            break;

            case 'monthly':
                if ($cutOff == 'A') {
                    $employer_share = $sss->employer_share;
                    $employee_share = $sss->employee_share;
                    $mpf_yer = $sss->mpf_yer;
                    $mpf_ee = $sss->mpf_ee;
                    $ec_yer = $sss->ec_yer;

                    return [
                        'employer_share' => $employer_share,
                        'employee_share' => $employee_share,
                        'mpf_yer' => $mpf_yer,
                        'ec_yer' => $ec_yer,
                        'mpf_ee' => $mpf_ee,
                        'total_ee_share_mpf' => $mpf_ee + $employee_share,
                        'total_er_share_mpf' => $mpf_yer + $employer_share
                    ];
                }
            break;
            
            default:
            # code...
            break;
        }

        // return 0;
    }


    public static function get_pagibig_amount($id , $month, $year, $basisSalary, $calculationType, $cutOff){
        if(!isset($basisSalary) || $basisSalary <= 0){
            return [
                'employer_share' => 0,
                'employee_share' => 0
            ];
        }
        $pagibig = Pagibig::where('min_salary', '<=', $basisSalary)
            ->where('max_salary', '>=', $basisSalary)
            ->first();

        switch ($calculationType) {
            case 'daily':
            case 's_monthly':
                if ($cutOff == 'A') {
                    return [
                        'employer_share' => $pagibig->employer_share / 2,
                        'employee_share' => $pagibig->employee_share / 2
                    ];
                }else if($cutOff == 'B'){
                    $get_prev_salary = self::get_prev_salary($id, $month, $year);
                    $prev_pagibig_share_ee = $get_prev_salary['pagibig_share_ee'] ?? 0;
                    $prev_pagibig_share_er = $get_prev_salary['pagibig_share_er'] ?? 0;

                    return [
                        'employer_share' => $pagibig->employer_share - $prev_pagibig_share_er,
                        'employee_share' => $pagibig->employee_share - $prev_pagibig_share_ee
                    ];
                }
            break;

            case 'monthly':
                if ($cutOff == 'A') {
                    return [
                        'employer_share' => $pagibig->employer_share,
                        'employee_share' => $pagibig->employee_share
                    ];
                }
            break;
            
            default:
            # code...
            break;
        }

        // return 0;
    }

    public static function get_philhealth_amount($id , $month, $year, $basisSalary, $calculationType, $cutOff){
        // Placeholder for PhilHealth calculation logic
        // This function should return an array with 'employer_share' and 'employee_share'
        // similar to the SSS and Pagibig functions.
        if(!isset($basisSalary) || $basisSalary <= 0){
            return [
                'employer_share' => 0,
                'employee_share' => 0
            ];
        }
        $philhealth = Philhealth::where('min_salary', '<=', $basisSalary)
            ->where('max_salary', '>=', $basisSalary)
            ->first();

        if(!isset($philhealth) || !isset($basisSalary) || $basisSalary <= 0){
            return [
                'employer_share' => 0,
                'employee_share' => 0
            ];
        }


        switch ($calculationType) {
            case 'daily':
            case 's_monthly':
                if ($cutOff == 'A') {
                    $employee_share = ($basisSalary * $philhealth->EE_share_percentage ?? 0) / 2;
                    $employer_share = ($basisSalary * $philhealth->ER_share_percentage ?? 0) / 2;
                    // return [
                    //     'employer_share' => $employer_share,
                    //     'employee_share' => $employee_share
                    // ];

                    return [
                        'employer_share' => 250,
                        'employee_share' => 250
                    ];

                }else if($cutOff == 'B'){
                    $get_prev_salary = self::get_prev_salary($id, $month, $year);
                    
                    $prev_philhealth_share_ee = $get_prev_salary['philhealth_share_ee'] ?? 0;
                    $prev_philhealth_share_er = $get_prev_salary['philhealth_share_er'] ?? 0;


                    $employee_share = ($basisSalary * $philhealth->EE_share_percentage ?? 0) - $prev_philhealth_share_ee;
                    $employer_share = ($basisSalary * $philhealth->ER_share_percentage ?? 0) - $prev_philhealth_share_er;
                    
                    return [
                        'employer_share' => $employer_share,
                        'employee_share' => $employee_share
                    ];
                }
            break;


            case 'monthly':
                if ($cutOff == 'A') {
                    $employee_share = ($basisSalary * $philhealth->EE_share_percentage ?? 0);
                    $employer_share = ($basisSalary * $philhealth->ER_share_percentage ?? 0);
                    
                    return [
                        'employer_share' => $employer_share,
                        'employee_share' => $employee_share
                    ];
                }else if($cutOff == 'B'){
                    $employee_share = ($basisSalary * $philhealth->EE_share_percentage ?? 0);
                    $employer_share = ($basisSalary * $philhealth->ER_share_percentage ?? 0);
                    
                    return [
                        'employer_share' => $employer_share,
                        'employee_share' => $employee_share
                    ];
                }
            break;

            
            default:
            # code...
            break;
        }
    }

    public static function get_withheld_tax($id , $month, $year, $taxable, $calculationType, $cutOff, $type_tax)
    {
        if(!isset($taxable) || $taxable <= 0){
            return [
                'annual_tax' => 0,
                'monthly_tax' => 0,
                'per_cutoff_tax' => 0
            ];
        }

        switch ($type_tax) {
            case 'annualize':
                switch ($calculationType) {
                    case 's_monthly':
                        if($cutOff == 'A'){
                             $taxable = $taxable * 24;
                            $taxable_annual_gross = (float) $taxable ?? 0;
                            $taxable_table = TaxBir::where('min_salary', '<=', $taxable_annual_gross)->where('max_salary', '>=', $taxable_annual_gross)->first();
                            $annual_tax = ((($taxable_annual_gross - $taxable_table->min_salary) * $taxable_table->tax_percentage) + $taxable_table->fixed_amount);
                            $annual_tax = number_format($annual_tax, 2, '.', '');
                            $monthly_tax = $annual_tax / 12;
                            $per_cutoff_tax = $monthly_tax / 2;

                            return [
                                'annual_tax' => $annual_tax,
                                'monthly_tax' => number_format($monthly_tax, 2, '.', ''),
                                'per_cutoff_tax' => number_format($per_cutoff_tax, 2, '.', '')
                            ];

                        }else if($cutOff == 'B'){
                            $get_prev_salary = self::get_prev_salary($id, $month, $year);
                            $taxable = ($taxable + $get_prev_salary["taxable_income"]) * 12;
                            $taxable_annual_gross = (float) $taxable ?? 0;
                            $taxable_table = TaxBir::where('min_salary', '<=', $taxable_annual_gross)->where('max_salary', '>=', $taxable_annual_gross)->first();
                            $annual_tax = ((($taxable_annual_gross - $taxable_table->min_salary) * $taxable_table->tax_percentage) + $taxable_table->fixed_amount);
                            $prev_tax_withheld = $get_prev_salary['tax_withheld'] ?? 0;
                            $annual_tax = number_format($annual_tax, 2, '.', '');
                            $monthly_tax = $annual_tax / 12;
                            $per_cutoff_tax = $monthly_tax - $prev_tax_withheld;

                            return [
                                'annual_tax' => $annual_tax,
                                'monthly_tax' => number_format($monthly_tax, 2, '.', ''),
                                'per_cutoff_tax' => number_format($per_cutoff_tax, 2, '.', '')
                            ];
                        }
                        

                        

                    break;

                    case 'monthly':
                        
                    break;
                    
                }
            break;
            
            default:
            # code...
            break;
        }
    }

    public static function getSalaryAdjustment($user_id, $month, $year, $cutoff){
        // $data = SalaryAdjustment::where('user_id', $user_id)
        // ->where('start_month_specific', $month)
        // ->where('start_year_specific', $year)
        // ->where('start_cut_off_specific', $cutoff)
        // ->get();

        $targetKey = $year . str_pad($month, 2, '0', STR_PAD_LEFT) . $cutoff;
        $data = SalaryAdjustment::where('user_id', $user_id)
        ->whereRaw("CONCAT(start_year_specific, LPAD(start_month_specific, 2, '0'), start_cut_off_specific) <= ?", [$targetKey])
        ->whereRaw("CONCAT(end_year_specific, LPAD(end_month_specific, 2, '0'), end_cut_off_specific) >= ?", [$targetKey])
        ->get();
        // \Log::info($data->toArray());
        // exit;
        $dataArray = array();
        if($data){
            $dataArray['earn'] = [];
            $dataArray['dedc'] = [];
            foreach ($data as $key => $value) {
                $dataArray['earn'][] = $value->toArray();
                // switch ($value->adjustment_type) {
                //     case 'EARN':
                //         $dataArray['earn'][] = $value->toArray();
                        
                //     break;
                    
                //     case 'DEDC':
                //         $dataArray['dedc'][] = $value->toArray();
                //     break;
                // }
            }
        }
        return $dataArray;
    }

    public static function getLoanDeduction($user_id, $month, $year, $cutoff){
        // $data = SalaryAdjustment::where('user_id', $user_id)
        // ->where('start_month_specific', $month)
        // ->where('start_year_specific', $year)
        // ->where('start_cut_off_specific', $cutoff)
        // ->get();

        $targetKey = $year . str_pad($month, 2, '0', STR_PAD_LEFT) . $cutoff;
        $data = LoanDeduction::where('user_id', $user_id)
        ->whereRaw("CONCAT(start_year_specific, LPAD(start_month_specific, 2, '0'), start_batch_specific) <= ?", [$targetKey])
        ->whereRaw("CONCAT(end_year_specific, LPAD(end_month_specific, 2, '0'), end_batch_specific) >= ?", [$targetKey])
        ->get();
        // \Log::info($data->toArray());
        // exit;
        $dataArray = array();
        if($data){
            // $dataArray['earn'] = [];
            $dataArray['dedc'] = [];
            foreach ($data as $key => $value) {
                $dataArray['dedc'][] = $value->toArray();
                // switch ($value->adjustment_type) {
                //     case 'EARN':
                //         $dataArray['earn'][] = $value->toArray();
                        
                //     break;
                    
                //     case 'DEDC':
                //         $dataArray['dedc'][] = $value->toArray();
                //     break;
                // }
            }
        }
        return $dataArray;
    }


    public static function payrollGenerateRegenerate($payrollGenerateRequest, $user, $company)
    {
        try {
            if($payrollGenerateRequest->has('year') && $payrollGenerateRequest->has('month') && $payrollGenerateRequest->month > 0 ){

            $year = (int) $payrollGenerateRequest->year;
            $month = (int) $payrollGenerateRequest->month;
            $cut_off = $payrollGenerateRequest->cut_off;

            $allUsers = StaffMember::select('id', 'basic_salary', 'semi_monthly_rate', 'hourly_rate', 'daily_rate', 'salary_group_id', 'ctc_value', 'annual_ctc', 'calculation_type', 'name', 'company_id')
                ->where('status', 'active')
                ->whereNotNull('ctc_value')
                ->with('salaryGroup.salaryGroupComponents.salaryComponent')->get();
            // \Log::info($allUsers);

            $userIds = [];

            if ($payrollGenerateRequest->has('users')) {
                $userIds = Common::getIdArrayFromHash($payrollGenerateRequest->users);

                $allUsers = $allUsers->whereIn('id', $userIds);
            }
            foreach ($allUsers as $allUser) {

                $sched = CommonHrm::getScheduleOftaxAndBenifits($allUser->company_id);
                
                $payroll = Payroll::where('month', $month)
                    ->where('year', $year)
                    ->where('user_id', $allUser->id)
                    ->where('cut_off', $cut_off)
                    ->first();

                if (!$payroll) {
                    $payroll = new Payroll();
                    $payroll->created_by = $user->id;
                    $payroll->user_id = $allUser->id;
                    $payroll->year = $year;
                    $payroll->month = $month;
                    $payroll->cut_off = $cut_off;
                    $payroll->total_days = 0;
                    $payroll->basic_salary = 0;
                    $payroll->working_days = 0;

                    $payroll->total_office_time = 0;
                    $payroll->total_worked_time = 0;
                    $payroll->half_days = 0;
                    $payroll->late_days = 0;
                    $payroll->paid_leaves = 0;
                    $payroll->unpaid_leaves = 0;
                    $payroll->holiday_count = 0;

                    $payroll->present_days = 0;
                    $payroll->salary_amount = 0;
                    $payroll->net_salary = 0;
                    $payroll->status = "draft";
                    $payroll->updated_by = $user->id;
                    $payroll->payment_date = null;
                    $payroll->company_id = $company->id;

                    $payroll->sss_share_er = 0;
                    $payroll->sss_share_ee = 0;
                    $payroll->sss_mpf_er = 0;
                    $payroll->sss_ec_er = 0;
                    $payroll->sss_mpf_ee = 0;
                    $payroll->pagibig_share_er = 0;
                    $payroll->pagibig_share_ee = 0;
                    $payroll->philhealth_share_er = 0;
                    $payroll->philhealth_share_ee = 0;
                    $payroll->taxable_income = 0;
                    $payroll->tax_withheld = 0;

                    $payroll->night_differential_amount = 0;
                    $payroll->legal_holiday_ot_amount  = 0;
                    $payroll->legal_holiday_amount     = 0;
                    $payroll->rest_day_ot_amount       = 0;
                    $payroll->rest_day_amount          = 0;
                    $payroll->regular_ot_amount        = 0;
                    $payroll->save();

                }

                    // $basicSalary = $allUser->basic_salary / 2 ?? 0;
                    $resultData = CommonHrm::getMonthYearAttendanceDetails($allUser->id, $month, $year, $cut_off);
                // \Log::alert($resultData);
                    $regular_ot_percentage = ($company->regular_ot_percentage / 100);
                    $legal_holiday_percentage = ($company->legal_holiday_percentage / 100);
                    $legal_holiday_ot_percentage = ($company->legal_holiday_ot_percentage / 100);
                    $rest_day_percentage = ($company->rest_day_percentage / 100);
                    $rest_day_ot_percentage = ($company->rest_day_ot_percentage / 100);
                    $night_diff_percentage = ($company->night_diff_percentage / 100);
                    
                    $total_days = $resultData['total_days'];
                    $working_days = $resultData['working_days'];
                    $regular_hrs = $resultData['regular_hrs'];
                    $regular_ot = $resultData['regular_ot'];
                    $rest_day = $resultData['rest_day'];
                    $rest_day_ot = $resultData['rest_day_ot'];
                    $legal_holiday = $resultData['legal_holiday'];
                    $legal_holiday_ot = $resultData['legal_holiday_ot'];
                    $night_differential = $resultData['night_differential'];

                    $SalarAdjustment = self::getSalaryAdjustment($allUser->id, $month, $year, $cut_off);
                    $EarnTax = 0;
                    $EarnNonTax = 0;
                    $DeaductTax = 0;
                    $DeaductNonTax = 0;
                    \Log::info($payroll);
                    if(count($SalarAdjustment) > 0){
                        if(count($SalarAdjustment['earn']) > 0){
                            foreach ($SalarAdjustment['earn'] as $key => $value) {
                                switch ($value['type']) {
                                    case 'NT':
                                        $EarnNonTax += $value['amount'];
                                        PayrollsDetl::updateOrCreate(
                                            [
                                                'salary_adjustment_id' => Common::getIdFromHash($value['xid']),
                                                'payroll_id' => Common::getIdFromHash($payroll->xid) ,
                                            ],
                                            [
                                                'salary_adjustment_id' => Common::getIdFromHash($value['xid']),
                                                'payroll_id' => Common::getIdFromHash($payroll->xid),
                                                'amount' => $value['amount'],
                                                'title' => $value['name'],
                                                'types' => $value['adjustment_type'],
                                                'isTaxable' => false
                                            ]
                                        );
                                    break;

                                    case 'T':
                                        $EarnTax += $value['amount'];
                                        PayrollsDetl::updateOrCreate(
                                            [
                                                'salary_adjustment_id' => Common::getIdFromHash($value['xid']),
                                                'payroll_id' => Common::getIdFromHash($payroll->xid) ,
                                            ],
                                            [
                                                'salary_adjustment_id' => Common::getIdFromHash($value['xid']),
                                                'payroll_id' => Common::getIdFromHash($payroll->xid),
                                                'amount' => $value['amount'],
                                                'title' => $value['name'],
                                                'identity' => 'earn',
                                                'types' => $value['adjustment_type'],
                                                'isTaxable' => true
                                            ]
                                        );
                                    break;
                                }
                                
                            }
                        }

                        // if(count($SalarAdjustment['dedc']) > 0){
                        //     foreach ($SalarAdjustment['dedc'] as $key => $value) {
                        //         switch ($value['type']) {
                        //             case 'NT':
                        //                 $DeaductNonTax += $value['amount'];
                        //                 PayrollsDetl::updateOrCreate(
                        //                     [
                        //                         'salary_adjustment_id' => Common::getIdFromHash($value['xid']),
                        //                         'payroll_id' => Common::getIdFromHash($payroll->xid) ,
                        //                     ],
                        //                     [
                        //                         'salary_adjustment_id' => Common::getIdFromHash($value['xid']),
                        //                         'payroll_id' => Common::getIdFromHash($payroll->xid),
                        //                         'amount' => $value['amount'],
                        //                         'title' => $value['name'],
                        //                         'types' => $value['adjustment_type'],
                        //                         'isTaxable' => false
                        //                     ]
                        //                 );
                        //             break;

                        //             case 'T':
                        //                 $DeaductTax += $value['amount'];
                        //                 PayrollsDetl::updateOrCreate(
                        //                     [
                        //                         'salary_adjustment_id' => Common::getIdFromHash($value['xid']),
                        //                         'payroll_id' => Common::getIdFromHash($payroll->xid) ,
                        //                     ],
                        //                     [
                        //                         'salary_adjustment_id' => Common::getIdFromHash($value['xid']),
                        //                         'payroll_id' => Common::getIdFromHash($payroll->xid),
                        //                         'amount' => $value['amount'],
                        //                         'title' => $value['name'],
                        //                         'types' => $value['adjustment_type'],
                        //                         'isTaxable' => true
                        //                     ]
                        //                 );
                        //             break;
                        //         }
                        //     }
                        // }


                        
                        
                    }

                    

                    $SalarDeductionLoan = self::getLoanDeduction($allUser->id, $month, $year, $cut_off);
                    // \Log::info($SalarDeductionLoan);
                    if(count($SalarDeductionLoan['dedc']) > 0){
                        foreach ($SalarDeductionLoan['dedc'] as $key => $value) {
                            $DeaductNonTax += $value['payroll_deduction'];
                            // \Log::alert($value);
                            PayrollsDetl::updateOrCreate(
                                [
                                    'salary_adjustment_id' => Common::getIdFromHash($value['xid']),
                                    'payroll_id' => Common::getIdFromHash($payroll->xid) ,
                                ],
                                [
                                    'salary_adjustment_id' => Common::getIdFromHash($value['xid']),
                                    'payroll_id' => Common::getIdFromHash($payroll->xid),
                                    'amount' => $value['payroll_deduction'],
                                    'title' => $value['type_of_loan'],
                                    'types' => $value['loan_name'],
                                    'identity' => 'dedc',
                                    'isTaxable' => false
                                ]
                            );
                        }
                    }
                    
                    \Log::info($DeaductNonTax . " DeaductNonTax" . $EarnNonTax . " EarnNonTax" . $EarnTax . " EarnTax" . $DeaductTax . " DeaductTax");
                    


                    $basicSalary = ($working_days * $allUser->daily_rate);
                    $regular_ot_amount = (($allUser->hourly_rate * $regular_ot_percentage) * $regular_ot);
                    $rest_day_amount = (($allUser->hourly_rate * $rest_day_percentage) * $rest_day);
                    $rest_day_ot_amount = (($allUser->hourly_rate * $rest_day_ot_percentage) * $rest_day_ot);
                    $legal_holiday_amount = (($allUser->hourly_rate * $legal_holiday_percentage) * $legal_holiday);
                    $legal_holiday_ot_amount = (($allUser->hourly_rate * $legal_holiday_ot_percentage) * $legal_holiday_ot);
                    $night_differential_amount = (($allUser->hourly_rate * $night_diff_percentage) * $night_differential);

                    $OTEarnings = $regular_ot_amount + $rest_day_amount + $rest_day_ot_amount + $legal_holiday_amount + $legal_holiday_ot_amount + $night_differential_amount;
                    $TotalSSSBasis = 0;
                    $prev_basic_salary = 0;
                    $prev_OTEarnings = 0;
                    if($cut_off == 'B'){
                        $get_prev_salary = self::get_prev_salary($allUser->id, $month, $year);
                        $prev_basic_salary = $get_prev_salary['basic_salary'];
                        $prev_night_differential_amount = $get_prev_salary['night_differential_amount'];
                        $prev_legal_holiday_ot_amount = $get_prev_salary['legal_holiday_ot_amount'];
                        $prev_legal_holiday_amount = $get_prev_salary['legal_holiday_amount'];
                        $prev_rest_day_ot_amount = $get_prev_salary['rest_day_ot_amount'];
                        $prev_rest_day_amount = $get_prev_salary['rest_day_amount'];
                        $prev_regular_ot_amount = $get_prev_salary['regular_ot_amount'];
                        $prev_OTEarnings = $prev_regular_ot_amount + $prev_rest_day_amount + $prev_rest_day_ot_amount + $prev_legal_holiday_amount + $prev_legal_holiday_ot_amount + $prev_night_differential_amount;
                    }

                    $TotalSSSBasis = (($basicSalary + $OTEarnings) + ($prev_basic_salary + $prev_OTEarnings));
                    // \Log::info($TotalSSSBasis);

                    // SSSS Calculation
                    $sssData = self::get_sss_amount($allUser->id, $month, $year, $TotalSSSBasis, $allUser->calculation_type, $cut_off);
                    
                    // Pagibig Calculation
                    $pagibigData = self::get_pagibig_amount($allUser->id, $month, $year, $allUser->basic_salary, $allUser->calculation_type, $cut_off);

                    // PhilHealth Calculation
                    $philhealthData = self::get_philhealth_amount($allUser->id, $month, $year, $allUser->basic_salary, $allUser->calculation_type, $cut_off);
                    // \Log::debug($philhealthData);
                    $sss_empee_share = $sssData['employee_share'] ?? 0;
                    $sss_emper_share  = $sssData['employer_share'] ?? 0;
                    $sss_mpf_er = $sssData['mpf_yer'] ?? 0;
                    $sss_mpf_ee = $sssData['mpf_ee'] ?? 0;
                    $sss_ec_er = $sssData['ec_yer'] ?? 0;
                    $pagibig_er = $pagibigData['employer_share'] ?? 0;
                    $pagibig_ee = $pagibigData['employee_share'] ?? 0;
                    $philhealth_er = $philhealthData['employer_share'] ?? 0;
                    $philhealth_ee = $philhealthData['employee_share'] ?? 0;
                    
                    $taxable_forBIR = ($basicSalary - (($sss_empee_share + $sss_mpf_ee) + $pagibig_ee + $philhealth_ee));
                    // tax calculation
                    $tax_data = self::get_withheld_tax($allUser->id, $month, $year, $taxable_forBIR, $allUser->calculation_type, $cut_off, $sched['tax_schedule']);
                    
                    $tax_withheld = $tax_data['per_cutoff_tax'] ?? 0;

                    $netSalary = ($basicSalary +  $OTEarnings + $EarnTax + $EarnNonTax) - (((($sss_empee_share + $sss_mpf_ee) + $pagibig_ee + $philhealth_ee) + $tax_withheld) +  ($DeaductNonTax + $DeaductTax) );
                    $payroll->user_id = $allUser->id;
                    $payroll->year = $year;
                    $payroll->month = $month;
                    $payroll->cut_off = $cut_off;
                    $payroll->total_days = $total_days;
                    $payroll->basic_salary = $basicSalary;
                    $payroll->working_days = $resultData['working_days'];


                    $payroll->total_office_time = 0;
                    $payroll->total_worked_time = 0;
                    $payroll->half_days = 0;
                    $payroll->late_days = 0;
                    $payroll->paid_leaves = 0;
                    $payroll->unpaid_leaves = 0;
                    $payroll->holiday_count = 0;
                    
                    
                    
                    
                    
                    
                    
                    $payroll->present_days = $working_days;
                    $payroll->salary_amount = 0;
                    $payroll->net_salary = $netSalary;
                    $payroll->status = "generated";
                    $payroll->updated_by = $user->id;
                    $payroll->payment_date = null;
                    $payroll->company_id = $company->id;
                    $payroll->sss_share_er = $sss_emper_share;
                    $payroll->sss_share_ee = $sss_empee_share;
                    $payroll->sss_mpf_er = $sss_mpf_er;
                    $payroll->sss_ec_er = $sss_ec_er;
                    $payroll->sss_mpf_ee = $sss_mpf_ee;
                    $payroll->pagibig_share_er = $pagibig_er;
                    $payroll->pagibig_share_ee = $pagibig_ee;
                    $payroll->philhealth_share_er = $philhealth_er;
                    $payroll->philhealth_share_ee = $philhealth_ee;
                    $payroll->taxable_income = $taxable_forBIR;
                    $payroll->tax_withheld = $tax_withheld ?? 0;
                    $payroll->night_differential_amount	 = $night_differential_amount;
                    $payroll->legal_holiday_ot_amount	 = $legal_holiday_ot_amount;
                    $payroll->legal_holiday_amount	 = $legal_holiday_amount;
                    $payroll->rest_day_ot_amount	 = $rest_day_ot_amount;
                    $payroll->rest_day_amount	 = $rest_day_amount;
                    $payroll->regular_ot_amount	 = $regular_ot_amount;
                    if($netSalary > 0){
                        $payroll->save();
                    }
                    

                    if($allUser->id == 12){

                        // \Log::debug('Regular OT: ' . $regular_ot_amount);
                        // \Log::debug('rest_day_amount: ' . $rest_day_amount);
                        // \Log::debug('rest_day_ot_amount: ' . $rest_day_ot_amount);
                        // \Log::debug('legal_holiday_amount: ' . $legal_holiday_amount);
                        // \Log::debug('legal_holiday_ot_amount: ' . $legal_holiday_ot_amount);
                        // \Log::debug('night_differential_amount: ' . $night_differential_amount);
                        // \Log::debug('OT Earnings: ' . $OTEarnings);

                        
                    }
            }
        }
        } catch (\Throwable $th) {
            throw $th;
        }
        // && $payrollGenerateRequest->cut_off == "A"
        
        
    }

}
