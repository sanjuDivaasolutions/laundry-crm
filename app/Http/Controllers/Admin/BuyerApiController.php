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
 *  *  Last modified: 15/01/25, 2:26 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBuyerRequest;
use App\Http\Requests\UpdateBuyerRequest;
use App\Http\Resources\Admin\BuyerResource;
use App\Models\Buyer;
use App\Models\ContactAddress;
use App\Services\ContactService;
use App\Services\DatabaseService;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class BuyerApiController extends Controller
{
    protected $className = Buyer::class;
    protected $scopes = [];
    protected $with = [];
    protected $fetcher = 'advancedFilter';
    protected $filterMethods = ['index', 'getCsv', 'getPdf'];
    protected $csvFilePrefix = 'buyers-list-';
    protected $fields = ['name', 'display_name'];
    protected $filters = [
    ];

    use SearchFilters;
    use ControllerRequest;
    use ExportRequest;

    public function index()
    {
        abort_if(Gate::denies('buyer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        return BuyerResource::collection(Buyer::query()
            ->with([
                'paymentTerm:id,name',
                'agent:id,name,display_name',
            ])
            ->advancedFilter());
    }

    public function store(StoreBuyerRequest $request)
    {
        $buyer = Buyer::create($request->validated());

        $this->updateRelatives($request, $buyer);

        $buyer->load([
            'paymentTerm:id,name',
            'billingAddress',
            'shippingAddress',
            'agent:id,name,display_name',
        ]);

        return (new BuyerResource($buyer))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('buyer_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(Buyer $buyer)
    {
        abort_if(Gate::denies('buyer_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new BuyerResource($buyer->load([
            'paymentTerm:id,name',
            'billingAddress',
            'shippingAddress',
            'agent:id,name,display_name',
        ]));
    }

    public function update(UpdateBuyerRequest $request, Buyer $buyer)
    {
        $buyer->update($request->validated());

        $this->updateRelatives($request, $buyer);

        $buyer->load([
            'paymentTerm:id,name',
            'billingAddress',
            'shippingAddress',
            'agent:id,name,display_name',
        ]);

        return (new BuyerResource($buyer))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(Buyer $buyer)
    {
        abort_if(Gate::denies('buyer_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new BuyerResource($buyer->load([
                'paymentTerm:id,name',
                'billingAddress',
                'billingAddress.country:id,name',
                'billingAddress.state:id,name',
                'billingAddress.city:id,name',
                'shippingAddress',
                'shippingAddress.country:id,name',
                'shippingAddress.state:id,name',
                'shippingAddress.city:id,name',
                'currency:id,name',
                'agent:id,name,display_name',
            ])),
            'meta' => [],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function destroy(Buyer $buyer)
    {
        abort_if(Gate::denies('buyer_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if(ContactService::isBuyerUsed($buyer), Response::HTTP_FORBIDDEN, 'This buyer is already used in sales order or sales invoice');

        DatabaseService::executeTransaction(function () use ($buyer) {
            ContactService::removeContact($buyer);
        });

        return response(null, Response::HTTP_NO_CONTENT);
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
