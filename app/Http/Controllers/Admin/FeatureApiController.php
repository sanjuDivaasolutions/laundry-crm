<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeatureRequest;
use App\Http\Requests\UpdateFeatureRequest;
use App\Http\Resources\Admin\FeatureResource;
use App\Models\Feature;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class FeatureApiController extends Controller
{
    protected $className = Feature::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = FeatureResource::class;
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
        abort_if(Gate::denies('feature_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        return FeatureResource::collection(Feature::advancedFilter());
    }

    public function store(StoreFeatureRequest $request)
    {
        $feature = Feature::create($request->validated());

        return (new FeatureResource($feature))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('feature_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(Feature $feature)
    {
        abort_if(Gate::denies('feature_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new FeatureResource($feature);
    }

    public function update(UpdateFeatureRequest $request, Feature $feature)
    {
        $feature->update($request->validated());

        return (new FeatureResource($feature))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(Feature $feature)
    {
        abort_if(Gate::denies('feature_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new FeatureResource($feature),
            'meta' => [],
        ]);
    }

    public function destroy(Feature $feature)
    {
        abort_if(Gate::denies('feature_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $feature->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
