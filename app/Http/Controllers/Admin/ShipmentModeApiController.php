<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShipmentModeRequest;
use App\Http\Requests\UpdateShipmentModeRequest;
use App\Http\Resources\Admin\ShipmentModeResource;
use App\Models\ShipmentMode;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class ShipmentModeApiController extends Controller
{
    protected $className = ShipmentMode::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = ShipmentModeResource::class;
    protected $fetcher = 'advancedFilter';
    protected $processListMethod = 'getProcessedList';
    protected $filterMethods = ['index','getCsv','getPdf'];
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
        abort_if(Gate::denies('shipment_mode_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        return ShipmentModeResource::collection(ShipmentMode::advancedFilter());
    }

    public function store(StoreShipmentModeRequest $request)
    {
        $shipmentMode = ShipmentMode::create($request->validated());

        return (new ShipmentModeResource($shipmentMode))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('shipment_mode_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(ShipmentMode $shipmentMode)
    {
        abort_if(Gate::denies('shipment_mode_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ShipmentModeResource($shipmentMode);
    }

    public function update(UpdateShipmentModeRequest $request, ShipmentMode $shipmentMode)
    {
        $shipmentMode->update($request->validated());

        return (new ShipmentModeResource($shipmentMode))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(ShipmentMode $shipmentMode)
    {
        abort_if(Gate::denies('shipment_mode_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new ShipmentModeResource($shipmentMode),
            'meta' => [],
        ]);
    }

    public function destroy(ShipmentMode $shipmentMode)
    {
        abort_if(Gate::denies('shipment_mode_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $shipmentMode->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
