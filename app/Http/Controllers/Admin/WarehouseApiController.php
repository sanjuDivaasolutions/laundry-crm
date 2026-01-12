<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Http\Resources\Admin\WarehouseResource;
use App\Models\Warehouse;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class WarehouseApiController extends Controller
{
    protected $className = Warehouse::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = WarehouseResource::class;
    protected $fetcher = 'advancedFilter';
    protected $processListMethod = 'getProcessedList';
    protected $filterMethods = ['index','getCsv','getPdf'];
    protected $csvFilePrefix = null;
    protected $pdfFilePrefix = null;
    protected $fields = ['name'];
    protected $filters = [
        ['request' => 'f_current_company_only', 'field' => 'company', 'operator' => 'scope'],
    ];

    use SearchFilters;
    use ControllerRequest;
    use ExportRequest;
    public function index()
    {
        abort_if(Gate::denies('warehouse_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList(){
        return WarehouseResource::collection(Warehouse::advancedFilter());
    }

    public function store(StoreWarehouseRequest $request)
    {
        $warehouse = Warehouse::create($request->validated());

        return (new WarehouseResource($warehouse))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('warehouse_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(Warehouse $warehouse)
    {
        abort_if(Gate::denies('warehouse_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new WarehouseResource($warehouse);
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse)
    {
        $warehouse->update($request->validated());

        return (new WarehouseResource($warehouse))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(Warehouse $warehouse)
    {
        abort_if(Gate::denies('warehouse_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new WarehouseResource($warehouse->load([
                'city:id,name',
                'country:id,name',
                'state:id,name',
            ])),    
            'meta' => [],
        ]);
    }

    public function destroy(Warehouse $warehouse)
    {
        abort_if(Gate::denies('warehouse_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $warehouse->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
