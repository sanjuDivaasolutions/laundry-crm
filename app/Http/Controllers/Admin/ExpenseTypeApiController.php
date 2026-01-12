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
 *  *  Last modified: 16/01/25, 9:12 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenseTypeRequest;
use App\Http\Resources\Admin\ExpenseTypeResource;
use App\Models\ExpenseType;
use App\Services\DatabaseService;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class ExpenseTypeApiController extends Controller
{
    protected $className = ExpenseType::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = ExpenseTypeResource::class;
    protected $fetcher = 'advancedFilter';
    protected $processListMethod = 'getProcessedList';
    protected $filterMethods = ['index', 'getCsv', 'getPdf'];
    protected $csvFilePrefix = 'expense-types-list-';
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
        abort_if(Gate::denies('expense_type_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        return ExpenseTypeResource::collection(ExpenseType::advancedFilter());
    }

    /**
     * @throws \Exception
     */
    public function store(ExpenseTypeRequest $request)
    {
        $obj = null;
        DatabaseService::executeTransaction(function () use ($request, &$obj) {
            $obj = ExpenseType::query()->create($request->validated());
        });

        return (new ExpenseTypeResource($obj))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('expense_type_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(ExpenseType $expenseType)
    {
        abort_if(Gate::denies('expense_type_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ExpenseTypeResource($expenseType);
    }

    /**
     * @throws \Exception
     */
    public function update(ExpenseTypeRequest $request, ExpenseType $expenseType)
    {
        DatabaseService::executeTransaction(function () use ($request, $expenseType) {
            $expenseType->update($request->validated());
        });

        return (new ExpenseTypeResource($expenseType))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(ExpenseType $expenseType)
    {
        abort_if(Gate::denies('expense_type_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new ExpenseTypeResource($expenseType),
            'meta' => [],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function destroy(ExpenseType $expenseType)
    {
        abort_if(Gate::denies('expense_type_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        DatabaseService::executeTransaction(function () use ($expenseType) {
            $expenseType->delete();
        });

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
