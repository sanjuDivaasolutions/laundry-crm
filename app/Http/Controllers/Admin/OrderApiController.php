<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\Admin\OrderEditResource;
use App\Http\Resources\Admin\OrderResource;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\ProcessingStatus;
use App\Models\Service;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class OrderApiController extends Controller
{
    protected $className = Order::class;

    protected $scopes = [];

    protected $with = ['customer', 'processingStatus', 'orderStatus'];

    protected $exportResource = OrderResource::class;

    protected $fetcher = 'advancedFilter';

    protected $processListMethod = 'getProcessedList';

    protected $filterMethods = ['index', 'getCsv', 'getPdf'];

    protected $csvFilePrefix = 'orders';

    protected $pdfFilePrefix = 'orders';

    protected $fields = ['order_number', 'customer.name', 'customer.phone'];

    protected $filters = [];

    use ControllerRequest;
    use ExportRequest;
    use SearchFilters;

    public function index()
    {
        abort_if(Gate::denies('order_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->success(OrderResource::collection(
            Order::with($this->with)->advancedFilter()
        ));
    }

    public function create()
    {
        abort_if(Gate::denies('order_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();

        $order = DB::transaction(function () use ($validated) {
            $items = $validated['items'] ?? [];
            unset($validated['items']);

            $validated['total_items'] = collect($items)->sum('quantity');
            $validated['subtotal'] = collect($items)->sum(fn ($i) => $i['quantity'] * $i['unit_price']);

            $discountAmount = $validated['discount_amount'] ?? 0;
            if (($validated['discount_type'] ?? null) === 'percentage') {
                $discountAmount = $validated['subtotal'] * ($discountAmount / 100);
            }
            $validated['discount_amount'] = $discountAmount;

            $taxableAmount = $validated['subtotal'] - $discountAmount;
            $taxRate = $validated['tax_rate'] ?? 0;
            $validated['tax_amount'] = $taxableAmount * ($taxRate / 100);
            $validated['total_amount'] = $taxableAmount + $validated['tax_amount'];
            $validated['balance_amount'] = $validated['total_amount'];
            $validated['paid_amount'] = 0;

            $validated['processing_status_id'] = $validated['processing_status_id']
                ?? ProcessingStatus::where('status_name', 'Pending')->value('id')
                ?? ProcessingStatus::first()?->id;
            $validated['order_status_id'] = $validated['order_status_id']
                ?? OrderStatus::where('status_name', 'Open')->value('id')
                ?? OrderStatus::first()?->id;
            $validated['created_by_employee_id'] = $validated['created_by_employee_id'] ?? adminAuth()->id();

            $order = Order::create($validated);

            foreach ($items as $itemData) {
                $item = Item::find($itemData['item_id']);
                $service = Service::find($itemData['service_id']);

                $order->orderItems()->create([
                    'item_id' => $itemData['item_id'],
                    'service_id' => $itemData['service_id'],
                    'item_name' => $item?->name ?? 'Unknown',
                    'service_name' => $service?->name ?? 'Unknown',
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $itemData['quantity'] * $itemData['unit_price'],
                    'color' => $itemData['color'] ?? null,
                    'brand' => $itemData['brand'] ?? null,
                    'defect_notes' => $itemData['defect_notes'] ?? null,
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            return $order;
        });

        return (new OrderResource($order->load($this->with)))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Order $order)
    {
        abort_if(Gate::denies('order_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->success(new OrderResource(
            $order->load(['customer', 'processingStatus', 'orderStatus', 'orderItems', 'payments', 'statusHistories'])
        ));
    }

    public function edit(Order $order)
    {
        abort_if(Gate::denies('order_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new OrderEditResource($order->load(['orderItems'])),
            'meta' => [],
        ]);
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $order) {
            $items = $validated['items'] ?? null;
            unset($validated['items']);

            if ($items !== null) {
                $validated['total_items'] = collect($items)->sum('quantity');
                $validated['subtotal'] = collect($items)->sum(fn ($i) => $i['quantity'] * $i['unit_price']);

                $discountAmount = $validated['discount_amount'] ?? $order->discount_amount;
                if (($validated['discount_type'] ?? $order->discount_type) === 'percentage') {
                    $discountAmount = $validated['subtotal'] * ($discountAmount / 100);
                }
                $validated['discount_amount'] = $discountAmount;

                $taxableAmount = $validated['subtotal'] - $discountAmount;
                $taxRate = $validated['tax_rate'] ?? $order->tax_rate;
                $validated['tax_amount'] = $taxableAmount * ($taxRate / 100);
                $validated['total_amount'] = $taxableAmount + $validated['tax_amount'];
                $validated['balance_amount'] = $validated['total_amount'] - $order->paid_amount;

                $existingItemIds = collect($items)->pluck('id')->filter()->all();
                $order->orderItems()->whereNotIn('id', $existingItemIds)->delete();

                foreach ($items as $itemData) {
                    $item = Item::find($itemData['item_id']);
                    $service = Service::find($itemData['service_id']);

                    $orderItemData = [
                        'item_id' => $itemData['item_id'],
                        'service_id' => $itemData['service_id'],
                        'item_name' => $item?->name ?? 'Unknown',
                        'service_name' => $service?->name ?? 'Unknown',
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'total_price' => $itemData['quantity'] * $itemData['unit_price'],
                        'color' => $itemData['color'] ?? null,
                        'brand' => $itemData['brand'] ?? null,
                        'defect_notes' => $itemData['defect_notes'] ?? null,
                        'notes' => $itemData['notes'] ?? null,
                    ];

                    if (! empty($itemData['id'])) {
                        OrderItem::where('id', $itemData['id'])->update($orderItemData);
                    } else {
                        $order->orderItems()->create($orderItemData);
                    }
                }
            }

            $order->update($validated);
        });

        return (new OrderResource($order->fresh($this->with)))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(Order $order)
    {
        abort_if(Gate::denies('order_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $order->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
