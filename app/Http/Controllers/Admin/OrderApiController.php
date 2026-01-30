<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\OrderResource;
use App\Models\Order;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Illuminate\Http\Response;
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
        // abort_if(Gate::denies('order_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->success(OrderResource::collection(
            Order::with($this->with)->advancedFilter()
        ));
    }

    public function show(Order $order)
    {
        // abort_if(Gate::denies('order_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->success(new OrderResource($order->load(['customer', 'processingStatus', 'orderStatus', 'orderItems', 'payments', 'statusHistories'])));
    }
}
