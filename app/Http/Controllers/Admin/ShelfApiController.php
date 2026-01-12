<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShelfRequest;
use App\Http\Requests\UpdateShelfRequest;
use App\Http\Resources\Admin\ShelfResource;
use App\Models\Shelf;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class ShelfApiController extends Controller
{
    protected $className = Shelf::class;
    protected $scopes = [];
    protected $with = ['warehouse:id,name'];
    protected $exportResource = ShelfResource::class;
    protected $fetcher = 'advancedFilter';
    protected $processListMethod = 'getProcessedList';
    protected $filterMethods = ['index', 'getCsv', 'getPdf'];
    protected $csvFilePrefix = null;
    protected $pdfFilePrefix = null;
    protected $fields = ['name'];
    protected $filters = [
        ['request' => 'f_current_company_only', 'field' => 'company', 'operator' => 'scope'],
        ['request' => 'f_warehouse_id', 'field' => 'warehouse_id', 'operator' => 'in'],
        ['request' => 'f_active_only', 'field' => 'active', 'operator' => 'scope'],
    ];

    use SearchFilters;
    use ControllerRequest;
    use ExportRequest;

    public function index()
    {
        abort_if(Gate::denies('shelf_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $shelf = Shelf::query()
            ->with([
                'warehouse:id,name',
                'productStockShelf' => function ($q) {
                    $q->selectRaw('shelf_id, sum(on_hand) as on_hand')
                        ->groupBy('shelf_id');
                },
            ])
            ->advancedFilter();

        $shelf->each(function ($s) {
            $s->on_hand = $s->productStockShelf->sum('on_hand');
            unset($s->productStockShelf);
        });

        return ShelfResource::collection($shelf);
    }

    public function store(StoreShelfRequest $request)
    {
        $shelf = Shelf::create($request->validated());

        return (new ShelfResource($shelf))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('shelf_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(Shelf $shelf)
    {
        abort_if(Gate::denies('shelf_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $shelf->load([
            'warehouse:id,name',
            'productStockShelf.productStock.product' => function ($query) {
                $query->select([
                    'id',
                    'name',
                    'sku',
                    'code',
                    'category_id',
                    'manufacturer',
                    'unit_01_id',
                    'type',
                ])->with([
                    'category:id,name',
                    'unit_01:id,name',
                ]);
            },
            'productStockShelf.productStock.warehouse:id,name',
        ]);

        return new ShelfResource($shelf);
    }

    public function update(UpdateShelfRequest $request, Shelf $shelf)
    {
        $shelf->update($request->validated());

        return (new ShelfResource($shelf))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(Shelf $shelf)
    {
        abort_if(Gate::denies('shelf_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new ShelfResource($shelf->load(['warehouse:id,name'])),
            'meta' => [],
        ]);
    }

    public function destroy(Shelf $shelf)
    {
        abort_if(Gate::denies('shelf_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $shelf->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
