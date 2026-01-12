<?php
/*
 *
 *  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 05/02/25, 7:30 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Http\Resources\Admin\ContractEditResource;
use App\Http\Resources\Admin\ContractListResource;
use App\Http\Resources\Admin\ContractResource;
use App\Http\Resources\Admin\ContractShowResource;
use App\Models\Contract;
use App\Services\CompanyService;
use App\Services\ContractService;
use App\Services\DatabaseService;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class ContractApiController extends Controller
{
    protected $className = Contract::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = ContractResource::class;
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
        abort_if(Gate::denies('contract_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        return ContractListResource::collection(Contract::query()
            ->with([
                'revision',
                'buyer:id,name,display_name',
            ])
            ->advancedFilter());
    }

    /**
     * @throws \Exception
     */
    public function store(StoreContractRequest $request)
    {
        $obj = null;
        DatabaseService::executeTransaction(function () use ($request, &$obj) {
            $obj = ContractService::create($request);
        });

        return (new ContractResource($obj))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('contract_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $defaults = [
            'date'    => now()->format(config('project.date_format')),
            'company' => CompanyService::getDefaultCompanyEntry(),
        ];

        return response([
            'meta'     => [],
            'defaults' => $defaults,
        ]);
    }

    public function show(Contract $contract)
    {
        abort_if(Gate::denies('contract_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ContractShowResource($contract->load([
            'buyer:id,display_name',
            'revision.subscription',
            'items:id,contract_id,product_id,description,remark,amount',
            'items.product:id,name',
        ]));
    }

    /**
     * @throws \Exception
     */
    public function update(UpdateContractRequest $request, Contract $contract)
    {
        DatabaseService::executeTransaction(function () use ($request, $contract) {
            ContractService::update($request, $contract);
        });

        return (new ContractResource($contract))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(Contract $contract)
    {
        abort_if(Gate::denies('contract_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new ContractEditResource($contract->load([
                'buyer:id,name,display_name',
                'revision',
                'term',
                'items.product:id,name',
            ])),
            'meta' => [],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function destroy(Contract $contract)
    {
        abort_if(Gate::denies('contract_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        DatabaseService::executeTransaction(function () use ($contract) {
            ContractService::removeContract($contract);
        });

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function sendPaymentLink(Contract $contract)
    {
        ContractService::sendPaymentLink($contract);

        return okResponse('Payment link sent successfully');
    }

}
