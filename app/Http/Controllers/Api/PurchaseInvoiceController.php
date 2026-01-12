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
use App\Http\Resources\PurchaseInvoiceResource;
use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $query = PurchaseInvoice::with(['supplier', 'warehouse', 'company', 'user', 'items.product']);

        // Apply filters
        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
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
                $q->where('invoice_number', 'like', '%'.$search.'%')
                    ->orWhere('reference_no', 'like', '%'.$search.'%')
                    ->orWhere('remark', 'like', '%'.$search.'%');
            });
        }

        // Apply advanced filtering if the trait exists
        if (method_exists(PurchaseInvoice::class, 'filter')) {
            $query = $query->filter($request->all());
        }

        $invoices = $query->paginate($request->get('per_page', 15));

        return PurchaseInvoiceResource::collection($invoices);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return PurchaseInvoiceResource
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_number' => 'required|string|max:255|unique:purchase_invoices',
            'date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:date',
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'company_id' => 'required|exists:companies,id',
            'user_id' => 'nullable|exists:users,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'type' => 'nullable|in:p,d',
            'reference_no' => 'nullable|string|max:255',
            'remark' => 'nullable|string',
            'sub_total' => 'required|numeric|min:0',
            'tax_total' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
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
            $invoice = PurchaseInvoice::create([
                'invoice_number' => $validated['invoice_number'],
                'date' => $validated['date'],
                'due_date' => $validated['due_date'] ?? null,
                'supplier_id' => $validated['supplier_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'company_id' => $validated['company_id'],
                'user_id' => $validated['user_id'] ?? auth()->id(),
                'purchase_order_id' => $validated['purchase_order_id'] ?? null,
                'type' => $validated['type'] ?? 'p',
                'reference_no' => $validated['reference_no'] ?? null,
                'remark' => $validated['remark'] ?? null,
                'sub_total' => $validated['sub_total'],
                'tax_total' => $validated['tax_total'],
                'tax_rate' => $validated['tax_rate'] ?? 0,
                'grand_total' => $validated['grand_total'],
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

            return new PurchaseInvoiceResource($invoice->load(['supplier', 'warehouse', 'company', 'user', 'items.product']));
        });
    }

    /**
     * Display the specified resource.
     *
     * @return PurchaseInvoiceResource
     */
    public function show(PurchaseInvoice $purchaseInvoice)
    {
        return new PurchaseInvoiceResource($purchaseInvoice->load([
            'supplier',
            'warehouse',
            'company',
            'user',
            'purchaseOrder',
            'items.product',
            'items.product.unit_01',
            'items.product.unit_02',
        ]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return PurchaseInvoiceResource
     */
    public function update(Request $request, PurchaseInvoice $purchaseInvoice)
    {
        $validated = $request->validate([
            'invoice_number' => 'sometimes|required|string|max:255|unique:purchase_invoices,invoice_number,'.$purchaseInvoice->id,
            'date' => 'sometimes|required|date',
            'due_date' => 'nullable|date|after_or_equal:date',
            'supplier_id' => 'sometimes|required|exists:suppliers,id',
            'warehouse_id' => 'sometimes|required|exists:warehouses,id',
            'company_id' => 'sometimes|required|exists:companies,id',
            'user_id' => 'nullable|exists:users,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'type' => 'nullable|in:p,d',
            'reference_no' => 'nullable|string|max:255',
            'remark' => 'nullable|string',
            'sub_total' => 'sometimes|required|numeric|min:0',
            'tax_total' => 'sometimes|required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'grand_total' => 'sometimes|required|numeric|min:0',
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

        return DB::transaction(function () use ($validated, $purchaseInvoice) {
            $purchaseInvoice->update([
                'invoice_number' => $validated['invoice_number'] ?? $purchaseInvoice->invoice_number,
                'date' => $validated['date'] ?? $purchaseInvoice->date,
                'due_date' => $validated['due_date'] ?? $purchaseInvoice->due_date,
                'supplier_id' => $validated['supplier_id'] ?? $purchaseInvoice->supplier_id,
                'warehouse_id' => $validated['warehouse_id'] ?? $purchaseInvoice->warehouse_id,
                'company_id' => $validated['company_id'] ?? $purchaseInvoice->company_id,
                'user_id' => $validated['user_id'] ?? $purchaseInvoice->user_id,
                'purchase_order_id' => $validated['purchase_order_id'] ?? $purchaseInvoice->purchase_order_id,
                'type' => $validated['type'] ?? $purchaseInvoice->type,
                'reference_no' => $validated['reference_no'] ?? $purchaseInvoice->reference_no,
                'remark' => $validated['remark'] ?? $purchaseInvoice->remark,
                'sub_total' => $validated['sub_total'] ?? $purchaseInvoice->sub_total,
                'tax_total' => $validated['tax_total'] ?? $purchaseInvoice->tax_total,
                'tax_rate' => $validated['tax_rate'] ?? $purchaseInvoice->tax_rate,
                'grand_total' => $validated['grand_total'] ?? $purchaseInvoice->grand_total,
                'notes' => $validated['notes'] ?? $purchaseInvoice->notes,
                'terms_conditions' => $validated['terms_conditions'] ?? $purchaseInvoice->terms_conditions,
            ]);

            // Update items if provided
            if (isset($validated['items'])) {
                $purchaseInvoice->items()->delete();

                foreach ($validated['items'] as $item) {
                    $purchaseInvoice->items()->create([
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

            return new PurchaseInvoiceResource($purchaseInvoice->load(['supplier', 'warehouse', 'company', 'user', 'items.product']));
        });
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(PurchaseInvoice $purchaseInvoice)
    {
        $purchaseInvoice->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get suppliers for dropdown.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function suppliers()
    {
        $suppliers = Supplier::select('id', 'name', 'code')
            ->get()
            ->map(function ($supplier) {
                return [
                    'value' => $supplier->id,
                    'label' => "{$supplier->code} - {$supplier->name}",
                ];
            });

        return response()->json($suppliers);
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
        $prefix = 'PI';
        $year = date('Y');
        $month = date('m');

        $latest = PurchaseInvoice::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $latest ? intval(substr($latest->invoice_number, -4)) + 1 : 1;
        $invoiceNumber = "{$prefix}-{$year}{$month}-".str_pad($sequence, 4, '0', STR_PAD_LEFT);

        return response()->json(['invoice_number' => $invoiceNumber]);
    }
}
