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

class Payrolls
{
    public static function updateUserSalary($userId, $annualCTC)
    {
        $user = StaffMember::find($userId);
       
        $calculationType = $user->calculation_type;
        $ctcValue = (float)$user->ctc_value;
        if ($ctcValue == 0 || $annualCTC == 0) {
            $monthlySalary = 0;
        } else {
            if ($ctcValue == 0 || $ctcValue === null) {
                $monthlySalary = 0;
            } else {
                $monthlySalary = $calculationType === 'fixed'
                    ? $ctcValue
                    : ($annualCTC * $ctcValue) / 100 / 12;
            }
        }

        $annualAmount = $monthlySalary * 12;
        $monthlyCtc = $annualCTC / 12;
        $earnings = 0;
        $deductions = 0;

        $basicSalaryDetails = $user->basicSalaryDetails ? $user->basicSalaryDetails : [];
        foreach ($basicSalaryDetails as $group) {
            $salaryComponents = $group->salaryComponent ? $group->salaryComponent : [];
            foreach ($salaryComponents as $component) {
                $amount = 0.0;

                switch ($component->value_type) {
                    case 'fixed':
                        $amount = (float) $component->monthly;
                        break;
                    case 'variable':
                        $amount = (float) $group['monthly'];
                        break;

                    case 'basic_percent':
                        $amount = ($monthlySalary * (float) $component->monthly) / 100;
                        break;

                    case 'ctc_percent':
                        if ($ctcValue != 0) {
                            $amount = ($monthlySalary * (float) $component->monthly) / $ctcValue;
                        } else {
                            $amount = 0;
                        }
                        break;

                    default:
                        $amount = 0.0;
                        break;
                }

                if ($component->type === 'earnings') {
                    $earnings += (float) $amount;
                } elseif ($component->type === 'deductions') {
                    $deductions += (float) $amount;
                }
            }
        }
        $specialAllowance = number_format(($monthlyCtc - $monthlySalary - $earnings), 2, '.', '');
        $netSalary = number_format(
            ($monthlySalary + $specialAllowance + $earnings - $deductions),
            2,
            '.',
            ''
        );
        $user->update([
            'basic_salary' => $monthlySalary,
            'monthly_amount' => $netSalary,
            'annual_amount' => $annualAmount,
            'annual_ctc' => $annualCTC,
            'special_allowances' => $specialAllowance,
            'net_salary'
            => $netSalary
        ]);
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
    public static function updateEmployeeSalary($id, $basicSalary, $monthlyAmount, $annualAmount, $annualCtc, $ctcValue, $netSalary, $specialAllowances, $salaryComponents, $salaryGroupId)
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

    // public static function payrollGenerateRegenerate($payrollGenerateRequest, $user, $company)
    // {
    //     if ($payrollGenerateRequest->has('year') && $payrollGenerateRequest->has('month') && $payrollGenerateRequest->month > 0) {
    //         $year = (int) $payrollGenerateRequest->year;
    //         $month = (int) $payrollGenerateRequest->month;
    //         $allUsers = StaffMember::select('id', 'basic_salary', 'salary_group_id', 'ctc_value', 'annual_ctc', 'calculation_type')
    //             ->where('status', 'active')
    //             ->whereNotNull('ctc_value')
    //             ->with('salaryGroup.salaryGroupComponents.salaryComponent')->get();
            
    //         $userIds = [];
    //         if ($payrollGenerateRequest->has('users')) {
    //             $userIds = Common::getIdArrayFromHash($payrollGenerateRequest->users);

    //             $allUsers = $allUsers->whereIn('id', $userIds);
    //         }
            
    //         foreach ($allUsers as $allUser) {
    //             $payroll = Payroll::where('month', $month)
    //                 ->where('year', $year)
    //                 ->where('user_id', $allUser->id)
    //                 ->first();

    //             if (!$payroll) {
    //                 $payroll = new Payroll();
    //                 $payroll->created_by = $user->id;
    //             }

    //             // Delete existing PayrollComponent records for the given payroll
    //             PayrollComponent::where('payroll_id', $payroll->id)
    //                 ->where(function ($query) {
    //                     $query->whereIn('type', [
    //                         'custom_earning',
    //                         'custom_deduction',
    //                         'additional_earning',
    //                         'expenses',
    //                         'pre_payments',
    //                         'earnings',
    //                         'deductions',
    //                     ]);
    //                 })
    //                 ->delete();
    //             // Recalculate basic salary
    //             $basicSalary = $allUser->basic_salary ?? 0;

    //             $resultData = CommonHrm::getMonthYearAttendanceDetails($allUser->id, $month, $year);
    //             $holidayCount = $resultData['holiday_count'];
    //             $paidLeaveCount = $resultData['total_paid_leaves'];
    //             $totalDaysInMonth = $resultData['total_days'];
    //             $workingDays = $resultData['working_days'];
    //             $presentDays = $resultData['present_days'];
    //             $totalUnpaidLeaves = $resultData['total_unpaid_leaves'];

    //             $payroll->user_id = $allUser->id;
    //             $payroll->year = $year;
    //             $payroll->month = $month;
    //             $payroll->total_days = $totalDaysInMonth;
    //             $payroll->basic_salary = $basicSalary;
    //             $payroll->working_days = $resultData['working_days'];
    //             $payroll->present_days = $resultData['present_days'];
    //             $payroll->total_office_time = $resultData['total_office_time'];
    //             $payroll->total_worked_time = $resultData['clock_in_duration'];
    //             $payroll->half_days = $resultData['half_days'];
    //             $payroll->late_days = $resultData['total_late_days'];
    //             $payroll->paid_leaves = $paidLeaveCount;
    //             $payroll->unpaid_leaves = $resultData['total_unpaid_leaves'];
    //             $payroll->holiday_count = $holidayCount;
    //             $payroll->salary_amount = 0;
    //             $payroll->net_salary = 0;
    //             $payroll->status = "generated";
    //             $payroll->updated_by = $user->id;
    //             $payroll->payment_date = null;
    //             $payroll->company_id = $company->id;
    //             $payroll->save();

    //             //insert salary component according to salary group which is assign to users
    //             $earnings = 0.0;
    //             $deductions = 0.0;
    //             if ($allUser->salaryGroup && $allUser->salaryGroup->salaryGroupComponents) {
    //                 foreach ($allUser->salaryGroup->salaryGroupComponents as $component) {
    //                     $salaryComponent = $component->salaryComponent;

    //                     $basicSalary = $allUser->basic_salary;
    //                     $annualCTC = (float) $allUser->annual_ctc;
    //                     $ctcAmountMonthly = (float)  $annualCTC / 12;
    //                     $monthlyCtc =
    //                         ($ctcAmountMonthly * $presentDays) / $totalDaysInMonth;

    //                     $unpaidDaysPayment =  ($ctcAmountMonthly * $totalUnpaidLeaves) / $totalDaysInMonth;

    //                     $payrollComponent = new PayrollComponent();
    //                     $payrollComponent->user_id = $allUser->id;
    //                     $payrollComponent->payroll_id = $payroll->id;
    //                     $payrollComponent->salary_component_id = $salaryComponent->id;
    //                     $payrollComponent->name = $salaryComponent->name;
    //                     $payrollComponent->value_type = $salaryComponent->value_type;
    //                     $payrollComponent->type = $salaryComponent->type;
    //                     $payrollComponent->is_earning = $salaryComponent->type == 'deductions' ? 0 : 1;
    //                     $payrollComponent->company_id = $company->id;
    //                     $payrollComponent->amount = $salaryComponent->monthly;

    //                     $amount = 0.0;
                       
    //                     switch ($payrollComponent->value_type) {
    //                         case 'fixed':
    //                             $amount = (float) $payrollComponent->amount;
    //                             break;
    //                         case 'variable':
    //                             $amount = (float) $payrollComponent['amount'];
    //                             break;
    //                         case 'basic_percent':
    //                             $amount = ($basicSalary * (float) $payrollComponent->amount) / 100;
    //                             break;
    //                         case 'ctc_percent':
    //                             $amount = ($monthlyCtc * (float) $payrollComponent->amount) / 100;
    //                             break;
    //                         default:
    //                             $amount = 0.0;
    //                             break;
    //                     }
    //                     $payrollComponent->monthly_value = round($amount, 2);
    //                     $payrollComponent->save();

    //                     if (strtolower(trim($payrollComponent->type)) === 'earnings') {
    //                         $earnings += $amount;
    //                     } elseif (strtolower(trim($payrollComponent->type)) === 'deductions') {
    //                         $deductions += $amount;
    //                     }
    //                 }
    //             }
    //             $ctcAmountMonthly = 0;

    //             if (isset($allUser->annual_ctc) && $allUser->annual_ctc > 0) {
    //                 $annualCTC = (float) $allUser->annual_ctc;
    //                 $ctcAmountMonthly = $annualCTC / 12;
    //             }

    //             $unpaidDaysPayment = 0;

    //             if ($totalDaysInMonth > 0) {
    //                 $unpaidDaysPayment = ($ctcAmountMonthly * $totalUnpaidLeaves) / $totalDaysInMonth;
    //             }

    //             if ($unpaidDaysPayment != 0) {
    //                 $unpaidDaysComponent = new PayrollComponent();
    //                 $unpaidDaysComponent->user_id = $allUser->id;
    //                 $unpaidDaysComponent->payroll_id = $payroll->id;
    //                 $unpaidDaysComponent->name = "Leave Deduction";
    //                 $unpaidDaysComponent->value_type = 'fixed';
    //                 $unpaidDaysComponent->type = 'deductions';
    //                 $unpaidDaysComponent->is_earning = 0;
    //                 $unpaidDaysComponent->company_id = $company->id;
    //                 $unpaidDaysComponent->amount = round($unpaidDaysPayment, 2);
    //                 $unpaidDaysComponent->monthly_value
    //                     = round($unpaidDaysPayment, 2);
    //                 $unpaidDaysComponent->save();
    //             }


    //             $specialAllowance = $basicSalary - $earnings;

    //             $totalPrePaymentAmount = 0;
    //             $totalExpenseAmount = 0;


    //             // Getting all Pre payments
    //             $allPrepayments = PrePayment::where('user_id', $allUser->id)
    //                 ->where('payroll_month', $month)
    //                 ->where('payroll_year', $year)
    //                 ->get();

    //             foreach ($allPrepayments as $allPrepayment) {
    //                 $newPrePaymentComponent = new PayrollComponent();
    //                 $newPrePaymentComponent->payroll_id = $payroll->id;
    //                 $newPrePaymentComponent->pre_payment_id = $allPrepayment->id;
    //                 $newPrePaymentComponent->user_id = $allUser->id;
    //                 $newPrePaymentComponent->name = "Pre Payments";
    //                 $newPrePaymentComponent->amount = $allPrepayment->amount;
    //                 $newPrePaymentComponent->monthly_value = $allPrepayment->amount;
    //                 $newPrePaymentComponent->is_earning = 0;
    //                 $newPrePaymentComponent->type = 'pre_payments';
    //                 $newPrePaymentComponent->company_id = $company->id;
    //                 $newPrePaymentComponent->save();

    //                 $totalPrePaymentAmount += $allPrepayment->amount;
    //             }

    //             // Getting all Expenses
    //             $allExpenses = Expense::where('user_id', $allUser->id)
    //                 ->where('payment_status', '0')
    //                 ->where('status', 'approved')
    //                 ->where('payroll_month', $month)
    //                 ->where('payroll_year', $year)
    //                 ->get();

    //             foreach ($allExpenses as $allExpense) {
    //                 $newExpenseComponent = new PayrollComponent();
    //                 $newExpenseComponent->payroll_id = $payroll->id;
    //                 $newExpenseComponent->expense_id = $allExpense->id;
    //                 $newExpenseComponent->user_id = $allUser->id;
    //                 $newExpenseComponent->name = "Expense Claim";
    //                 $newExpenseComponent->amount = $allExpense->amount;
    //                 $newExpenseComponent->monthly_value = $allExpense->amount;
    //                 $newExpenseComponent->is_earning = 1;
    //                 $newExpenseComponent->type = 'expenses';
    //                 $newExpenseComponent->company_id = $company->id;
    //                 $newExpenseComponent->save();

    //                 $totalExpenseAmount += $allExpense->amount;
    //             }

    //             $payroll->pre_payment_amount = $totalPrePaymentAmount;
    //             $payroll->expense_amount = $totalExpenseAmount;
    //             // $payroll->net_salary =   $ctcAmountMonthly - $basicSalary - $totalPrePaymentAmount + $totalExpenseAmount
    //             //     + $earnings - $deductions + $specialAllowance - $unpaidDaysPayment;
    //             $payroll->net_salary = $basicSalary;

    //             \Log::info($ctcAmountMonthly . " <-> " . $basicSalary . " <-> " . $totalPrePaymentAmount . " <+> " . $totalExpenseAmount . " <+> " . $earnings . " <-> " . $deductions . " <+> " . $specialAllowance . " <-> " . $unpaidDaysPayment);
    //             $payroll->salary_amount
    //                 = $ctcAmountMonthly - $basicSalary - $totalPrePaymentAmount + $totalExpenseAmount
    //                 + $earnings - $deductions + $specialAllowance - $unpaidDaysPayment;

    //             // This is use for update account balance on payroll generate and regenerate
    //             if ($payroll->account_id) {
    //                 DB::table('account_entries')
    //                     ->where('payroll_id', $payroll->id)
    //                     ->where('account_id', $payroll->account_id)
    //                     ->delete();

    //                 CommonHrm::updateAccountAmount($payroll->account_id);
    //             }
    //             $payroll->save();
    //         }
    //     }
    // }

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
            'taxable_income' => 0
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
        // \Log::info("SSS Details: ");
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
                    return [
                        'employer_share' => $employer_share,
                        'employee_share' => $employee_share
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
        // \Log::info('Salary: ' . $type_tax);
        // if($cutOff == 'A'){
        //     $taxable = $taxable * 24;
        //     $taxable_annual_gross = (float) $taxable ?? 0;
        //     $taxable_table = TaxBir::where('min_salary', '<=', $taxable_annual_gross)
        //         ->where('max_salary', '>=', $taxable_annual_gross)
        //         ->first();

        //     $get_prev_salary = self::get_prev_salary($id, $month, $year);
        //     $prev_tax_withheld = $get_prev_salary['tax_withheld'] ?? 0;

        //     switch ($calculationType) {
        //         case 's_monthly':
        //             $annual_tax = ((($taxable_annual_gross - $taxable_table->min_salary) * $taxable_table->tax_percentage) + $taxable_table->fixed_amount);
        //             $annual_tax = number_format($annual_tax, 2, '.', '');
        //             $monthly_tax = $annual_tax / 12;
        //             $per_cutoff_tax = $monthly_tax / 2;

        //             return [
        //                 'annual_tax' => $annual_tax,
        //                 'monthly_tax' => number_format($monthly_tax, 2, '.', ''),
        //                 'per_cutoff_tax' => number_format($per_cutoff_tax, 2, '.', '')
        //             ];
        //             // \Log::info('Annual Tax: ' . $annual_tax);
        //             // \Log::info('Monthly Tax: ' . $monthly_tax);
        //             // \Log::info('Per Cutoff Tax: ' . $per_cutoff_tax);

        //         break;

        //         case 'monthly':
                    
        //         break;
                
        //     }
        // }else if($cutOff == 'B'){
        //     $get_prev_salary = self::get_prev_salary($id, $month, $year);
        //     // \Log::info($get_prev_salary);
        //     $taxable = ($taxable + $get_prev_salary["taxable_income"]) * 12;
            
        //      \Log::info('Annual Salary: ' . $taxable);
        //     $taxable_annual_gross = (float) $taxable ?? 0;
        //     $taxable_table = TaxBir::where('min_salary', '<=', $taxable_annual_gross)
        //         ->where('max_salary', '>=', $taxable_annual_gross)
        //         ->first();
        //     switch ($calculationType) {
        //         case 'daily':
        //         case 's_monthly':
        //             $annual_tax = ((($taxable_annual_gross - $taxable_table->min_salary) * $taxable_table->tax_percentage) + $taxable_table->fixed_amount);
        //             // Adjust the annual tax based on the previous tax withheld
                    
        //             $prev_tax_withheld = $get_prev_salary['tax_withheld'] ?? 0;
        //             $annual_tax = number_format($annual_tax, 2, '.', '');
        //             $monthly_tax = $annual_tax / 12;
        //             $per_cutoff_tax = $monthly_tax - $prev_tax_withheld;
        //             // \Log::info('Annual Tax: ' . $annual_tax);
        //             // \Log::info('Monthly Tax: ' . $monthly_tax);
        //             // \Log::info('Per Cutoff Tax: ' . $per_cutoff_tax);
        //             return [
        //                 'annual_tax' => $annual_tax,
        //                 'monthly_tax' => number_format($monthly_tax, 2, '.', ''),
        //                 'per_cutoff_tax' => number_format($per_cutoff_tax, 2, '.', '')
        //             ];
        //             // \Log::info('Annual Tax: ' . $annual_tax);
        //             // \Log::info('Monthly Tax: ' . $monthly_tax);
        //             // \Log::info('Per Cutoff Tax: ' . $per_cutoff_tax);

        //         break;

        //         case 'monthly':
                    
        //         break;
                
        //     }
        // }


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

    public static function payrollGenerateRegenerate($payrollGenerateRequest, $user, $company)
    {
        // && $payrollGenerateRequest->cut_off == "A"
        if($payrollGenerateRequest->has('year') && $payrollGenerateRequest->has('month') && $payrollGenerateRequest->month > 0 ){

            $year = (int) $payrollGenerateRequest->year;
            $month = (int) $payrollGenerateRequest->month;
            $cut_off = $payrollGenerateRequest->cut_off;

            $allUsers = StaffMember::select('id', 'basic_salary', 'salary_group_id', 'ctc_value', 'annual_ctc', 'calculation_type', 'name', 'company_id')
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
                \Log::info($sched);
                $payroll = Payroll::where('month', $month)
                    ->where('year', $year)
                    ->where('user_id', $allUser->id)
                    ->where('cut_off', $cut_off)
                    ->first();

                if (!$payroll) {
                    $payroll = new Payroll();
                    $payroll->created_by = $user->id;
                }

                    $basicSalary = $allUser->basic_salary / 2 ?? 0;
                    $resultData = CommonHrm::getMonthYearAttendanceDetails($allUser->id, $month, $year, $cut_off);
                    $totalDaysInMonth = $resultData['total_days'];
                    $holidayCount = $resultData['holiday_count'];
                    $paidLeaveCount = $resultData['total_paid_leaves'];
                    $workingDays = $resultData['working_days'];
                    $presentDays = $resultData['present_days'];
                    $totalUnpaidLeaves = $resultData['total_unpaid_leaves'];

                    \Log::debug($allUser->calculation_type . " - " . $cut_off . " - " . $allUser->name . " - " . $allUser->basic_salary);
                    // SSSS Calculation
                    $sssData = self::get_sss_amount($allUser->id, $month, $year, $allUser->basic_salary, $allUser->calculation_type, $cut_off);
                    
                    // Pagibig Calculation
                    $pagibigData = self::get_pagibig_amount($allUser->id, $month, $year, $allUser->basic_salary, $allUser->calculation_type, $cut_off);

                    // PhilHealth Calculation
                    $philhealthData = self::get_philhealth_amount($allUser->id, $month, $year, $allUser->basic_salary, $allUser->calculation_type, $cut_off);

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
                    if($allUser->id == "12"){
                        \Log::debug($sssData);
                    }
                    
                    // tax calculation
                    $tax_data = self::get_withheld_tax($allUser->id, $month, $year, $taxable_forBIR, $allUser->calculation_type, $cut_off, $sched['tax_schedule']);
                    
                    $tax_withheld = $tax_data['per_cutoff_tax'] ?? 0;
                    \Log::debug($sssData);

                    
                    
                    
                    // \Log::debug($philhealthData);
                    $netSalary = $basicSalary - ((($sss_empee_share + $sss_mpf_ee) + $pagibig_ee + $philhealth_ee) + $tax_withheld);
                    $payroll->user_id = $allUser->id;
                    $payroll->year = $year;
                    $payroll->month = $month;
                    $payroll->cut_off = $cut_off;
                    $payroll->total_days = $totalDaysInMonth;
                    $payroll->basic_salary = $basicSalary;
                    $payroll->working_days = $resultData['working_days'];
                    $payroll->present_days = $resultData['present_days'];
                    $payroll->total_office_time = $resultData['total_office_time'];
                    $payroll->total_worked_time = $resultData['clock_in_duration'];
                    $payroll->half_days = $resultData['half_days'];
                    $payroll->late_days = $resultData['total_late_days'];
                    $payroll->paid_leaves = $paidLeaveCount;
                    $payroll->unpaid_leaves = $resultData['total_unpaid_leaves'];
                    $payroll->holiday_count = $holidayCount;
                    $payroll->salary_amount = 0;
                    $payroll->net_salary = $netSalary;
                    $payroll->status = "generated";
                    $payroll->updated_by = $user->id;
                    $payroll->payment_date = null;
                    $payroll->company_id = $company->id;
                    $payroll->sss_share_er = $sssData['employer_share'] ?? 0;
                    $payroll->sss_share_ee = $sssData['employee_share'] ?? 0;
                    $payroll->sss_mpf_er = $sssData['mpf_yer'] ?? 0;
                    $payroll->sss_ec_er = $sssData['ec_yer'] ?? 0;
                    $payroll->sss_mpf_ee = $sssData['mpf_ee'] ?? 0;
                    $payroll->pagibig_share_er = $pagibigData['employer_share'] ?? 0;
                    $payroll->pagibig_share_ee = $pagibigData['employee_share'] ?? 0;
                    $payroll->philhealth_share_er = $philhealthData['employer_share'] ?? 0;
                    $payroll->philhealth_share_ee = $philhealthData['employee_share'] ?? 0;
                    $payroll->taxable_income = $taxable_forBIR;
                    $payroll->tax_withheld = $tax_withheld ?? 0;

                    // $payroll->sss_total_ee_share_mpf = $sssData['total_ee_share_mpf'];
                    // $payroll->sss_total_er_share_mpf = $sssData['total_er_share_mpf'];

                    $payroll->save();

                
                // \Log::debug('<pre>' . print_r($allUser->toArray(), true) . '</pre>');
            }
        }
        // if ($payrollGenerateRequest->has('year') && $payrollGenerateRequest->has('month') && $payrollGenerateRequest->month > 0) {
        //     $year = (int) $payrollGenerateRequest->year;
        //     $month = (int) $payrollGenerateRequest->month;
        //     $allUsers = StaffMember::select('id', 'basic_salary', 'salary_group_id', 'ctc_value', 'annual_ctc', 'calculation_type')
        //         ->where('status', 'active')
        //         ->whereNotNull('ctc_value')
        //         ->with('salaryGroup.salaryGroupComponents.salaryComponent')->get();
            
        //     $userIds = [];
        //     if ($payrollGenerateRequest->has('users')) {
        //         $userIds = Common::getIdArrayFromHash($payrollGenerateRequest->users);

        //         $allUsers = $allUsers->whereIn('id', $userIds);
        //     }
            
        //     foreach ($allUsers as $allUser) {
        //         $payroll = Payroll::where('month', $month)
        //             ->where('year', $year)
        //             ->where('user_id', $allUser->id)
        //             ->first();

        //         if (!$payroll) {
        //             $payroll = new Payroll();
        //             $payroll->created_by = $user->id;
        //         }

        //         // Delete existing PayrollComponent records for the given payroll
        //         PayrollComponent::where('payroll_id', $payroll->id)
        //             ->where(function ($query) {
        //                 $query->whereIn('type', [
        //                     'custom_earning',
        //                     'custom_deduction',
        //                     'additional_earning',
        //                     'expenses',
        //                     'pre_payments',
        //                     'earnings',
        //                     'deductions',
        //                 ]);
        //             })
        //             ->delete();
        //         // Recalculate basic salary
        //         $basicSalary = $allUser->basic_salary ?? 0;

        //         $resultData = CommonHrm::getMonthYearAttendanceDetails($allUser->id, $month, $year);
        //         $holidayCount = $resultData['holiday_count'];
        //         $paidLeaveCount = $resultData['total_paid_leaves'];
        //         $totalDaysInMonth = $resultData['total_days'];
        //         $workingDays = $resultData['working_days'];
        //         $presentDays = $resultData['present_days'];
        //         $totalUnpaidLeaves = $resultData['total_unpaid_leaves'];

        //         $payroll->user_id = $allUser->id;
        //         $payroll->year = $year;
        //         $payroll->month = $month;
        //         $payroll->total_days = $totalDaysInMonth;
        //         $payroll->basic_salary = $basicSalary;
        //         $payroll->working_days = $resultData['working_days'];
        //         $payroll->present_days = $resultData['present_days'];
        //         $payroll->total_office_time = $resultData['total_office_time'];
        //         $payroll->total_worked_time = $resultData['clock_in_duration'];
        //         $payroll->half_days = $resultData['half_days'];
        //         $payroll->late_days = $resultData['total_late_days'];
        //         $payroll->paid_leaves = $paidLeaveCount;
        //         $payroll->unpaid_leaves = $resultData['total_unpaid_leaves'];
        //         $payroll->holiday_count = $holidayCount;
        //         $payroll->salary_amount = 0;
        //         $payroll->net_salary = 0;
        //         $payroll->status = "generated";
        //         $payroll->updated_by = $user->id;
        //         $payroll->payment_date = null;
        //         $payroll->company_id = $company->id;
        //         $payroll->save();

        //         //insert salary component according to salary group which is assign to users
        //         $earnings = 0.0;
        //         $deductions = 0.0;
        //         if ($allUser->salaryGroup && $allUser->salaryGroup->salaryGroupComponents) {
        //             foreach ($allUser->salaryGroup->salaryGroupComponents as $component) {
        //                 $salaryComponent = $component->salaryComponent;

        //                 $basicSalary = $allUser->basic_salary;
        //                 $annualCTC = (float) $allUser->annual_ctc;
        //                 $ctcAmountMonthly = (float)  $annualCTC / 12;
        //                 $monthlyCtc =
        //                     ($ctcAmountMonthly * $presentDays) / $totalDaysInMonth;

        //                 $unpaidDaysPayment =  ($ctcAmountMonthly * $totalUnpaidLeaves) / $totalDaysInMonth;

        //                 $payrollComponent = new PayrollComponent();
        //                 $payrollComponent->user_id = $allUser->id;
        //                 $payrollComponent->payroll_id = $payroll->id;
        //                 $payrollComponent->salary_component_id = $salaryComponent->id;
        //                 $payrollComponent->name = $salaryComponent->name;
        //                 $payrollComponent->value_type = $salaryComponent->value_type;
        //                 $payrollComponent->type = $salaryComponent->type;
        //                 $payrollComponent->is_earning = $salaryComponent->type == 'deductions' ? 0 : 1;
        //                 $payrollComponent->company_id = $company->id;
        //                 $payrollComponent->amount = $salaryComponent->monthly;

        //                 $amount = 0.0;
                       
        //                 switch ($payrollComponent->value_type) {
        //                     case 'fixed':
        //                         $amount = (float) $payrollComponent->amount;
        //                         break;
        //                     case 'variable':
        //                         $amount = (float) $payrollComponent['amount'];
        //                         break;
        //                     case 'basic_percent':
        //                         $amount = ($basicSalary * (float) $payrollComponent->amount) / 100;
        //                         break;
        //                     case 'ctc_percent':
        //                         $amount = ($monthlyCtc * (float) $payrollComponent->amount) / 100;
        //                         break;
        //                     default:
        //                         $amount = 0.0;
        //                         break;
        //                 }
        //                 $payrollComponent->monthly_value = round($amount, 2);
        //                 $payrollComponent->save();

        //                 if (strtolower(trim($payrollComponent->type)) === 'earnings') {
        //                     $earnings += $amount;
        //                 } elseif (strtolower(trim($payrollComponent->type)) === 'deductions') {
        //                     $deductions += $amount;
        //                 }
        //             }
        //         }
        //         $ctcAmountMonthly = 0;

        //         if (isset($allUser->annual_ctc) && $allUser->annual_ctc > 0) {
        //             $annualCTC = (float) $allUser->annual_ctc;
        //             $ctcAmountMonthly = $annualCTC / 12;
        //         }

        //         $unpaidDaysPayment = 0;

        //         if ($totalDaysInMonth > 0) {
        //             $unpaidDaysPayment = ($ctcAmountMonthly * $totalUnpaidLeaves) / $totalDaysInMonth;
        //         }

        //         if ($unpaidDaysPayment != 0) {
        //             $unpaidDaysComponent = new PayrollComponent();
        //             $unpaidDaysComponent->user_id = $allUser->id;
        //             $unpaidDaysComponent->payroll_id = $payroll->id;
        //             $unpaidDaysComponent->name = "Leave Deduction";
        //             $unpaidDaysComponent->value_type = 'fixed';
        //             $unpaidDaysComponent->type = 'deductions';
        //             $unpaidDaysComponent->is_earning = 0;
        //             $unpaidDaysComponent->company_id = $company->id;
        //             $unpaidDaysComponent->amount = round($unpaidDaysPayment, 2);
        //             $unpaidDaysComponent->monthly_value
        //                 = round($unpaidDaysPayment, 2);
        //             $unpaidDaysComponent->save();
        //         }


        //         $specialAllowance = $basicSalary - $earnings;

        //         $totalPrePaymentAmount = 0;
        //         $totalExpenseAmount = 0;


        //         // Getting all Pre payments
        //         $allPrepayments = PrePayment::where('user_id', $allUser->id)
        //             ->where('payroll_month', $month)
        //             ->where('payroll_year', $year)
        //             ->get();

        //         foreach ($allPrepayments as $allPrepayment) {
        //             $newPrePaymentComponent = new PayrollComponent();
        //             $newPrePaymentComponent->payroll_id = $payroll->id;
        //             $newPrePaymentComponent->pre_payment_id = $allPrepayment->id;
        //             $newPrePaymentComponent->user_id = $allUser->id;
        //             $newPrePaymentComponent->name = "Pre Payments";
        //             $newPrePaymentComponent->amount = $allPrepayment->amount;
        //             $newPrePaymentComponent->monthly_value = $allPrepayment->amount;
        //             $newPrePaymentComponent->is_earning = 0;
        //             $newPrePaymentComponent->type = 'pre_payments';
        //             $newPrePaymentComponent->company_id = $company->id;
        //             $newPrePaymentComponent->save();

        //             $totalPrePaymentAmount += $allPrepayment->amount;
        //         }

        //         // Getting all Expenses
        //         $allExpenses = Expense::where('user_id', $allUser->id)
        //             ->where('payment_status', '0')
        //             ->where('status', 'approved')
        //             ->where('payroll_month', $month)
        //             ->where('payroll_year', $year)
        //             ->get();

        //         foreach ($allExpenses as $allExpense) {
        //             $newExpenseComponent = new PayrollComponent();
        //             $newExpenseComponent->payroll_id = $payroll->id;
        //             $newExpenseComponent->expense_id = $allExpense->id;
        //             $newExpenseComponent->user_id = $allUser->id;
        //             $newExpenseComponent->name = "Expense Claim";
        //             $newExpenseComponent->amount = $allExpense->amount;
        //             $newExpenseComponent->monthly_value = $allExpense->amount;
        //             $newExpenseComponent->is_earning = 1;
        //             $newExpenseComponent->type = 'expenses';
        //             $newExpenseComponent->company_id = $company->id;
        //             $newExpenseComponent->save();

        //             $totalExpenseAmount += $allExpense->amount;
        //         }

        //         $payroll->pre_payment_amount = $totalPrePaymentAmount;
        //         $payroll->expense_amount = $totalExpenseAmount;
        //         // $payroll->net_salary =   $ctcAmountMonthly - $basicSalary - $totalPrePaymentAmount + $totalExpenseAmount
        //         //     + $earnings - $deductions + $specialAllowance - $unpaidDaysPayment;
        //         $payroll->net_salary = $basicSalary;

        //         \Log::info($ctcAmountMonthly . " <-> " . $basicSalary . " <-> " . $totalPrePaymentAmount . " <+> " . $totalExpenseAmount . " <+> " . $earnings . " <-> " . $deductions . " <+> " . $specialAllowance . " <-> " . $unpaidDaysPayment);
        //         $payroll->salary_amount
        //             = $ctcAmountMonthly - $basicSalary - $totalPrePaymentAmount + $totalExpenseAmount
        //             + $earnings - $deductions + $specialAllowance - $unpaidDaysPayment;

        //         // This is use for update account balance on payroll generate and regenerate
        //         if ($payroll->account_id) {
        //             DB::table('account_entries')
        //                 ->where('payroll_id', $payroll->id)
        //                 ->where('account_id', $payroll->account_id)
        //                 ->delete();

        //             CommonHrm::updateAccountAmount($payroll->account_id);
        //         }
        //         $payroll->save();
        //     }
        // }
    }
}
