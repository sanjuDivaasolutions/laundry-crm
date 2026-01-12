<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Http\Resources\Admin\UnitResource;
use App\Models\Unit;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class UnitApiController extends Controller
{
    protected $className = Unit::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = UnitResource::class;
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
        abort_if(Gate::denies('unit_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return UnitResource::collection(Unit::advancedFilter());
    }

    public function store(StoreUnitRequest $request)
    {
        $unit = Unit::create($request->validated());

        return (new UnitResource($unit))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('unit_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(Unit $unit)
    {
        abort_if(Gate::denies('unit_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new UnitResource($unit);
    }

    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $unit->update($request->validated());

        return (new UnitResource($unit))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(Unit $unit)
    {
        abort_if(Gate::denies('unit_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new UnitResource($unit),
            'meta' => [],
        ]);
    }

    public function destroy(Unit $unit)
    {
        abort_if(Gate::denies('unit_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $unit->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
