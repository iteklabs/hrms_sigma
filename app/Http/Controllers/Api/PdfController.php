<?php

namespace App\Http\Controllers\Api;

use App\Classes\Common;
use App\Http\Controllers\ApiBaseController;
use App\Models\Account;
use App\Models\AccountEntry;
use App\Models\Generate;
use App\Models\Holiday;
use App\Models\Lang;
use App\Models\Payroll;
use App\Models\Translation;
use App\Exports\AccountEntriesExport;
use App\Models\CompanyPolicy;
use Carbon\Carbon;
use Maatwebsite\Excel\Excel;
use PDF;

class PdfController extends ApiBaseController
{
    public function setPdfConfig($withHeaderFooter = true)
    {
        $company = company();
        $marginLeft = $withHeaderFooter ? 0 : $company->letterhead_left_space;
        $marginRight = $withHeaderFooter ? 0 : $company->letterhead_right_space;
        $marginTop = $withHeaderFooter ? $company->letterhead_top_space : $company->letterhead_top_space;
        $marginBottom = $withHeaderFooter ? $company->letterhead_bottom_space : $company->letterhead_bottom_space;
        $allFonts = [];

        if ($company->use_custom_font && $company->pdfFont && $company->pdfFont->id) {
            $allFonts[$company->pdfFont->short_name] = [
                'R'  => $company->pdfFont->file,
                'B'  => $company->pdfFont->file,
                'I'  => $company->pdfFont->file,
                'BI' => $company->pdfFont->file,
                'useOTL' => $company->pdfFont->user_kashida,
                'useKashida' => $company->pdfFont->use_otl,
            ];
        };

        config([
            'pdf.margin_left' => (int)$marginLeft,
            'pdf.margin_right' => (int)$marginRight,
            'pdf.margin_top' => (int)$marginTop,
            'pdf.margin_bottom' => (int)$marginBottom,
            'pdf.margin_header'  => (int)0,
            'pdf.margin_footer'  => (int)0,
            'pdf.auto_language_detection'  => $company->use_custom_font && $company->pdfFont && $company->pdfFont->id ? false : true,
            'pdf.custom_font_data' => $allFonts
        ]);
    }

    public function holidayPdf()
    {
        $request = request();
        $company = company();
        $title = $request->title;
        $year = $request->year;
        $langKey = $request->has('lang') ? $request->lang : 'en';
        $holidays = Holiday::where('is_weekend', 0)->whereYear('date', $year)->orderBy('date', 'asc')->get();

        $lang = Lang::where('key', $langKey)->first();
        if (!$lang) {
            $lang = Lang::where('key', 'en')->first();
        }

        $translation = Translation::where('lang_id', $lang->id)
            ->where('group', 'holiday')
            ->pluck('value', 'key')
            ->toArray();

        $margins = $this->getMargins();

        $pdfData = [
            'title' => $title,
            'company' => $company,
            'holidays' => $holidays,
            'translation' => $translation,
            'light_color' => Common::lightenHexColor($company->letterhead_header_color, 30),
            'showHeaderFooter' => true,
            'margins' => $margins,
        ];

        $this->setPdfConfig();

        $pdf = PDF::loadView('pdf.holiday', $pdfData);

        return $pdf->stream("holidays.pdf");
    }

    public function exportAccountEntries(Excel $excel)
    {
        $request = request();

        $accountId = $this->getIdFromHash($request->xid);
        $startDate = $request->date[0] ? Carbon::parse($request->date[0])->toDateString() : null;
        $endDate = $request->date[1] ? Carbon::parse($request->date[1])->toDateString() : null;
        $isType = $request->has('type') ? false : true;
        $openingBalance = $this->calculateOpeningBalance($startDate, $endDate, $accountId, $request->type ?? '');

        $export = new AccountEntriesExport($accountId, $startDate, $endDate, $isType, $openingBalance['opening_balance']);

        return response()->streamDownload(function () use ($export, $excel) {
            echo $excel->raw($export, Excel::CSV);
        }, 'account_entries.csv');
    }


    public function downloadAccountEntriesExcel(Excel $excel)
    {
        $request = request();
        $accountId = $this->getIdFromHash($request->xid);
        $startDate = $request->date[0] ? Carbon::parse($request->date[0])->toDateString() : null;
        $endDate = $request->date[1] ? Carbon::parse($request->date[1])->toDateString() : null;
        $isType = $request->has('type') ? false : true;
        $openingBalance = $this->calculateOpeningBalance($startDate, $endDate, $accountId, $request->type ?? '');

        $export = new AccountEntriesExport($accountId, $startDate, $endDate, $isType, $openingBalance['opening_balance']);

        return response()->streamDownload(function () use ($export, $excel) {
            echo $excel->raw($export, Excel::XLSX);
        }, 'account_entries.xlsx');
    }

