<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiBaseController;
use App\Models\LoanDeduction;
use App\Http\Requests\Api\User\IndexRequest;

class LoanDeductionController extends ApiBaseController
{
    protected $model = LoanDeduction::class;
    protected $indexRequest = IndexRequest::class;

    public function index(){
        try {
            $request = request();
            $limit = $request->limit; // default to 10 if not provided
            $data = LoanDeduction::with(['user'])->orderBy('loan_id', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [ 'paging' => ['total' => 10] ] 
            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
