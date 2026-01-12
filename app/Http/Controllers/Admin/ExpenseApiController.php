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
 *  *  Last modified: 29/01/25, 10:52 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenseRequest;
use App\Http\Resources\Admin\ExpenseListResourceCollection;
use App\Http\Resources\Admin\ExpenseResource;
use App\Models\Expense;
use App\Services\CanadaTaxService;
use App\Services\CompanyService;
use App\Services\DatabaseService;
use App\Services\InvoiceService;
use App\Services\MediaService;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class ExpenseApiController extends Controller
{
    protected $className = Expense::class;
    protected $scopes = [];
    protected $with = ['expenseType:id,name', 'paymentMode:id,name', 'company:id,name', 'state:id,name', 'user:id,name'];
    protected $exportResource = ExpenseResource::class;
    protected $fetcher = 'advancedFilter';
    protected $processListMethod = 'getProcessedList';
    protected $filterMethods = ['index', 'getCsv', 'getPdf'];
    protected $csvFilePrefix = 'expenses-list-';
    protected $pdfFilePrefix = null;
    protected $fields = ['invoice_number', 'description'];
    protected $filters = [
        ['request' => 'f_date_range', 'field' => 'date', 'operator' => 'date_range', 'separator' => ' to '],
        ['request' => 'f_current_company_only', 'field' => 'company', 'operator' => 'scope'],
        ['request' => 'f_expense_type', 'field' => 'expense_type_id', 'operator' => 'in'],
    ];

    use SearchFilters;
    use ControllerRequest;
    use ExportRequest;

    public function index()
    {
        abort_if(Gate::denies('expense_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        return new ExpenseListResourceCollection(Expense::query()
            ->with($this->with)
            ->advancedFilter());
    }

    /**
     * @throws \Exception
     */
    public function store(ExpenseRequest $request)
    {
        $obj = null;
        DatabaseService::executeTransaction(function () use ($request, &$obj) {
            $obj = Expense::query()->create($request->validated());
            $this->updateRelations($obj, $request);
        });

        return (new ExpenseResource($obj))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('expense_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');


        $defaults = [
            'date'    => Carbon::now()->format(config('project.date_format')),
            'company' => CompanyService::getDefaultCompanyEntry(),
            'state'   => CanadaTaxService::getDefaultStateObject(),
        ];

        return response([
            'meta'     => [],
            'defaults' => $defaults,
        ]);
    }

    public function show(Expense $expense)
    {
        abort_if(Gate::denies('expense_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $expense->load($this->with);

        return new ExpenseResource($expense);
    }

    /**
     * @throws \Exception
     */
    public function update(ExpenseRequest $request, Expense $expense)
    {
        DatabaseService::executeTransaction(function () use ($request, $expense) {
            $expense->update($request->validated());
            $this->updateRelations($expense, $request);
        });

        return (new ExpenseResource($expense))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(Expense $expense)
    {
        abort_if(Gate::denies('expense_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $expense->load($this->with);

        $expense->append('attachment');

        return response([
            'data' => new ExpenseResource($expense->load($this->with)),
            'meta' => [],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function destroy(Expense $expense)
    {
        abort_if(Gate::denies('expense_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        DatabaseService::executeTransaction(function () use ($expense) {
            $expense->delete();
        });

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function updateRelations($obj, $request)
    {
        MediaService::updateRelations($obj, $request->input('attachment', []), 'expense_attachment');
        $this->updateTaxes($obj);
    }

    private function updateTaxes($obj)
    {
        InvoiceService::setupTaxes($obj);
    }
}
