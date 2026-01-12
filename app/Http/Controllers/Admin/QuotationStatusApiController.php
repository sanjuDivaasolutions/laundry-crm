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
 *  *  Last modified: 21/01/25, 10:14 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuotationStatusRequest;
use App\Http\Resources\Admin\QuotationStatusResource;
use App\Models\QuotationStatus;

class QuotationStatusApiController extends Controller
{
    public function index()
    {
        return QuotationStatusResource::collection(QuotationStatus::all());
    }

    public function store(QuotationStatusRequest $request)
    {
        return new QuotationStatusResource(QuotationStatus::create($request->validated()));
    }

    public function show(QuotationStatus $quotationStatus)
    {
        return new QuotationStatusResource($quotationStatus);
    }

    public function update(QuotationStatusRequest $request, QuotationStatus $quotationStatus)
    {
        $quotationStatus->update($request->validated());

        return new QuotationStatusResource($quotationStatus);
    }

    public function destroy(QuotationStatus $quotationStatus)
    {
        $quotationStatus->delete();

        return response()->json();
    }
}
