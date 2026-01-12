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
 *  *  Last modified: 10/02/25, 7:22 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceInvoiceRequest;
use App\Http\Requests\UpdateServiceInvoiceRequest;
use App\Http\Resources\Admin\SalesInvoiceListResourceCollection;
use App\Http\Resources\Admin\ServiceInvoiceResource;
use App\Models\PaymentTerm;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Services\CanadaTaxService;
use App\Services\CompanyService;
use App\Services\ControllerService;
use App\Services\DatabaseService;
use App\Services\InvoiceService;
use App\Services\OrderService;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class ServiceInvoiceApiController extends Controller
{
    protected $className = SalesInvoice::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = ServiceInvoiceResource::class;
    protected $fetcher = 'advancedFilter';
    protected $processListMethod = 'getProcessedList';
    protected $filterMethods = ['index', 'getCsv', 'getPdf'];
    protected $csvFilePrefix = 'service-invoices-list-';
    protected $pdfFilePrefix = null;
    protected $fields = ['name'];
    protected $filters = [
        ['request' => 'f_date_range', 'field' => 'date', 'operator' => 'date_range', 'separator' => ' to '],
        ['request' => 'f_current_company_only', 'field' => 'company', 'operator' => 'scope'],
    ];

    use SearchFilters;
    use ControllerRequest;
    use ExportRequest;

    public function index()
    {
        abort_if(Gate::denies('service_invoice_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        return new SalesInvoiceListResourceCollection(SalesInvoice::query()
            ->whereIn('order_type', ['contract', 'service'])
            ->with([
                'company:id,name',
                'paymentTerm:id,name',
                'buyer:id,display_name',
                'user:id,name',
            ])
            ->advancedFilter());
    }

    /**
     * @throws \Exception
     */
    public function store(StoreServiceInvoiceRequest $request)
    {
        $obj = null;
        DatabaseService::executeTransaction(function () use ($request, &$obj) {
            $obj = SalesInvoice::query()->create($request->validated());
            $this->updateRelatives($request, $obj);
        });

        return (new ServiceInvoiceResource($obj))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('service_invoice_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $defaultPaymentTermId = config('system.defaults.payment_term_id', 3);
        $paymentTerm = PaymentTerm::query()
            ->select('id', 'name')
            ->find($defaultPaymentTermId);

        $defaults = [
            'date'         => Carbon::now()->format(config('project.date_format')),
            'company'      => CompanyService::getDefaultCompanyEntry(),
            'payment_term' => $paymentTerm,
            'state'        => CanadaTaxService::getDefaultStateObject(),
        ];

        return response([
            'meta'     => [],
            'defaults' => $defaults,
        ]);
    }

    public function show(SalesInvoice $salesInvoice)
    {
        abort_if(Gate::denies('service_invoice_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ServiceInvoiceResource($salesInvoice->load([
            'items',
            'taxes:id,amount,tax_rate_id,taxable_type,taxable_id',
            'taxes.taxRate:id,name,rate',
        ]));
    }

    public function update(UpdateServiceInvoiceRequest $request, SalesInvoice $salesInvoice)
    {
        DatabaseService::executeTransaction(function () use ($request, $salesInvoice) {
            $salesInvoice->update($request->validated());
            $this->updateRelatives($request, $salesInvoice);
        });

        return (new ServiceInvoiceResource($salesInvoice))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(SalesInvoice $salesInvoice)
    {
        abort_if(Gate::denies('service_invoice_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new ServiceInvoiceResource($salesInvoice->load([
                'company:id,name',
                'state:id,name',
                'paymentTerm:id,name',
                'buyer:id,display_name',
                'items.product:id,name',
                'taxes',
            ])),
            'meta' => [],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function destroy(SalesInvoice $salesInvoice)
    {
        abort_if(Gate::denies('service_invoice_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        DatabaseService::executeTransaction(function () use ($salesInvoice) {

            $salesInvoice->items()->delete();
            $salesInvoice->delete();
        });

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function updateRelatives($request, $obj)
    {
        $this->updateItems($request, $obj);
        $this->updateTaxes($request, $obj);
        $this->updateTotals($obj);
    }

    private function updateTaxes($request, $obj)
    {
        InvoiceService::updateTaxes($request, $obj, SalesInvoice::class);
    }

    private function updateItems($request, $obj)
    {
        $items = InvoiceService::getItemsObject($request);
        $request->merge(['items' => $items]);
        ControllerService::updateChild($request, $obj, 'items', SalesInvoiceItem::class, 'items', 'sales_invoice_id');
    }

    private function updateTotals($obj)
    {
        OrderService::updateSalesOrderTotals($obj);
    }
}
