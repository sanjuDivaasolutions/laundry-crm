<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Resources\Admin\ItemEditResource;
use App\Http\Resources\Admin\ItemResource;
use App\Models\Item;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class ItemApiController extends Controller
{
    protected $className = Item::class;

    protected $scopes = [];

    protected $with = ['category'];

    protected $exportResource = ItemResource::class;

    protected $fetcher = 'advancedFilter';

    protected $processListMethod = 'getProcessedList';

    protected $filterMethods = ['index', 'getCsv', 'getPdf'];

    protected $csvFilePrefix = 'items';

    protected $pdfFilePrefix = 'items';

    protected $fields = ['name', 'code'];

    protected $filters = [];

    use ControllerRequest;
    use ExportRequest;
    use SearchFilters;

    public function index()
    {
        abort_if(Gate::denies('item_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return ItemResource::collection(
            Item::with($this->with)->advancedFilter()
        );
    }

    public function store(StoreItemRequest $request)
    {
        $item = Item::create($request->validated());

        return (new ItemResource($item->load(['category'])))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('item_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(Item $item)
    {
        abort_if(Gate::denies('item_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ItemResource($item->load(['category']));
    }

    public function update(UpdateItemRequest $request, Item $item)
    {
        $item->update($request->validated());

        return (new ItemResource($item->load(['category'])))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(Item $item)
    {
        abort_if(Gate::denies('item_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new ItemEditResource($item),
            'meta' => [],
        ]);
    }

    public function destroy(Item $item)
    {
        abort_if(Gate::denies('item_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $item->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }


}
