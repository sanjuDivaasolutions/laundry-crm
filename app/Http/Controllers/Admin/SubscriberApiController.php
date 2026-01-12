<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubscriberRequest;
use App\Http\Requests\UpdateSubscriberRequest;
use App\Http\Resources\Admin\SubscriberResource;
use App\Models\Subscriber;
use App\Services\DatabaseService;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SubscriberApiController extends Controller
{
    protected $className = Subscriber::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = SubscriberResource::class;
    protected $fetcher = 'advancedFilter';
    protected $processListMethod = 'getProcessedList';
    protected $filterMethods = ['index', 'getCsv', 'getPdf'];
    protected $csvFilePrefix = 'subscribers-list-';
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
        abort_if(Gate::denies('subscriber_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        return SubscriberResource::collection(Subscriber::advancedFilter());
    }

    /**
     * @throws \Exception
     */
    public function store(StoreSubscriberRequest $request)
    {
        $obj = null;
        DatabaseService::executeTransaction(function () use ($request, &$obj) {
            $obj = Subscriber::query()->create($request->validated());
        });

        return (new SubscriberResource($obj))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('subscriber_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(Subscriber $subscriber)
    {
        abort_if(Gate::denies('subscriber_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new SubscriberResource($subscriber);
    }

    /**
     * @throws \Exception
     */
    public function update(UpdateSubscriberRequest $request, Subscriber $subscriber)
    {
        DatabaseService::executeTransaction(function () use ($request, $subscriber) {
            $subscriber->update($request->validated());
        });

        return (new SubscriberResource($subscriber))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(Subscriber $subscriber)
    {
        abort_if(Gate::denies('subscriber_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new SubscriberResource($subscriber),
            'meta' => [],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function destroy(Subscriber $subscriber)
    {
        abort_if(Gate::denies('subscriber_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        DatabaseService::executeTransaction(function () use ($subscriber) {
            $subscriber->delete();
        });

        return response(null, Response::HTTP_NO_CONTENT);
    }


    public function subscribe(Request $request)
    {
        $subscriber = Subscriber::query()->where('email', $request->email)->first();
        if ($subscriber) {
            $subscriber->update(['active' => 1]);
        } else {
            $data = [
                'email'      => $request->email,
                'name'       => $request->name,
                'company_id' => $request->company_id,
                'active'     => 1,
            ];
            $subscriber = Subscriber::query()->create($data);
        }

        return response([
            'data' => $subscriber,
            'meta' => [],
        ]);
    }
}
