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
        <tr><td colspan="2">Overtime</td></tr>
        <tr><td>Earnings Tax</td><td class="right">750.00</td></tr>
        <tr><td>Earnings Non Tax</td><td class="right">-</td></tr>
        
        <tr class="bold"><td>Total Earnings</td><td class="right">27,153.08</td></tr>
      </tbody>
    </table>

    <!-- Deductions Table -->
    <table style="width: 100%;">
      <thead>
        <tr><th colspan="2">Deductions</th></tr>
      </thead>
      <tbody>
        <tr><td>Absences</td><td class="right">-</td></tr>
        <tr><td>SSS Contribution</td><td class="right">500.00</td></tr>
        <tr><td>Pag-ibig Contribution</td><td class="right">100.00</td></tr>
        <tr><td>Philhealth Contribution</td><td class="right">250.00</td></tr>
        <tr><td>SGESLA Contribution</td><td class="right">2,000.00</td></tr>
        <tr><td>Coop</td><td class="right">4,560.00</td></tr>
        <tr><td>HDMF Salary Loan</td><td class="right">3,063.32</td></tr>
        <tr><td>HDMF Calamity Loan</td><td class="right">887.87</td></tr>
        <tr><td>SSS Salary Loan</td><td class="right">-</td></tr>
        <tr><td>SSS Calamity Loan</td><td class="right">-</td></tr>
        <tr><td>HDMF Voluntary Share</td><td class="right">500.00</td></tr>
        <tr class="bold"><td>Total Deductions</td><td class="right">11,861.19</td></tr>
      </tbody>
    </table>
  </div>

</body>
</html>
