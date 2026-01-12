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
 *  *  Last modified: 17/10/24, 6:37 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ContractInvoiceListResourceCollection;
use App\Http\Resources\Admin\ContractInvoiceResource;
use App\Models\Contract;
use App\Models\SalesInvoice;
use App\Services\DatabaseService;
use App\Services\InvoiceService;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class ContractInvoiceApiController extends Controller
{
    protected $className = SalesInvoice::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = ContractInvoiceResource::class;
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

    public function index(Contract $contract)
    {
        abort_if(Gate::denies('sales_invoice_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList($contract);
    }

    public function getList($contract)
    {
        $contract->load(['revision']);

        return new ContractInvoiceListResourceCollection(SalesInvoice::query()
            ->with([
                'buyer:id,name',
                //'payments:id,invoice_id,amount,date'
            ])
            ->where('contract_revision_id', $contract->revision->id)
            ->advancedFilter(), $contract);
    }

    /**
     * @throws \Exception
     */
    public function generate(Contract $contract)
    {
        $contract->load(['revision']);
        $revision = $contract->revision;
        $contractType = $revision->contract_type ? $revision->contract_type['value'] : 'default';

        abort_if($contractType != 'default', Response::HTTP_FORBIDDEN, 'Invoice generation is only allowed for manual contract type');

        $invoice = null;
        DatabaseService::executeTransaction(function () use ($contract, $revision, &$invoice) {
            $invoice = InvoiceService::generateInvoiceFromContract($contract);
        });

        return (new ContractInvoiceResource($invoice))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function destroy(SalesInvoice $salesInvoice)
    {
        abort_if(Gate::denies('sales_invoice_delete'), Response::HTTP_FORBIDDEN, 'You are not authorized to delete this record');

        abort_if(!$salesInvoice->id, Response::HTTP_NOT_FOUND, 'Requested record was not found');

        DatabaseService::executeTransaction(function () use ($salesInvoice) {
            //$salesInvoice->payments()->delete();
            $salesInvoice->items()->delete();
            $salesInvoice->delete();
        });

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function updateRelatives($request, $obj)
    {
        $this->updateItems($request, $obj);
        InvoiceService::updateTotals($obj);
    }

    private function updateItems($request, $obj)
    {
        $this->updateChild($request, $obj, 'items', InvoiceItem::class, 'items', 'invoice_id');
    }
}
