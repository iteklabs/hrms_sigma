<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\CompanyPolicy\IndexRequest;
use App\Http\Requests\Api\CompanyPolicy\StoreRequest;
use App\Http\Requests\Api\CompanyPolicy\UpdateRequest;
use App\Http\Requests\Api\CompanyPolicy\DeleteRequest;
use App\Models\CompanyPolicy;

class CompanyPolicyController extends ApiBaseController
{
	protected $model = CompanyPolicy::class;
	protected $indexRequest = IndexRequest::class;
	protected $storeRequest = StoreRequest::class;
	protected $updateRequest = UpdateRequest::class;
	protected $deleteRequest = DeleteRequest::class;

    protected function modifyIndex($query)
	{
		$request = request();

            if ($request->has('location_id') && ($request->location_id != "" || $request->location_id != null)) {
                $locationId = $this->getIdFromHash($request->location_id);
                $query = $query->where('company_policies.location_id', $locationId);
            };

		return  $query;
	}
}