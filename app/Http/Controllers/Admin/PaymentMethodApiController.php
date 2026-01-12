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
 *  *  Last modified: 16/01/25, 9:33 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentMethodRequest;
use App\Http\Resources\Admin\PaymentMethodResource;
use App\Models\PaymentMethod;
use App\Services\DatabaseService;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class PaymentMethodApiController extends Controller
{
    protected $className = PaymentMethod::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = PaymentMethodResource::class;
    protected $fetcher = 'advancedFilter';
    protected $processListMethod = 'getProcessedList';
    protected $filterMethods = ['index', 'getCsv', 'getPdf'];
    protected $csvFilePrefix = 'payment-methods-list-';
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
        abort_if(Gate::denies('payment_method_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        return PaymentMethodResource::collection(PaymentMethod::advancedFilter());
    }

    /**
     * @throws \Exception
     */
    public function store(PaymentMethodRequest $request)
    {
        $obj = null;
        DatabaseService::executeTransaction(function () use ($request, &$obj) {
            $obj = PaymentMethod::query()->create($request->validated());
        });

        return (new PaymentMethodResource($obj))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('payment_method_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(PaymentMethod $paymentMethod)
    {
        abort_if(Gate::denies('payment_method_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new PaymentMethodResource($paymentMethod);
    }

    /**
     * @throws \Exception
     */
    public function update(PaymentMethodRequest $request, PaymentMethod $paymentMethod)
    {
        DatabaseService::executeTransaction(function () use ($request, $paymentMethod) {
            $paymentMethod->update($request->validated());
        });

        return (new PaymentMethodResource($paymentMethod))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        abort_if(Gate::denies('payment_method_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new PaymentMethodResource($paymentMethod),
            'meta' => [],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        abort_if(Gate::denies('payment_method_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        DatabaseService::executeTransaction(function () use ($paymentMethod) {
            $paymentMethod->delete();
        });

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
