<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MessageRequest;
use App\Http\Resources\Admin\MessageResource;
use App\Models\Message;
use App\Services\DatabaseService;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class MessageApiController extends Controller
{
    protected $className = Message::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = MessageResource::class;
    protected $fetcher = 'advancedFilter';
    protected $processListMethod = 'getProcessedList';
    protected $filterMethods = ['index', 'getCsv', 'getPdf'];
    protected $csvFilePrefix = 'messages-list-';
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
        abort_if(Gate::denies('message_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        return MessageResource::collection(Message::advancedFilter());
    }

    /**
     * @throws \Exception
     */
    public function store(MessageRequest $request)
    {
        $obj = null;
        DatabaseService::executeTransaction(function () use ($request, &$obj) {
            $obj = Message::query()->create($request->validated());
        });

        return (new MessageResource($obj))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('message_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(Message $message)
    {
        abort_if(Gate::denies('message_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new MessageResource($message);
    }

    /**
     * @throws \Exception
     */
    public function update(MessageRequest $request, Message $message)
    {
        DatabaseService::executeTransaction(function () use ($request, $message) {
            $message->update($request->validated());
        });

        return (new MessageResource($message))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(Message $message)
    {
        abort_if(Gate::denies('message_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new MessageResource($message),
            'meta' => [],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function destroy(Message $message)
    {
        abort_if(Gate::denies('message_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        DatabaseService::executeTransaction(function () use ($message) {
            $message->delete();
        });

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
