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
 *  *  Last modified: 22/01/25, 6:09 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\Admin\CompanyResource;
use App\Models\Company;
use App\Services\DatabaseService;
use App\Services\MediaService;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class CompanyApiController extends Controller
{
    protected $className = Company::class;

    protected $scopes = [];

    protected $with = ['warehouse:id,name'];

    protected $exportResource = CompanyResource::class;

    protected $fetcher = 'advancedFilter';

    protected $processListMethod = 'getProcessedList';

    protected $filterMethods = ['index', 'getCsv', 'getPdf'];

    protected $csvFilePrefix = 'companies-list-';

    protected $pdfFilePrefix = null;

    protected $fields = ['name'];

    protected $filters = [
        // ['request'=>'','field'=>'','operator'=>'in'],
    ];

    use ControllerRequest;
    use ExportRequest;
    use SearchFilters;

    public function index()
    {
        abort_if(Gate::denies('company_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        return CompanyResource::collection(Company::query()
            ->with($this->with)
            ->advancedFilter());
    }

    public function store(StoreCompanyRequest $request)
    {
        $obj = null;
        DatabaseService::executeTransaction(function () use ($request, &$obj) {
            $obj = Company::query()->create($request->validated());
            $this->updateRelations($obj, $request);
        });

        return (new CompanyResource($obj))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('company_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(Company $company)
    {
        abort_if(Gate::denies('company_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $company->load($this->with);

        return new CompanyResource($company);
    }

    public function update(UpdateCompanyRequest $request, Company $company)
    {
        DatabaseService::executeTransaction(function () use ($request, $company) {
            $company->update($request->validated());
            $this->updateRelations($company, $request);
        });

        return (new CompanyResource($company))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(Company $company)
    {
        abort_if(Gate::denies('company_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $company->load($this->with);

        return response([
            'data' => new CompanyResource($company),
            'meta' => [],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function destroy(Company $company)
    {
        abort_if(Gate::denies('company_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if(in_array($company->id, config('system.restricted.company', [])), Response::HTTP_FORBIDDEN, "You can't delete this company");

        DatabaseService::executeTransaction(function () use ($company) {
            $company->delete();
        });

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function updateRelations($obj, $request)
    {
        MediaService::updateRelations($obj, $request->input('image', []), 'company_image');
    }
}
