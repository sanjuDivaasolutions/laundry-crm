<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShipmentRequest;
use App\Http\Requests\UpdateShipmentRequest;
use App\Http\Resources\Admin\ShipmentResource;
use App\Models\Shipment;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class ShipmentApiController extends Controller
{
    protected $className = Shipment::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = ShipmentResource::class;
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
        abort_if(Gate::denies('shipment_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        return ShipmentResource::collection(Shipment::advancedFilter());
    }

    public function store(StoreShipmentRequest $request)
    {
        $shipment = Shipment::create($request->validated());

        return (new ShipmentResource($shipment))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('shipment_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(Shipment $shipment)
    {
        abort_if(Gate::denies('shipment_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ShipmentResource($shipment);
    }

    public function update(UpdateShipmentRequest $request, Shipment $shipment)
    {
        $shipment->update($request->validated());

        return (new ShipmentResource($shipment))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(Shipment $shipment)
    {
        abort_if(Gate::denies('shipment_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new ShipmentResource($shipment->load([
                'package:id,code',
                'shipmentMode:id,name',
            ])),
            'meta' => [],
        ]);
    }

    public function destroy(Shipment $shipment)
    {
        abort_if(Gate::denies('shipment_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $shipment->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
