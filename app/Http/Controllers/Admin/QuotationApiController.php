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
 *  *  Last modified: 21/01/25, 6:18 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConvertToSalesOrderRequest;
use App\Http\Requests\QuotationRequest;
use App\Http\Requests\StoreQuotationRequest;
use App\Http\Requests\UpdateQuotationRequest;
use App\Http\Resources\Admin\QuotationListResourceCollection;
use App\Http\Resources\Admin\QuotationResource;
use App\Http\Resources\Admin\SalesOrderResource;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesOrder;
use App\Services\CompanyService;
use App\Services\ControllerService;
use App\Services\DatabaseService;
use App\Services\QuotationService;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Barryvdh\DomPDF\Facade\Pdf;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class QuotationApiController extends Controller
{
    protected $className = Quotation::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = QuotationResource::class;
    protected $fetcher = 'advancedFilter';
    protected $processListMethod = 'getProcessedList';
    protected $filterMethods = ['index', 'getCsv', 'getPdf'];
    protected $csvFilePrefix = 'quotations-list-';
    protected $pdfFilePrefix = null;
    protected $fields = ['order_no', 'reference_no', 'remark'];
    protected $filters = [
        ['request' => 'f_date_range', 'field' => 'date', 'operator' => 'date_range', 'separator' => ' to '],
        ['request' => 'f_current_company_only', 'field' => 'company', 'operator' => 'scope'],
    ];

    use SearchFilters;
    use ControllerRequest;
    use ExportRequest;

    public function index()
    {
        abort_if(Gate::denies('quotation_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        return new QuotationListResourceCollection(Quotation::query()
            ->with([
                'buyer:id,display_name',
                'user:id,name',
            ])
            ->advancedFilter());
    }

    /**
     * @throws \Exception
     */
    public function store(QuotationRequest $request)
    {
        $obj = null;
        DatabaseService::executeTransaction(function () use ($request, &$obj) {
            $obj = Quotation::query()->create($request->validated());
            $this->updateRelatives($request, $obj);
            QuotationService::setStatus($obj, 'draft', Carbon::now()->format(config('project.date_format')), 'Quotation created');
        });

        return (new QuotationResource($obj))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('quotation_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $defaults = [
            'date'    => Carbon::now()->format(config('project.date_format')),
            'company' => CompanyService::getDefaultCompanyEntry(),
        ];

        return response([
            'meta'     => [],
            'defaults' => $defaults,
        ]);
    }

    public function show(Quotation $quotation)
    {
        abort_if(Gate::denies('quotation_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $quotation->load([
            'company:id,name',
            'buyer:id,name,billing_address_id,shipping_address_id',
            'buyer.billingAddress:id,name,address_1,address_2,city_id,state_id,postal_code,phone',
            'buyer.billingAddress.state:id,name',
            'buyer.billingAddress.city:id,name',
            'items.product:id,name,sku',
            'items.unit:id,name',
            'status:id,quotation_id,status,date,remark',
        ]);

        return new QuotationResource($quotation);
    }

    /**
     * @throws \Exception
     */
    public function update(QuotationRequest $request, Quotation $quotation)
    {
        DatabaseService::executeTransaction(function () use ($request, $quotation) {
            $quotation->update($request->validated());
            $this->updateRelatives($request, $quotation);
        });

        return (new QuotationResource($quotation))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(Quotation $quotation)
    {
        abort_if(Gate::denies('quotation_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $quotation->load([
            'company:id,name',
            'buyer:id,display_name',
            'items:id,quotation_id,unit_id,product_id,rate,quantity,amount',
            'items.product:id,name,sku',
            'items.unit:id,name',
        ]);

        return response([
            'data' => new QuotationResource($quotation),
            'meta' => [],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function destroy(Quotation $quotation)
    {
        abort_if(Gate::denies('quotation_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        DatabaseService::executeTransaction(function () use ($quotation) {
            $quotation->items()->delete();
            $quotation->statuses()->delete();
            $quotation->delete();
        });

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function updateRelatives($request, $obj)
    {
        $this->updateItems($request, $obj);
        $this->updateTotals($obj);
    }

    private function updateTotals($obj)
    {
        QuotationService::updateTotals($obj);
    }

    private function updateItems($request, $obj)
    {
        $items = QuotationService::getItemsObject($request);
        $request->merge(['items' => $items]);
        ControllerService::updateChild($request, $obj, 'items', QuotationItem::class, 'items', 'quotation_id');
    }

    public function getSinglePdf(Quotation $quotation)
    {
        $quotation->load([
            'buyer:id,name,billing_address_id,shipping_address_id',
            'buyer.billingAddress:id,name,address_1,address_2,city_id,state_id,postal_code,phone',
            'buyer.billingAddress.city:id,name',
            'items.product:id,name,sku',
            'items.unit:id,name',
        ]);
        $code = $quotation->order_no;
        $fileName = "quotation-$code.pdf";
        $compact = [
            'obj' => $quotation,
        ];

        $pdf = Pdf::loadView('pdf-templates.quotation.quotation', $compact);
        return $pdf->download($fileName);
    }

    public function markStatus(Quotation $quotation, Request $request)
    {
        $status = $request->input('status');
        $remark = $request->input('remark');
        $date = $request->input('date', Carbon::now()->format(config('project.date_format')));
        QuotationService::setStatus($quotation, $status, $date, $remark);

        return response('Quotation status was updated successfully', Response::HTTP_CREATED);
    }

    /**
     * Convert quotation to sales order
     */
    public function convertToSalesOrder(ConvertToSalesOrderRequest $request, Quotation $quotation)
    {
        abort_if(Gate::denies('quotation_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        abort_if(Gate::denies('sales_order_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Check if quotation is already converted
        if ($quotation->status && $quotation->status->status === 'converted') {
            return response()->json([
                'message' => 'This quotation has already been converted to a sales order',
                'errors' => ['quotation' => ['Quotation already converted']]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $salesOrder = $quotation->convertToSalesOrder($request->validated());

        return response()->json([
            'message' => 'Quotation successfully converted to sales order',
            'data' => [
                'sales_order_id' => $salesOrder->id,
                'so_number' => $salesOrder->so_number,
                'quotation_id' => $quotation->id,
                'quotation_no' => $quotation->order_no,
            ]
        ], Response::HTTP_CREATED);
    }
}
