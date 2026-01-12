<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalesReturnRequest;
use App\Http\Requests\UpdateSalesReturnRequest;
use App\Http\Resources\Admin\SalesReturnResource;
use App\Models\SalesReturn;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class SalesReturnApiController extends Controller
{
    protected $className = SalesReturn::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = SalesReturnResource::class;
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
        abort_if(Gate::denies('sales_return_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        return SalesReturnResource::collection(SalesReturn::advancedFilter());
    }

    public function store(StoreSalesReturnRequest $request)
    {
        $salesReturn = SalesReturn::create($request->validated());

        return (new SalesReturnResource($salesReturn))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('sales_return_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(SalesReturn $salesReturn)
    {
        abort_if(Gate::denies('sales_return_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new SalesReturnResource($salesReturn);
    }

    public function update(UpdateSalesReturnRequest $request, SalesReturn $salesReturn)
    {
        $salesReturn->update($request->validated());

        return (new SalesReturnResource($salesReturn))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(SalesReturn $salesReturn)
    {
        abort_if(Gate::denies('sales_return_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new SalesReturnResource($salesReturn),
            'meta' => [],
        ]);
    }

    public function destroy(SalesReturn $salesReturn)
    {
        abort_if(Gate::denies('sales_return_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $salesReturn->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
