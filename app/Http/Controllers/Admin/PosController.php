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
 *  *  Last modified: 29/11/25, 12:00 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Buyer;
use App\Models\ProductStock;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\ProductInventory;
use App\Models\Payment;
use App\Models\PaymentMode;
use App\Models\PosSession;
use App\Services\InventoryService;
use App\Services\UtilityService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PosController extends Controller
{
    /**
     * Get all products for POS with stock information
     */
    public function getProducts(Request $request)
    {
        $query = Product::with(['category', 'stock', 'price', 'unit_01'])
            ->where('active', true);

        // Search by name or SKU
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('sku', 'LIKE', '%' . $search . '%');
            });
        }

        // Filter by category
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->get()->map(function ($product) {
            // Calculate total stock across all warehouses
            // Note: on_hand is stored as string, so we need to convert it
            $totalStock = $product->stock->sum(function($stock) {
                return floatval($stock->on_hand ?? 0);
            });
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'selling_price' => $product->price->sale_price ?? 0,
                'category_id' => $product->category_id,
                'category_name' => $product->category->name ?? '',
                'is_service' => $product->type === 'service',
                'is_medicine' => false, // Can be customized based on category or other logic
                'requires_prescription' => false, // Can be customized based on category
                'strength' => null, // Not available in current schema
                'stock_quantity' => $totalStock,
                'low_stock_threshold' => config('inventory.defaults.low_stock_threshold', 10), // Default threshold
                'is_low_stock' => $totalStock <= config('inventory.defaults.low_stock_threshold', 10),
                'unit' => $product->unit_01->name ?? 'pcs'
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get all categories for filtering
     */
    public function getCategories()
    {
        $categories = Category::where('active', true)
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get customers/buyers for POS
     */
    public function getCustomers(Request $request)
    {
        $query = Buyer::where('active', true);

        // Search by name or phone
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('phone', 'LIKE', '%' . $search . '%')
                  ->orWhere('email', 'LIKE', '%' . $search . '%');
            });
        }

        $customers = $query->select(['id', 'name', 'phone', 'email'])
            ->orderBy('name')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $customers
        ]);
    }

    /**
     * Get product stock for specific warehouse
     */
    public function getProductStock($productId, Request $request)
    {
        $warehouseId = $request->get('warehouse_id', config('inventory.defaults.warehouse_id', 1)); // Default warehouse

        $stock = ProductStock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'stock_quantity' => $stock ? floatval($stock->on_hand ?? 0) : 0,
                'in_transit' => $stock ? floatval($stock->in_transit ?? 0) : 0
            ]
        ]);
    }

    /**
     * Process POS sale - create sales invoice
     */
    public function processSale(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'payment_mode_id' => 'required|exists:payment_modes,id',
            'customer_id' => 'nullable|exists:buyers,id',
            'discount' => 'nullable|numeric|min:0',
            'amount_received' => 'nullable|numeric',
            'prescription_verified' => 'boolean',
            'reference_no' => 'nullable|string|max:100',
            'remarks' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            // Check if prescription medicines require verification
            $hasRxMedicines = false;
            foreach ($request->items as $item) {
                $product = Product::find($item['id']);
                if ($product && $product->requires_prescription) {
                    $hasRxMedicines = true;
                    break;
                }
            }

            if ($hasRxMedicines && !$request->prescription_verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Prescription verification required for Rx medicines'
                ], 400);
            }

            // Check stock availability
            foreach ($request->items as $item) {
                $productId = $item['id'];
                $requestedQty = $item['quantity'];
                
                // Get all stocks and sum on_hand (stored as string)
                $stocks = ProductStock::where('product_id', $productId)->get();
                $totalStock = $stocks->sum(function($stock) {
                    return floatval($stock->on_hand ?? 0);
                });
                
                if ($totalStock < $requestedQty) {
                    $product = Product::find($productId);
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for product: {$product->name}. Available: {$totalStock}, Requested: {$requestedQty}"
                    ], 400);
                }
            }

            // Generate invoice number
            $invoiceNumber = 'POS-' . date('Ymd') . '-' . str_pad(
                SalesInvoice::whereDate('created_at', today())->count() + 1, 
                4, 
                '0', 
                STR_PAD_LEFT
            );

            // Get payment mode name for remarks
            $paymentMode = PaymentMode::find($request->payment_mode_id);
            $paymentModeName = $paymentMode ? $paymentMode->name : 'Unknown';

            // Get active session for this user (if any)
            $activeSession = PosSession::where('user_id', Auth::id())
                ->where('status', 'open')
                ->first();

            // Create sales invoice
            // Note: SalesInvoice model uses 'date' field (not invoice_date) with project.date_format
            $salesInvoice = SalesInvoice::create([
                'invoice_number' => $invoiceNumber,
                'buyer_id' => $request->customer_id,
                'company_id' => Auth::user()->company_id,
                'user_id' => Auth::id(),
                'warehouse_id' => config('inventory.defaults.warehouse_id', 1), // Default warehouse
                'pos_session_id' => $activeSession?->id, // Link to active session
                'date' => now()->format(config('project.date_format')),
                'due_date' => now()->format(config('project.date_format')),
                'sub_total' => $request->subtotal,
                'tax_total' => $request->tax_amount,
                'grand_total' => $request->total,
                'payment_status' => 'paid',
                'order_type' => 'product',
                'remark' => $request->remarks ?? 'POS Sale - ' . $paymentModeName
            ]);

            // Create invoice items
            foreach ($request->items as $item) {
                SalesInvoiceItem::create([
                    'sales_invoice_id' => $salesInvoice->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                    'tax_amount' => ($item['quantity'] * $item['unit_price']) * (config('accounting.defaults.tax_percentage', 13) / 100), // 13% tax
                ]);
            }

            // Record the sale in inventory
            InventoryService::recordPosSale($salesInvoice);

            // Generate payment order number using UtilityService
            $paymentOrderNo = UtilityService::generateCode([
                'table'  => 'payments',
                'field'  => 'order_no',
                'prefix' => 'PAY-'
            ]);

            // Create payment record
            // Payment model expects payment_date in project.date_format (d/m/Y) for the mutator
            $payment = Payment::create([
                'payment_type' => 'si',
                'tran_type' => 'receive',
                'sales_invoice_id' => $salesInvoice->id,
                'payment_mode_id' => $request->payment_mode_id,
                'order_no' => $paymentOrderNo,
                'reference_no' => $request->reference_no,
                'payment_date' => now()->format(config('project.date_format')),
                'amount' => $request->total,
                'user_id' => Auth::id(),
                'remarks' => 'POS Payment - ' . $paymentModeName . ($request->reference_no ? ' - Ref: ' . $request->reference_no : '')
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sale processed successfully',
                'data' => [
                    'invoice_id' => $salesInvoice->id,
                    'invoice_number' => $salesInvoice->invoice_number,
                    'payment_id' => $payment->id,
                    'payment_order_no' => $payment->order_no,
                    'total' => $salesInvoice->grand_total,
                    'amount_received' => $request->amount_received ?? $request->total,
                    'change' => ($request->amount_received ?? $request->total) - $request->total,
                    'payment_mode' => $paymentModeName
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            // Log detailed error for debugging
            \Log::error('POS Sale Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process sale: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Get sales summary for POS dashboard
     */
    public function getSalesSummary(Request $request)
    {
        try {
            $date = $request->get('date', today());
            $user = Auth::user();
            
            // Handle case where company_id might be null
            $query = SalesInvoice::query();
            if ($user && $user->company_id) {
                $query->where('company_id', $user->company_id);
            }
            
            $summary = $query->whereDate('date', $date)
                ->selectRaw('
                    COUNT(*) as total_transactions,
                    COALESCE(SUM(grand_total), 0) as total_sales,
                    COALESCE(SUM(tax_total), 0) as total_tax,
                    COALESCE(SUM(sub_total), 0) as subtotal_amount,
                    COALESCE(AVG(grand_total), 0) as average_transaction
                ')
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'date' => $date,
                    'total_transactions' => $summary->total_transactions ?? 0,
                    'total_sales' => floatval($summary->total_sales ?? 0),
                    'total_tax' => floatval($summary->total_tax ?? 0),
                    'subtotal_amount' => floatval($summary->subtotal_amount ?? 0),
                    'average_transaction' => floatval($summary->average_transaction ?? 0)
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load sales summary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment modes for POS
     */
    public function getPaymentModes()
    {
        $paymentModes = PaymentMode::where('active', true)
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $paymentModes
        ]);
    }

    /**
     * Quick create a customer from POS
     */
    public function createCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        try {
            $customer = Buyer::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'active' => true,
                'company_id' => Auth::user()->company_id ?? 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'data' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search products by barcode
     */
    public function searchByBarcode($barcode)
    {
        $product = Product::where('sku', $barcode)
            ->orWhere('barcode', $barcode)
            ->with(['category', 'stock', 'price'])
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $totalStock = $product->stock->sum(function($stock) {
            return floatval($stock->on_hand ?? 0);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'selling_price' => $product->price->sale_price ?? 0,
                'stock_quantity' => $totalStock,
                'is_medicine' => false, // Can be customized based on category
                'requires_prescription' => false // Can be customized based on category
            ]
        ]);
    }

    /**
     * Get POS orders list with filters
     */
    public function getOrders(Request $request)
    {
        try {
            $query = SalesInvoice::with(['buyer:id,name,phone', 'user:id,name', 'items.product:id,name,sku'])
                ->where('order_type', 'product');

            // Filter by company
            $user = Auth::user();
            if ($user && $user->company_id) {
                $query->where('company_id', $user->company_id);
            }

            // Filter by date range
            if ($request->has('date_from') && $request->date_from) {
                $dateFrom = Carbon::createFromFormat(config('project.date_format'), $request->date_from)->startOfDay();
                $query->where('created_at', '>=', $dateFrom);
            }

            if ($request->has('date_to') && $request->date_to) {
                $dateTo = Carbon::createFromFormat(config('project.date_format'), $request->date_to)->endOfDay();
                $query->where('created_at', '<=', $dateTo);
            }

            // Filter by today by default if no date specified
            if (!$request->has('date_from') && !$request->has('date_to') && !$request->has('show_all')) {
                $query->whereDate('created_at', today());
            }

            // Filter by payment status
            if ($request->has('payment_status') && $request->payment_status) {
                $query->where('payment_status', $request->payment_status);
            }

            // Search by invoice number or customer name
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'LIKE', "%{$search}%")
                      ->orWhereHas('buyer', function ($bq) use ($search) {
                          $bq->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%");
                      });
                });
            }

            // Pagination
            $perPage = $request->get('per_page', 20);
            $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);

            // Transform data
            $transformedOrders = $orders->getCollection()->map(function ($order) {
                return [
                    'id' => $order->id,
                    'invoice_number' => $order->invoice_number,
                    'date' => $order->created_at->format(config('project.date_format')),
                    'time' => $order->created_at->format('H:i'),
                    'customer' => $order->buyer ? [
                        'id' => $order->buyer->id,
                        'name' => $order->buyer->name,
                        'phone' => $order->buyer->phone
                    ] : null,
                    'items_count' => $order->items->count(),
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->name ?? 'Unknown Product',
                            'product_sku' => $item->product->sku ?? '',
                            'quantity' => $item->quantity,
                            'unit_price' => floatval($item->unit_price),
                            'total' => floatval($item->total)
                        ];
                    }),
                    'sub_total' => floatval($order->sub_total),
                    'tax_total' => floatval($order->tax_total),
                    'grand_total' => floatval($order->grand_total),
                    'payment_status' => $order->payment_status,
                    'cashier' => $order->user ? $order->user->name : 'Unknown',
                    'remark' => $order->remark
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedOrders,
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total()
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('POS Orders Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load orders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single order details
     */
    public function getOrder($id)
    {
        try {
            $order = SalesInvoice::with([
                'buyer:id,name,phone,email',
                'user:id,name',
                'items.product:id,name,sku',
                'payments.paymentMode:id,name'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $order->id,
                    'invoice_number' => $order->invoice_number,
                    'date' => $order->created_at->format(config('project.date_format')),
                    'time' => $order->created_at->format('H:i:s'),
                    'customer' => $order->buyer ? [
                        'id' => $order->buyer->id,
                        'name' => $order->buyer->name,
                        'phone' => $order->buyer->phone,
                        'email' => $order->buyer->email
                    ] : ['name' => 'Walk-in Customer'],
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->name ?? 'Unknown Product',
                            'product_sku' => $item->product->sku ?? '',
                            'quantity' => $item->quantity,
                            'unit_price' => floatval($item->unit_price),
                            'tax_amount' => floatval($item->tax_amount ?? 0),
                            'total' => floatval($item->total)
                        ];
                    }),
                    'sub_total' => floatval($order->sub_total),
                    'tax_total' => floatval($order->tax_total),
                    'grand_total' => floatval($order->grand_total),
                    'payment_status' => $order->payment_status,
                    'payments' => $order->payments->map(function ($payment) {
                        return [
                            'id' => $payment->id,
                            'order_no' => $payment->order_no,
                            'amount' => floatval($payment->amount),
                            'payment_mode' => $payment->paymentMode->name ?? 'Unknown',
                            'reference_no' => $payment->reference_no,
                            'date' => $payment->created_at->format(config('project.date_format') . ' H:i')
                        ];
                    }),
                    'cashier' => $order->user ? $order->user->name : 'Unknown',
                    'remark' => $order->remark,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
    }

    // ==========================================
    // SESSION MANAGEMENT METHODS
    // ==========================================

    /**
     * Get current user's active session
     */
    public function getActiveSession()
    {
        try {
            $session = PosSession::where('user_id', Auth::id())
                ->where('status', 'open')
                ->with('user:id,name')
                ->first();

            if (!$session) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'has_session' => false
                ]);
            }

            // Calculate real-time stats from sales invoices linked to this session
            $sessionSales = SalesInvoice::where('pos_session_id', $session->id)->get();
            $totalSales = $sessionSales->sum('grand_total');
            $totalTransactions = $sessionSales->count();

            return response()->json([
                'success' => true,
                'has_session' => true,
                'data' => [
                    'id' => $session->id,
                    'session_number' => $session->session_number,
                    'opened_at' => $session->opened_at->format('Y-m-d H:i:s'),
                    'duration' => $session->duration,
                    'opening_cash' => floatval($session->opening_cash),
                    'total_sales' => floatval($totalSales),
                    'total_transactions' => $totalTransactions,
                    'user_name' => $session->user->name ?? 'Unknown'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get session: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get orders for current active session
     */
    public function getSessionOrders()
    {
        try {
            $session = PosSession::where('user_id', Auth::id())
                ->where('status', 'open')
                ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active session found'
                ], 404);
            }

            $orders = SalesInvoice::where('pos_session_id', $session->id)
                ->with(['buyer:id,name,code', 'items.product:id,name'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($invoice) {
                    return [
                        'id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'customer_name' => $invoice->buyer->name ?? 'Walk-in Customer',
                        'items_count' => $invoice->items->count(),
                        'grand_total' => floatval($invoice->grand_total),
                        'payment_status' => $invoice->payment_status,
                        'created_at' => $invoice->created_at->format('h:i A'),
                        'items' => $invoice->items->map(function ($item) {
                            return [
                                'name' => $item->product->name ?? 'Unknown',
                                'quantity' => $item->quantity,
                                'price' => floatval($item->selling_price),
                                'total' => floatval($item->total)
                            ];
                        })
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $orders,
                'session_number' => $session->session_number,
                'total_orders' => $orders->count(),
                'total_amount' => $orders->sum('grand_total')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get session orders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Open a new POS session
     */
    public function openSession(Request $request)
    {
        $request->validate([
            'opening_cash' => 'required|numeric|min:0',
        ]);

        try {
            // Check if user already has an open session
            $existingSession = PosSession::where('user_id', Auth::id())
                ->where('status', 'open')
                ->first();

            if ($existingSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an open session. Please close it first.'
                ], 400);
            }

            // Create new session
            $session = PosSession::create([
                'user_id' => Auth::id(),
                'warehouse_id' => config('inventory.defaults.warehouse_id', 1), // Default warehouse
                'company_id' => Auth::user()->company_id ?? 1,
                'session_number' => PosSession::generateSessionNumber(),
                'status' => 'open',
                'opened_at' => now(),
                'opening_cash' => $request->opening_cash,
                'total_sales' => 0,
                'total_transactions' => 0,
                'total_cash_sales' => 0,
                'total_card_sales' => 0,
                'total_other_sales' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Session opened successfully',
                'data' => [
                    'id' => $session->id,
                    'session_number' => $session->session_number,
                    'opened_at' => $session->opened_at->format('Y-m-d H:i:s'),
                    'opening_cash' => floatval($session->opening_cash)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to open session: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Close the active POS session
     */
    public function closeSession(Request $request)
    {
        $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $session = PosSession::where('user_id', Auth::id())
                ->where('status', 'open')
                ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active session found'
                ], 404);
            }

            // Calculate session totals from sales invoices
            $sessionSales = SalesInvoice::where('pos_session_id', $session->id)->get();
            
            $totalSales = $sessionSales->sum('grand_total');
            $totalTransactions = $sessionSales->count();

            // Calculate by payment type
            $cashPayments = Payment::whereIn('sales_invoice_id', $sessionSales->pluck('id'))
                ->whereHas('paymentMode', function ($q) {
                    $q->where('name', 'LIKE', '%cash%');
                })
                ->sum('amount');

            $cardPayments = Payment::whereIn('sales_invoice_id', $sessionSales->pluck('id'))
                ->whereHas('paymentMode', function ($q) {
                    $q->where('name', 'LIKE', '%card%')
                      ->orWhere('name', 'LIKE', '%credit%')
                      ->orWhere('name', 'LIKE', '%debit%');
                })
                ->sum('amount');

            $otherPayments = $totalSales - $cashPayments - $cardPayments;

            // Calculate expected cash
            $expectedCash = floatval($session->opening_cash) + floatval($cashPayments);
            $cashDifference = floatval($request->closing_cash) - $expectedCash;

            // Update session
            $session->update([
                'status' => 'closed',
                'closed_at' => now(),
                'closing_cash' => $request->closing_cash,
                'expected_cash' => $expectedCash,
                'cash_difference' => $cashDifference,
                'total_sales' => $totalSales,
                'total_transactions' => $totalTransactions,
                'total_cash_sales' => $cashPayments,
                'total_card_sales' => $cardPayments,
                'total_other_sales' => $otherPayments,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Session closed successfully',
                'data' => [
                    'id' => $session->id,
                    'session_number' => $session->session_number,
                    'opened_at' => $session->opened_at->format('Y-m-d H:i:s'),
                    'closed_at' => $session->closed_at->format('Y-m-d H:i:s'),
                    'duration' => $session->duration,
                    'opening_cash' => floatval($session->opening_cash),
                    'closing_cash' => floatval($session->closing_cash),
                    'expected_cash' => floatval($session->expected_cash),
                    'cash_difference' => floatval($session->cash_difference),
                    'total_sales' => floatval($session->total_sales),
                    'total_transactions' => $session->total_transactions,
                    'total_cash_sales' => floatval($session->total_cash_sales),
                    'total_card_sales' => floatval($session->total_card_sales),
                    'total_other_sales' => floatval($session->total_other_sales),
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Close Session Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to close session: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get session details/summary by ID
     */
    public function getSession($id)
    {
        try {
            $session = PosSession::with(['user:id,name', 'salesInvoices'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $session->id,
                    'session_number' => $session->session_number,
                    'status' => $session->status,
                    'user_name' => $session->user->name ?? 'Unknown',
                    'opened_at' => $session->opened_at->format('Y-m-d H:i:s'),
                    'closed_at' => $session->closed_at ? $session->closed_at->format('Y-m-d H:i:s') : null,
                    'duration' => $session->duration,
                    'opening_cash' => floatval($session->opening_cash),
                    'closing_cash' => $session->closing_cash ? floatval($session->closing_cash) : null,
                    'expected_cash' => $session->expected_cash ? floatval($session->expected_cash) : null,
                    'cash_difference' => $session->cash_difference ? floatval($session->cash_difference) : null,
                    'total_sales' => floatval($session->total_sales),
                    'total_transactions' => $session->total_transactions,
                    'total_cash_sales' => floatval($session->total_cash_sales),
                    'total_card_sales' => floatval($session->total_card_sales),
                    'total_other_sales' => floatval($session->total_other_sales),
                    'notes' => $session->notes,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found'
            ], 404);
        }
    }

    /**
     * Get list of all sessions (for history/reports)
     */
    public function getSessions(Request $request)
    {
        try {
            $query = PosSession::with('user:id,name');

            // Filter by company
            if (Auth::user()->company_id) {
                $query->where('company_id', Auth::user()->company_id);
            }

            // Filter by status
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Filter by date range
            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('opened_at', '>=', $request->date_from);
            }
            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('opened_at', '<=', $request->date_to);
            }

            $sessions = $query->orderBy('opened_at', 'desc')
                ->paginate($request->get('per_page', 20));

            $transformedSessions = $sessions->getCollection()->map(function ($session) {
                return [
                    'id' => $session->id,
                    'session_number' => $session->session_number,
                    'status' => $session->status,
                    'user_name' => $session->user->name ?? 'Unknown',
                    'opened_at' => $session->opened_at->format('Y-m-d H:i'),
                    'closed_at' => $session->closed_at ? $session->closed_at->format('Y-m-d H:i') : '-',
                    'duration' => $session->duration,
                    'total_sales' => floatval($session->total_sales),
                    'total_transactions' => $session->total_transactions,
                    'cash_difference' => $session->cash_difference ? floatval($session->cash_difference) : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedSessions,
                'pagination' => [
                    'current_page' => $sessions->currentPage(),
                    'last_page' => $sessions->lastPage(),
                    'per_page' => $sessions->perPage(),
                    'total' => $sessions->total()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load sessions: ' . $e->getMessage()
            ], 500);
        }
    }
}