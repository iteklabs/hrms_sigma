<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\User\IndexRequest;
use App\Models\SalaryAdjustment;

class SalaryAdjustmentController extends ApiBaseController
{
    protected $model = SalaryAdjustment::class;
    protected $indexRequest = IndexRequest::class;

    public function index(){
        try {
            $request = request();
            $limit = $request->limit; // default to 10 if not provided
            $data = SalaryAdjustment::orderBy('id', 'desc')
                ->limit($limit)
                ->get();
            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [ 'paging' => ['total' => 10] ] 
            ]);
            
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        
    }

    // public function stored(SalaryAdjustment $SalaryAdjustment){
    //     $request = request();
    //     echo "<pre>";
    //    print_r($SalaryAdjustment->find($request->id));
    //    exit;
    // }
}
