<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\SalesOrderStatusResource;
use App\Models\SalesOrder;
use App\Models\SalesOrderActivity;
use App\Models\SalesOrderItem;
use App\Models\Shipment;
use App\Services\ShipmentService;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class LoadingPlanApiController extends Controller
{
    protected $className = SalesOrder::class;
    protected $scopes = [];
    protected $with = [];
    protected $fetcher = 'advancedFilter';
    protected $filterMethods = ['index', 'getCsv', 'getPdf'];
    protected $fields = ['so_number'];
    protected $filters = [
        //['request'=>'','field'=>'','operator'=>'in'],
    ];

    use SearchFilters;
    use ControllerRequest;
    use ExportRequest;

    public function index()
    {
        abort_if(Gate::denies('sales_order_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return SalesOrderStatusResource::collection(SalesOrderItem::query()
            ->whereHas('activity', function ($q) {
                $q->where('fir_activity_master_id', '=', config('system.defaults.fir_activity.ready_to_ship', 20));
            })
            ->with([
                'salesOrder:id,buyer_id,destination_port_id',
                'salesOrder.destinationPort:id,name',
                'salesOrder.buyer:id,name',
                'product:id,name,category_id',
                'product.category:id,name',
                'unit:id,name',
                'activity:id,sales_order_item_id,fir_activity_master_id,remark,date',
                'shade:id,name',
                'subShade:id,name',
            ])
            ->advancedFilter());
    }

    public function moveToShipped(SalesOrderItem $salesOrderItem)
    {
        $status = [
            'sales_order_id'        => $salesOrderItem->sales_order_id,
            'fir_activity_master_id'=> config('system.defaults.fir_activity.shipped', 21),
            'remark'                => 'Moved to Shipped',
            'active'                => 1,
            'user_id'               => auth()->id(),
            'date'                  => Carbon::now()->format(config('project.date_format')),
        ];
        $new = $salesOrderItem->activity()->create($status);

        //set active to 0 for all other status
        SalesOrderActivity::query()
            ->where('sales_order_item_id', $salesOrderItem->id)
            ->where('id', '!=', $new->id)
            ->update(['active' => 0]);

        return okResponse($salesOrderItem);
    }

    public function generateShipment()
    {
        $ids = stringToArray(request('ids', ''));

        abort_if(empty($ids), Response::HTTP_BAD_REQUEST, 'No items selected');

        $salesOrderItems = SalesOrderItem::query()
            ->whereIn('id', $ids)
            ->whereHas('activity', function ($q) {
                $q->where('fir_activity_master_id', '=', config('system.defaults.fir_activity.ready_to_ship', 20));
            })
            ->with([
                'salesOrder:id,buyer_id,destination_port_id',
                'salesOrder.destinationPort:id,name',
                'salesOrder.buyer:id,name',
                'product:id,name,category_id',
                'product.category:id,name',
                'unit:id,name',
                'activity:id,sales_order_item_id,fir_activity_master_id,remark,date',
                'shade:id,name',
                'subShade:id,name',
            ])
            ->get();

        abort_if($salesOrderItems->count() != count($ids), Response::HTTP_BAD_REQUEST, 'Selected items are not valid');

        $shipment = new Shipment();
        $shipment->date = Carbon::now()->format(config('project.date_format'));
        $shipment->code = ShipmentService::getShipmentCode();
        $shipment->save();

        $amount = 0;

        foreach ($salesOrderItems as $item) {
            $shipment->items()->create([
                'sales_order_item_id' => $item->id,
                'sales_order_id'      => $item->sales_order_id,
                'item_code'           => $item->item_code,
                'supplier_id'         => $item?->salesOrder?->fir?->supplier_id,
                'invoice_amount'      => $item->amount,
            ]);
            $amount += $item->amount;
            $this->moveToShipped($item);
        }
        $shipment->amount = $amount;
        $shipment->save();

        return okResponse($shipment);
    }

}
