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
 *  *  Last modified: 07/01/25, 5:06 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SalesInvoiceResource;
use App\Models\SalesInvoice;
use App\Models\Buyer;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SalesInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $query = SalesInvoice::with(['buyer', 'warehouse', 'company', 'user', 'agent', 'items.product']);

        // Apply filters
        if ($request->has('buyer_id')) {
            $query->where('buyer_id', $request->buyer_id);
        }

        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->has('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        if ($request->has('order_type')) {
            $query->where('order_type', $request->order_type);
        }

        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', '%' . $search . '%')
                  ->orWhere('reference_no', 'like', '%' . $search . '%')
                  ->orWhere('remark', 'like', '%' . $search . '%');
            });
        }

        // Apply advanced filtering if the trait exists
        if (method_exists(SalesInvoice::class, 'filter')) {
            $query = $query->filter($request->all());
        }

        $invoices = $query->paginate($request->get('per_page', 15));

        return SalesInvoiceResource::collection($invoices);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return SalesInvoiceResource
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_number' => 'required|string|max:255|unique:sales_invoices',
            'date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:date',
            'buyer_id' => 'required|exists:buyers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'company_id' => 'required|exists:companies,id',
            'user_id' => 'nullable|exists:users,id',
            'agent_id' => 'nullable|exists:suppliers,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'payment_term_id' => 'nullable|exists:payment_terms,id',
            'state_id' => 'nullable|exists:states,id',
            'type' => 'nullable|in:p,d',
            'order_type' => 'required|in:product,service,contract',
            'reference_no' => 'nullable|string|max:255',
            'remark' => 'nullable|string',
            'sub_total' => 'required|numeric|min:0',
            'tax_total' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'commission' => 'nullable|numeric|min:0',
            'commission_total' => 'nullable|numeric|min:0',
            'is_taxable' => 'boolean',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated) {
            $invoice = SalesInvoice::create([
                'invoice_number' => $validated['invoice_number'],
                'date' => $validated['date'],
                'due_date' => $validated['due_date'] ?? null,
                'buyer_id' => $validated['buyer_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'company_id' => $validated['company_id'],
                'user_id' => $validated['user_id'] ?? auth()->id(),
                'agent_id' => $validated['agent_id'] ?? null,
                'sales_order_id' => $validated['sales_order_id'] ?? null,
                'payment_term_id' => $validated['payment_term_id'] ?? null,
                'state_id' => $validated['state_id'] ?? null,
                'type' => $validated['type'] ?? 'p',
                'order_type' => $validated['order_type'],
                'reference_no' => $validated['reference_no'] ?? null,
                'remark' => $validated['remark'] ?? null,
                'sub_total' => $validated['sub_total'],
                'tax_total' => $validated['tax_total'],
                'tax_rate' => $validated['tax_rate'] ?? 0,
                'grand_total' => $validated['grand_total'],
                'commission' => $validated['commission'] ?? 0,
                'commission_total' => $validated['commission_total'] ?? 0,
                'is_taxable' => $validated['is_taxable'] ?? false,
            ]);

            // Create invoice items
            foreach ($validated['items'] as $item) {
                $invoice->items()->create([
                    'product_id' => $item['product_id'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'total' => $item['total'],
                ]);
            }

            return new SalesInvoiceResource($invoice->load(['buyer', 'warehouse', 'company', 'user', 'agent', 'items.product']));
        });
    }

    /**
     * Display the specified resource.
     *
     * @param SalesInvoice $salesInvoice
     * @return SalesInvoiceResource
     */
    public function show(SalesInvoice $salesInvoice)
    {
        return new SalesInvoiceResource($salesInvoice->load([
            'buyer',
            'warehouse',
            'company',
            'user',
            'agent',
            'salesOrder',
            'paymentTerm',
            'state',
            'items.product',
            'items.product.unit_01',
            'items.product.unit_02',
            'taxes',
            'payments'
        ]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param SalesInvoice $salesInvoice
     * @return SalesInvoiceResource
     */
    public function update(Request $request, SalesInvoice $salesInvoice)
    {
        $validated = $request->validate([
            'invoice_number' => 'sometimes|required|string|max:255|unique:sales_invoices,invoice_number,' . $salesInvoice->id,
            'date' => 'sometimes|required|date',
            'due_date' => 'nullable|date|after_or_equal:date',
            'buyer_id' => 'sometimes|required|exists:buyers,id',
            'warehouse_id' => 'sometimes|required|exists:warehouses,id',
            'company_id' => 'sometimes|required|exists:companies,id',
            'user_id' => 'nullable|exists:users,id',
            'agent_id' => 'nullable|exists:suppliers,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'payment_term_id' => 'nullable|exists:payment_terms,id',
            'state_id' => 'nullable|exists:states,id',
            'type' => 'nullable|in:p,d',
            'order_type' => 'sometimes|required|in:product,service,contract',
            'reference_no' => 'nullable|string|max:255',
            'remark' => 'nullable|string',
            'sub_total' => 'sometimes|required|numeric|min:0',
            'tax_total' => 'sometimes|required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'grand_total' => 'sometimes|required|numeric|min:0',
            'commission' => 'nullable|numeric|min:0',
            'commission_total' => 'nullable|numeric|min:0',
            'is_taxable' => 'boolean',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'items' => 'sometimes|required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated, $salesInvoice) {
            $salesInvoice->update([
                'invoice_number' => $validated['invoice_number'] ?? $salesInvoice->invoice_number,
                'date' => $validated['date'] ?? $salesInvoice->date,
                'due_date' => $validated['due_date'] ?? $salesInvoice->due_date,
                'buyer_id' => $validated['buyer_id'] ?? $salesInvoice->buyer_id,
                'warehouse_id' => $validated['warehouse_id'] ?? $salesInvoice->warehouse_id,
                'company_id' => $validated['company_id'] ?? $salesInvoice->company_id,
                'user_id' => $validated['user_id'] ?? $salesInvoice->user_id,
                'agent_id' => $validated['agent_id'] ?? $salesInvoice->agent_id,
                'sales_order_id' => $validated['sales_order_id'] ?? $salesInvoice->sales_order_id,
                'payment_term_id' => $validated['payment_term_id'] ?? $salesInvoice->payment_term_id,
                'state_id' => $validated['state_id'] ?? $salesInvoice->state_id,
                'type' => $validated['type'] ?? $salesInvoice->type,
                'order_type' => $validated['order_type'] ?? $salesInvoice->order_type,
                'reference_no' => $validated['reference_no'] ?? $salesInvoice->reference_no,
                'remark' => $validated['remark'] ?? $salesInvoice->remark,
                'sub_total' => $validated['sub_total'] ?? $salesInvoice->sub_total,
                'tax_total' => $validated['tax_total'] ?? $salesInvoice->tax_total,
                'tax_rate' => $validated['tax_rate'] ?? $salesInvoice->tax_rate,
                'grand_total' => $validated['grand_total'] ?? $salesInvoice->grand_total,
                'commission' => $validated['commission'] ?? $salesInvoice->commission,
                'commission_total' => $validated['commission_total'] ?? $salesInvoice->commission_total,
                'is_taxable' => $validated['is_taxable'] ?? $salesInvoice->is_taxable,
                'notes' => $validated['notes'] ?? $salesInvoice->notes,
                'terms_conditions' => $validated['terms_conditions'] ?? $salesInvoice->terms_conditions,
            ]);

            // Update items if provided
            if (isset($validated['items'])) {
                $salesInvoice->items()->delete();
                
                foreach ($validated['items'] as $item) {
                    $salesInvoice->items()->create([
                        'product_id' => $item['product_id'],
                        'description' => $item['description'] ?? null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount' => $item['discount'] ?? 0,
                        'tax_rate' => $item['tax_rate'] ?? 0,
                        'total' => $item['total'],
                    ]);
                }
            }

            return new SalesInvoiceResource($salesInvoice->load(['buyer', 'warehouse', 'company', 'user', 'agent', 'items.product']));
        });
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param SalesInvoice $salesInvoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(SalesInvoice $salesInvoice)
    {
        $salesInvoice->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get buyers for dropdown.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function buyers()
    {
        $buyers = Buyer::select('id', 'name', 'code')
            ->get()
            ->map(function ($buyer) {
                return [
                    'value' => $buyer->id,
                    'label' => "{$buyer->code} - {$buyer->name}",
                ];
            });

        return response()->json($buyers);
    }

    /**
     * Get agents for dropdown.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function agents()
    {
        $agents = Supplier::select('id', 'name', 'code')
            ->where('is_agent', true)
            ->get()
            ->map(function ($agent) {
                return [
                    'value' => $agent->id,
                    'label' => "{$agent->code} - {$agent->name}",
                ];
            });

        return response()->json($agents);
    }

    /**
     * Get warehouses for dropdown.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function warehouses()
    {
        $warehouses = Warehouse::select('id', 'name', 'code')
            ->get()
            ->map(function ($warehouse) {
                return [
                    'value' => $warehouse->id,
                    'label' => $warehouse->full_name ?? "{$warehouse->code} - {$warehouse->name}",
                ];
            });

        return response()->json($warehouses);
    }

    /**
     * Generate invoice number.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateInvoiceNumber()
    {
        $prefix = 'SI';
        $year = date('Y');
        $month = date('m');
        
        $latest = SalesInvoice::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $latest ? intval(substr($latest->invoice_number, -4)) + 1 : 1;
        $invoiceNumber = "{$prefix}-{$year}{$month}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);

        return response()->json(['invoice_number' => $invoiceNumber]);
    }
}