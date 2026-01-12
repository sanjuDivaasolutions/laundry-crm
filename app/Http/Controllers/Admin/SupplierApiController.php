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
 *  *  Last modified: 15/01/25, 2:23 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Http\Resources\Admin\SupplierResource;
use App\Models\ContactAddress;
use App\Models\Supplier;
use App\Services\ContactService;
use App\Services\DatabaseService;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class SupplierApiController extends Controller
{
    protected $className = Supplier::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = SupplierResource::class;
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
        abort_if(Gate::denies('supplier_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        $query = Supplier::query();
        $isAgentRoute = $this->isAgentRoute();
        if ($isAgentRoute) {
            $query->where('is_agent', true);
        } else {
            $query->where(function ($q) {
                $q->whereNull('is_agent')
                    ->orWhere('is_agent', false);
            });
        }

        return SupplierResource::collection($query->advancedFilter());
    }

    public function store(StoreSupplierRequest $request)
    {
        $data = $request->validated();
        if ($this->isAgentRoute()) {
            $data['is_agent'] = true;
        } elseif (!array_key_exists('is_agent', $data)) {
            $data['is_agent'] = false;
        }

        $supplier = Supplier::create($data);
        $this->updateRelatives($request, $supplier);
        return (new SupplierResource($supplier))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('supplier_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(Supplier $supplier)
    {
        abort_if(Gate::denies('supplier_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->ensureCorrectContext($supplier);

        return new SupplierResource($supplier->load(['paymentTerm:id,name', 'billingAddress', 'shippingAddress']));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $this->ensureCorrectContext($supplier);

        $data = $request->validated();
        if ($this->isAgentRoute()) {
            $data['is_agent'] = true;
        } elseif (!array_key_exists('is_agent', $data)) {
            $data['is_agent'] = (bool) $supplier->is_agent;
        }

        $supplier->update($data);
        $this->updateRelatives($request, $supplier);
        return (new SupplierResource($supplier))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(Supplier $supplier)
    {
        abort_if(Gate::denies('supplier_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->ensureCorrectContext($supplier);

        return response([
            'data' => new SupplierResource($supplier->load([
                'paymentTerm:id,name',
                'billingAddress',
                'shippingAddress',
                'currency:id,name'
            ])),
            'meta' => [],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function destroy(Supplier $supplier)
    {
        abort_if(Gate::denies('supplier_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->ensureCorrectContext($supplier);

        abort_if(ContactService::isSupplierUsed($supplier), Response::HTTP_FORBIDDEN, 'This supplier is already used in purchase order or purchase invoice');

        DatabaseService::executeTransaction(function () use ($supplier) {
            ContactService::removeContact($supplier);
        });

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function isAgentRoute(): bool
    {
        return request()->routeIs('api.agents.*') || request()->routeIs('api.agents-csv');
    }

    private function ensureCorrectContext(Supplier $supplier): void
    {
        $isAgentRoute = $this->isAgentRoute();
        if ($isAgentRoute && !$supplier->is_agent) {
            abort(Response::HTTP_NOT_FOUND);
        }
        if (!$isAgentRoute && $supplier->is_agent) {
            abort(Response::HTTP_NOT_FOUND);
        }
    }

    private function updateRelatives($request, $obj)
    {
        $billingAddress = $request->post('billing_address', []);
        if (!is_array($billingAddress)) {
            $billingAddress = [];
        }

        $this->updateAddress($billingAddress, $obj, 'billing');

        $shippingAddress = $request->post('shipping_address', []);
        if (!is_array($shippingAddress)) {
            $shippingAddress = [];
        }

        if ($request->boolean('shipping_same_as_billing')) {
            $shippingAddress = $billingAddress;
            if ($obj->billing_address_id) {
                $shippingAddress['id'] = $obj->billing_address_id;
            }
        }

        $this->updateAddress($shippingAddress, $obj, 'shipping');
    }

    private function updateAddress($field, $obj, $type)
    {
        $a = null;
        if (isset($field['id']) && $field['id']) {
            $a = ContactAddress::find($field['id']);
        }
        if (!$a) {
            $a = new ContactAddress();
        }
        if (isset($field['city']) && $field['city']) {
            $a->{'city_id'} = $field['city']['id'];
        }
        if (isset($field['state']) && $field['state']) {
            $a->{'state_id'} = $field['state']['id'];
        }
        if (isset($field['country']) && $field['country']) {
            $a->{'country_id'} = $field['country']['id'];
        }
        $a->fill($field);
        $a->save();

        $obj->{$type . '_address_id'} = $a->id;
        $obj->save();
        return true;
    }
}
