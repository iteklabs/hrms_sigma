<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Payslip</title>
<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 13px;
        padding: 15px;
    }
    table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 10px;
    }
    th, td {
        border: 1px solid #000;
        padding: 5px 8px;
    }
    th {
        background: #f2f2f2;
        text-align: left;
    }
    .right {
        text-align: right;
    }
    .bold {
        font-weight: bold;
    }
</style>
</head>
<body>

    <!-- HEADER -->
    <table>
        <tr>
            <td colspan="2"><b>COMPANY NAME: SIGMA SECURITY SERVICES, INC</b></td>
            <td style="width:150px;"><b>{{ now()->format('m.d.Y') }}</b></td>
        </tr>
        <tr>
            <td><b>ID NO. / NAME:</b></td>
            <td><b>DEPARTMENT:</b></td>
            <td><b>NET PAY:</b></td>
        </tr>
        <tr>
            <td><b>{{ $data->user->employee_no }} - {{ $data->user->name }}</b></td>
            <td><b>{{ $data->user->location->name }}</b></td>
            <td class="right"><b>{{ number_format($data->net_salary, 2) }}</b></td>
        </tr>
    </table>

    <!-- EARNINGS & DEDUCTIONS SIDE BY SIDE -->
    <table>
        <tr>
            <!-- Earnings -->
            <td style="width: 50%; vertical-align: top;">
                <table style="width: 100%;">
                    <thead>
                        <tr><th colspan="2">Earnings</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Basic Salary</td><td class="right">{{ number_format($data->basic_salary, 2) }}</td></tr>
                        <tr><th colspan="2">Overtime</th></tr>
                        {{ $overTimePay = $data->night_differential_amount + $data->legal_holiday_ot_amount + $data->legal_holiday_amount + $data->rest_day_ot_amount + $data->rest_day_amount + $data->regular_ot_amount; }}
                        <tr><td>Overtime Pay</td><td class="right">{{ number_format($overTimePay, 2) }}</td></tr>
                        <tr><td>Earnings Tax</td><td class="right">{{ number_format($EarnTax->sum('amount'), 2) }}</td></tr>
                        <tr><td>Earnings Non Tax</td><td class="right">{{ number_format($EarnNonTax->sum('amount'), 2) }}</td></tr>
                        <tr class="bold">
                            <td>Total Earnings</td>
                            <td class="right">
                                {{ number_format($data->basic_salary + $overTimePay + $EarnTax->sum('amount') + $EarnNonTax->sum('amount'), 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>

            <!-- Deductions -->
            <td style="width: 50%; vertical-align: top;">
                <table style="width: 100%;">
                    <thead>
                        <tr><th colspan="2">Deductions</th></tr>
                    </thead>
                    <tbody>
                        {{ $totalDeducation = $data->sss_share_ee + $data->sss_mpf_ee + $data->pagibig_share_ee + $data->philhealth_share_ee; }}
                        <tr><td>Absences</td><td class="right">-</td></tr>
                        <tr><td>Late</td><td class="right">-</td></tr>
                        <tr><td>Undertime</td><td class="right">-</td></tr>
                        <tr><td>SSS Contribution</td><td class="right">{{ number_format($data->sss_share_ee + $data->sss_mpf_ee, 2) }}</td></tr>
                        <tr><td>Pag-ibig Contribution</td><td class="right">{{ number_format($data->pagibig_share_ee, 2) }}</td></tr>
                        <tr><td>Philhealth Contribution</td><td class="right">{{ number_format($data->philhealth_share_ee, 2) }}</td></tr>
                        
                        @foreach ($DedcTax as $value)
                            <tr><td>{{ $value->title }}</td><td class="right">{{ number_format($value->amount, 2) }}</td></tr>
                        @endforeach
                        {{ $totalDeducation += $DedcTax->sum('amount') }}

                        @foreach ($DedcNonTax as $value)
                            <tr><td>{{ $value->title }}</td><td class="right">{{ number_format($value->amount, 2) }}</td></tr>
                        @endforeach
                        {{ $totalDeducation += $DedcNonTax->sum('amount') }}

                        <tr class="bold">
                            <td>Total Deductions</td>
                            <td class="right">{{ number_format($totalDeducation, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <!-- FINAL NET PAY SUMMARY -->
    <table>
        <tr class="bold" style="background: #eaeaea;">
            <td style="width: 70%; text-align: right;">Net Pay:</td>
            <td class="right" style="width: 30%;">{{ number_format($data->net_salary, 2) }}</td>
        </tr>
    </table>

</body>
</html>
