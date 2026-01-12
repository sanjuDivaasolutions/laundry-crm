<?php
/*
 *
 *  *  Copyright (c) 2024 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 02/07/24, 6:07 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContractTermRequest;
use App\Http\Requests\UpdateContractTermRequest;
use App\Http\Resources\Admin\ContractTermResource;
use App\Models\ContractTerm;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class ContractTermApiController extends Controller
{
    protected $className = ContractTerm::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = ContractTermResource::class;
    protected $fetcher = 'advancedFilter';
    protected $processListMethod = 'getProcessedList';
    protected $filterMethods = ['index', 'getCsv', 'getPdf'];
    protected $csvFilePrefix = null;
    protected $pdfFilePrefix = null;
    protected $fields = ['name'];
    protected $filters = [
        //['request'=>'','field'=>'','operator'=>'in'],
    ];

    use SearchFilters;
    use ControllerRequest;
    use ExportRequest;

    public function index()
    {
        abort_if(Gate::denies('contract_term_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        return ContractTermResource::collection(ContractTerm::advancedFilter());
    }

    public function store(StoreContractTermRequest $request)
    {
        $contractTerm = ContractTerm::create($request->validated());

        return (new ContractTermResource($contractTerm))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('contract_term_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(ContractTerm $contractTerm)
    {
        abort_if(Gate::denies('contract_term_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ContractTermResource($contractTerm);
    }

    public function update(UpdateContractTermRequest $request, ContractTerm $contractTerm)
    {
        $contractTerm->update($request->validated());

        return (new ContractTermResource($contractTerm))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(ContractTerm $contractTerm)
    {
        abort_if(Gate::denies('contract_term_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new ContractTermResource($contractTerm),
            'meta' => [],
        ]);
    }

    public function destroy(ContractTerm $contractTerm)
    {
        abort_if(Gate::denies('contract_term_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $contractTerm->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
