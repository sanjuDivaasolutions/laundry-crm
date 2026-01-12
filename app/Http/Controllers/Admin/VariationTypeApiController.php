<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVariationTypeRequest;
use App\Http\Requests\UpdateVariationTypeRequest;
use App\Http\Resources\Admin\VariationTypeResource;
use App\Models\VariationType;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class VariationTypeApiController extends Controller
{
    protected $className = VariationType::class;
    protected $scopes = [];
    protected $with = [];
    protected $fetcher = 'advancedFilter';
    protected $filterMethods = ['index','getCsv','getPdf'];
    protected $fields = ['name'];
    protected $filters = [
        //['request'=>'','field'=>'','operator'=>'in'],
    ];

    use SearchFilters;
    use ControllerRequest;
    use ExportRequest;
    public function index()
    {
        abort_if(Gate::denies('variation_type_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return VariationTypeResource::collection(VariationType::advancedFilter());
    }

    public function store(StoreVariationTypeRequest $request)
    {
        $variationType = VariationType::create($request->validated());

        return (new VariationTypeResource($variationType))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('variation_type_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(VariationType $variationType)
    {
        abort_if(Gate::denies('variation_type_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new VariationTypeResource($variationType);
    }

    public function update(UpdateVariationTypeRequest $request, VariationType $variationType)
    {
        $variationType->update($request->validated());

        return (new VariationTypeResource($variationType))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(VariationType $variationType)
    {
        abort_if(Gate::denies('variation_type_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new VariationTypeResource($variationType),
            'meta' => [],
        ]);
    }

    public function destroy(VariationType $variationType)
    {
        abort_if(Gate::denies('variation_type_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $variationType->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
