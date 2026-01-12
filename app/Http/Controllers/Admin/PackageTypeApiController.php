<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePackageTypeRequest;
use App\Http\Requests\UpdatePackageTypeRequest;
use App\Http\Resources\Admin\PackageTypeResource;
use App\Models\PackageType;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class PackageTypeApiController extends Controller
{
    protected $className = PackageType::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = PackageTypeResource::class;
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
        abort_if(Gate::denies('package_type_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        return PackageTypeResource::collection(PackageType::advancedFilter());
    }

    public function store(StorePackageTypeRequest $request)
    {
        $packageType = PackageType::create($request->validated());

        return (new PackageTypeResource($packageType))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('package_type_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(PackageType $packageType)
    {
        abort_if(Gate::denies('package_type_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new PackageTypeResource($packageType);
    }

    public function update(UpdatePackageTypeRequest $request, PackageType $packageType)
    {
        $packageType->update($request->validated());

        return (new PackageTypeResource($packageType))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(PackageType $packageType)
    {
        abort_if(Gate::denies('package_type_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new PackageTypeResource($packageType),
            'meta' => [],
        ]);
    }

    public function destroy(PackageType $packageType)
    {
        abort_if(Gate::denies('package_type_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $packageType->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
