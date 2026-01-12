<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCurrencyRequest;
use App\Http\Requests\UpdateCurrencyRequest;
use App\Http\Resources\Admin\CurrencyResource;
use App\Models\Currency;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class CurrencyApiController extends Controller
{
    protected $className = Currency::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = CurrencyResource::class;
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
        abort_if(Gate::denies('currency_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return CurrencyResource::collection(Currency::advancedFilter());
    }

    public function store(StoreCurrencyRequest $request)
    {
        $currency = Currency::create($request->validated());

        return (new CurrencyResource($currency))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('currency_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(Currency $currency)
    {
        abort_if(Gate::denies('currency_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new CurrencyResource($currency);
    }

    public function update(UpdateCurrencyRequest $request, Currency $currency)
    {
        $currency->update($request->validated());

        return (new CurrencyResource($currency))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(Currency $currency)
    {
        abort_if(Gate::denies('currency_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new CurrencyResource($currency),
            'meta' => [],
        ]);
    }

    public function destroy(Currency $currency)
    {
        abort_if(Gate::denies('currency_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $currency->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
