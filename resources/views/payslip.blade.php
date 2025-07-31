<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Payslip</title>
<style>
    body {
        font-family: Arial, sans-serif;
        padding: 40px;
    }
    h2 {
        text-align: center;
        margin-bottom: 5px;
    }
    h3 {
        text-align: center;
        margin-top: 0;
        margin-bottom: 20px;
    }
    .table-container {
        display: flex;
        justify-content: space-between;
    }
    table {
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    th, td {
        padding: 6px 8px;
        border: 1px solid #ccc;
        font-size: 14px;
    }
    .summary {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
        font-size: 14px;
    }
    .summary div {
        width: 24%;
    }
    .footer {
        margin-top: 30px;
        font-size: 14px;
    }
    .footer p {
        margin: 3px 0;
    }
    .bold {
        font-weight: bold;
    }
    .right {
        text-align: right;
    }
</style>
</head>
<body>
    <table style="width: 100%; border: 3px solid black; border-collapse: collapse;" border="1">
        <thead>
            <tr>
                <td colspan="2" style="border: 1px solid black; padding: 8px;"><b>COMPANY NAME: SIGMA SECURITY SERVICES, INC</b></td>
                <td style="border: 1px solid black; padding: 8px; width: 150px;"><b>06.15.2025</b></td>
            </tr>
            <tr>
                <td style="border: 1px solid black; padding: 8px;"><b>ID NO. / NAME:</b></td>
                <td style="border: 1px solid black; padding: 8px;"><b>DEPARTMENT:</b></td>
                <td style="border: 1px solid black; padding: 8px;"><b>NET PAY:</b></td>
            </tr>
            <tr>
                <td style="border: 1px solid black; padding: 8px;"><b>{{ $data->user->name }}</b></td>
                <td style="border: 1px solid black; padding: 8px;"><b>{{ $data->user->location->name }}</b></td>location
                <td style="border: 1px solid black; padding: 8px;"><b>{{ number_format($data->net_salary, 2) }}</b></td>
            </tr>
        </thead>
    </table>

  <div class="table-container">
    <table style="width: 100%;">
      <thead>
        <tr><th colspan="2">Earnings</th></tr>
      </thead>
      <tbody>
        <tr><td>Basic Salary</td><td class="right">{{ number_format($data->basic_salary, 2) }}</td></tr>
        <tr><th colspan="2">Overtime</th></tr>
        {{ $overTimePay = 0; }}
        {{ $overTimePay = $data->night_differential_amount + $data->legal_holiday_ot_amount + $data->legal_holiday_amount + $data->rest_day_ot_amount + $data->rest_day_amount + $data->regular_ot_amount; }}
        <tr><td>Overtime Pay</td><td class="right">{{ number_format($overTimePay, 2) }}</td></tr>
        <tr><td>Earnings Tax</td><td class="right">{{ number_format($EarnTax->sum('amount'), 2) }}</td></tr>
        <tr><td>Earnings Non Tax</td><td class="right">{{ number_format($EarnNonTax->sum('amount'), 2) }}</td></tr>
        
        <tr class="bold"><td>Total Earnings</td><td class="right">{{ number_format( ($data->basic_salary + $overTimePay + $EarnTax->sum('amount') + $EarnNonTax->sum('amount')), 2) }}</td></tr>
      </tbody>
    </table>

    <!-- Deductions Table -->
    <table style="width: 100%;">
      <thead>
        <tr><th colspan="2">Deductions</th></tr>
      </thead>
      <tbody>
        {{ $totalDeducation = 0; }}
        <tr><td>Absences</td><td class="right">-</td></tr>
        <tr><td>Late</td><td class="right">-</td></tr>
        <tr><td>Undertime</td><td class="right">-</td></tr>
        <tr><td>SSS Contribution</td><td class="right">{{ number_format($data->sss_share_ee + $data->sss_mpf_ee, 2) }}</td></tr>
        <tr><td>Pag-ibig Contribution</td><td class="right">{{ number_format($data->pagibig_share_ee, 2) }}</td></tr>
        <tr><td>Philhealth Contribution</td><td class="right">{{ number_format($data->philhealth_share_ee, 2) }}</td></tr>
        {{ $totalDeducation = $data->sss_share_ee + $data->sss_mpf_ee + $data->pagibig_share_ee + $data->philhealth_share_ee; }}
        @foreach ($DedcTax as $index => $value)
            <tr><td>{{ $value->title }}</td><td class="right">{{ number_format($value->amount, 2) }}</td></tr>
        @endforeach
        {{ $totalDeducation += $DedcTax->sum('amount') }}
        @foreach ($DedcNonTax as $index => $value)
            <tr><td>{{ $value->title }}</td><td class="right">{{ number_format($value->amount, 2) }}</td></tr>
        @endforeach
        {{ $totalDeducation += $DedcNonTax->sum('amount') }}
        <tr class="bold"><td>Total Deductions</td><td class="right">{{ number_format($totalDeducation, 2) }}</td></tr>
      </tbody>
    </table>
  </div>

</body>
</html>