    public function accountPdf()
    {
        $request = request();
        $company = company();

        $title = $request->title;
        $id = $this->getIdFromHash($request->xid);
        $langKey = $request->has('lang') ? $request->lang : 'en';

        $account = Account::find($id);

        $statementDate = '';
        $fromDate = '';
        $endDate = '';
        $isType = true;

        $statementDate = Carbon::now()->format('Y-m-d');

        if ($request->has('date') && count($request->date) > 0) {
            $dates = $request->date;
            $startDate = Carbon::parse($dates[0])->toDateString();
            $endDate = Carbon::parse($dates[1])->toDateString();
            $fromDate = $startDate;
            if ($request->has('type') && $request->type != "") {
                $openingDetail = $this->calculateOpeningBalance($startDate, $endDate, $id, $request->type);
                $isType = false;
            } else {

                $openingDetail = $this->calculateOpeningBalance($startDate, $endDate, $id, '');
            }
        } else {
            $fromDate = $account->created_at->format('Y-m-d');
            $startDate = null;
            $endDate = $statementDate;
            if ($request->has('type') && $request->type != "") {
                $openingDetail = $this->calculateOpeningBalance(null, $endDate, $id, $request->type);
                $isType = false;
            } else {

                $openingDetail = $this->calculateOpeningBalance(null, $statementDate, $id, '');
            }
        }


        $accountEntries = AccountEntry::select(
            'account_entries.account_id',
            'account_entries.amount',
            'account_entries.is_debit',
            'account_entries.type',
            'account_entries.date',
            'account_entries.deposit_id',
            'account_entries.initial_account_id'
        )
            ->where('account_entries.account_id', $id)
            ->when($request->has('date') && count($request->date) > 0, function ($query) use ($request) {
                $dates = $request->date;

                $startDate = Carbon::parse($dates[0])->toDateString();
                $endDate = Carbon::parse($dates[1])->toDateString();

                $query->whereBetween('account_entries.date', [$startDate, $endDate]);
            })
            ->when($request->has('type') && $request->type != "", function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->orderBy('date', 'asc')
            ->with('account')
            ->get();

        $lang = Lang::where('key', $langKey)->first();
        if (!$lang) {
            $lang = Lang::where('key', 'en')->first();
        }

        $translation = Translation::where('lang_id', $lang->id)
            ->where('group', 'account')
            ->pluck('value', 'key')
            ->toArray();

        $margins = $this->getMargins();

        $pdfData = [
            'title' => $title,
            'company' => $company,
            'accountEntries' => $accountEntries,
            'translation' => $translation,
            'account' => $account,
            'openingDetail' => $openingDetail,
            'statementDate' => $statementDate,
            'fromDate' => $fromDate,
            'endDate' => $endDate,
            'isType' => $isType,
            'showHeaderFooter' => true,
            'margins' => $margins,
        ];

        $this->setPdfConfig();

        $pdf = PDF::loadView('pdf.account_entries', $pdfData);

        return $pdf->stream("account_entries.pdf");
    }

    public function payrollPdf($xid)
    {
        $request = request();
        $id = $this->getIdFromHash($xid);
        $company = company();

        $id = $this->getIdFromHash($xid);
        $payroll = Payroll::with([
            'payrollComponents.prePayment',
            'user.department',
            'user.designation'
        ])->find($id);

        $lang = Lang::where('key', $request->lang)->first();
        if (!$lang) {
            $lang = Lang::where('key', 'en')->first();
        }
        $payrollTranslation = Translation::where('lang_id', $lang->id)
            ->where('group', 'payroll')
            ->pluck('value', 'key');

        $margins = $this->getMargins();


        $pdfData = [
            'htmlcontent' => $request->description,
            'title' => $request->title,
            'payroll' => $payroll,
            'company' => $company,
            'showHeaderFooter' => true,
            'payrollTranslation' => $payrollTranslation,
            'margins' => $margins,
        ];

        $this->setPdfConfig();

        $pdf = PDF::loadView('pdf.payroll', $pdfData);

        return $pdf->stream('payroll.pdf');
    }

    public function getMargins()
    {
        $company = company();

        return [
            'left' => $company->letterhead_left_space . 'mm',
            'right' => $company->letterhead_right_space . 'mm',
            'top' => $company->letterhead_top_space . 'mm',
            'bottom' => $company->letterhead_bottom_space . 'mm',
        ];
    }

    public function generatePdf($xid = null)
    {
        $request = request();
        $showHeaderFooter = $request->has('show_header_footer') && $request->show_header_footer == 'no' ? false : true;

        $company = company();

        $margins = $this->getMargins();

        if ($xid != null) {
            $id = $this->getIdFromHash($xid);
            $generate = Generate::where('id', $id)->with(['user'])->first();
            $title = $generate->title;
            $htmlcontent = $generate->description;
        } else {
            $title = $request->title;
            $htmlcontent = $request->html;
        }

        $pdfData = [
            'htmlcontent' => $htmlcontent,
            'title' => $title,
            'company' => $company,
            'showHeaderFooter' => $showHeaderFooter,
            'margins' => $margins,
        ];

        $this->setPdfConfig($showHeaderFooter);

        $pdf = PDF::loadView('pdf.generate', $pdfData);

        return $pdf->stream('Letter.pdf');
    }

    public function companyPolicyPdf($xid = null)
    {
        $request = request();
        $showHeaderFooter = $request->has('show_header_footer') && $request->show_header_footer == 'no' ? false : true;

        $company = company();

        $margins = $this->getMargins();

        if ($xid != null) {
            $id = $this->getIdFromHash($xid);
            $companyPolicy = CompanyPolicy::where('id', $id)->with(['location'])->first();
            $title = $companyPolicy->title;
            $htmlcontent = $companyPolicy->letter_description;
        } else {
            $title = $request->title;
            $htmlcontent = $request->html;
        }

        $pdfData = [
            'htmlcontent' => $htmlcontent,
            'title' => $title,
            'company' => $company,
            'showHeaderFooter' => $showHeaderFooter,
            'margins' => $margins,
        ];

        $this->setPdfConfig($showHeaderFooter);

        $pdf = PDF::loadView('pdf.company_policy', $pdfData);

        return $pdf->stream('Letter.pdf');
    }

    public function calculateOpeningBalance($date1, $date2, $id, $type)
    {
        $opening_balance = 0;
        if ($date1 == null) {
            $totalCredit = AccountEntry::where('account_entries.account_id', $id)
                ->where('account_entries.date', '<=', $date2)
                ->when($type && ($type != "" || $type != null), function ($query) use ($type) {
                    $query->where('type', $type);
                })
                ->where('is_debit', '=', 0)
                ->sum('amount');

            $totalDebit = AccountEntry::where('account_entries.account_id', $id)
                ->where('account_entries.date', '<=', $date2)
                ->when($type && ($type != "" || $type != null), function ($query) use ($type) {
                    $query->where('type', $type);
                })
                ->where('is_debit', '=', 1)
                ->sum('amount');

            $closing = $totalCredit - $totalDebit;
        } else {

            $totalCreditBefore = AccountEntry::where('account_entries.account_id', $id)
                ->where('account_entries.date', '<=', [$date1])
                ->when($type && ($type != "" || $type != null), function ($query) use ($type) {
                    $query->where('type', $type);
                })
                ->where('is_debit', '=', 0)
                ->sum('amount');

            $totalCredit = AccountEntry::where('account_entries.account_id', $id)
                ->whereBetween('account_entries.date', [$date1, $date2])
                ->when($type && ($type != "" || $type != null), function ($query) use ($type) {
                    $query->where('type', $type);
                })
                ->where('is_debit', '=', 0)
                ->sum('amount');

            $totalDebitBefore = AccountEntry::where('account_entries.account_id', $id)
                ->where('account_entries.date', '<=', [$date1])
                ->when($type && ($type != "" || $type != null), function ($query) use ($type) {
                    $query->where('type', $type);
                })
                ->where('is_debit', '=', 1)
                ->sum('amount');
            $totalDebit = AccountEntry::where('account_entries.account_id', $id)
                ->whereBetween('account_entries.date', [$date1, $date2])
                ->when($type && ($type != "" || $type != null), function ($query) use ($type) {
                    $query->where('type', $type);
                })
                ->where('is_debit', '=', 1)
                ->sum('amount');

            $opening_balance = $totalCreditBefore - $totalDebitBefore;
            $closing = $opening_balance + $totalCredit - $totalDebit;
        }



        return [
            'opening_balance' => $opening_balance,
            'closing' => $closing,
            'total_credit' => $totalCredit,
            'total_debit' => $totalDebit,
        ];
    }
}
