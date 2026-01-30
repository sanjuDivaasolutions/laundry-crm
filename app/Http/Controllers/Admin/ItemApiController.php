<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Resources\Admin\ItemEditResource;
use App\Http\Resources\Admin\ItemResource;
use App\Models\Item;
use App\Models\Service;
use App\Models\ServicePrice;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ItemApiController extends Controller
{
    protected $className = Item::class;

    protected $scopes = [];

    protected $with = [];

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
        return DB::transaction(function () use ($request) {
            $validated = $request->validated();
            $servicePrices = $validated['service_prices'] ?? [];
            unset($validated['service_prices']);

            $item = Item::create($validated);

            // Save service prices
            $this->syncServicePrices($item, $servicePrices);

            return (new ItemResource($item->load(['servicePrices.service'])))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        });
    }

    public function create()
    {
        abort_if(Gate::denies('item_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Return all active services for the form
        $services = Service::active()->ordered()->get(['id', 'name', 'code']);

        return response([
            'meta' => [
                'services' => $services,
            ],
        ]);
    }

    public function show(Item $item)
    {
        abort_if(Gate::denies('item_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ItemResource($item->load(['servicePrices.service']));
    }

    public function update(UpdateItemRequest $request, Item $item)
    {
        return DB::transaction(function () use ($request, $item) {
            $validated = $request->validated();
            $servicePrices = $validated['service_prices'] ?? [];
            unset($validated['service_prices']);

            $item->update($validated);

            // Sync service prices
            $this->syncServicePrices($item, $servicePrices);

            return (new ItemResource($item->load(['servicePrices.service'])))
                ->response()
                ->setStatusCode(Response::HTTP_ACCEPTED);
        });
    }

    public function edit(Item $item)
    {
        abort_if(Gate::denies('item_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Get all active services
        $services = Service::active()->ordered()->get(['id', 'name', 'code']);

        // Get item with service prices
        $item->load(['servicePrices']);

        return response([
            'data' => new ItemEditResource($item),
            'meta' => [
                'services' => $services,
            ],
        ]);
    }

    public function destroy(Item $item)
    {
        abort_if(Gate::denies('item_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $item->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Sync service prices for an item.
     */
    protected function syncServicePrices(Item $item, array $servicePrices): void
    {
        $tenantId = $item->tenant_id;

        // Get existing service price IDs for this item
        $existingIds = $item->servicePrices()->pluck('service_id')->toArray();
        $newServiceIds = [];

        foreach ($servicePrices as $sp) {
            if (empty($sp['service_id']) || ! isset($sp['price'])) {
                continue;
            }

            // Skip if price is null or empty string (means user doesn't want this service)
            if ($sp['price'] === null || $sp['price'] === '') {
                continue;
            }

            $newServiceIds[] = $sp['service_id'];

            ServicePrice::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'item_id' => $item->id,
                    'service_id' => $sp['service_id'],
                ],
                [
                    'price' => $sp['price'],
                    'is_active' => $sp['is_active'] ?? true,
                ]
            );
        }

        // Remove service prices that are no longer in the list
        $toRemove = array_diff($existingIds, $newServiceIds);
        if (! empty($toRemove)) {
            $item->servicePrices()->whereIn('service_id', $toRemove)->delete();
        }
    }
}
