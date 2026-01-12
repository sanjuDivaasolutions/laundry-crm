<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStateRequest;
use App\Http\Requests\UpdateStateRequest;
use App\Http\Resources\Admin\StateResource;
use App\Models\Country;
use App\Models\State;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StateApiController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('state_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return StateResource::collection(State::with(['country'])->advancedFilter());
    }

    public function store(StoreStateRequest $request)
    {
        $state = State::create($request->validated());

        return (new StateResource($state))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('state_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [
                'country' => Country::get(['id', 'name']),
            ],
        ]);
    }

    public function show(State $state)
    {
        abort_if(Gate::denies('state_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new StateResource($state->load(['country']));
    }

    public function update(UpdateStateRequest $request, State $state)
    {
        $state->update($request->validated());

        return (new StateResource($state))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(State $state)
    {
        abort_if(Gate::denies('state_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new StateResource($state->load(['country'])),
            'meta' => [
                'country' => Country::get(['id', 'name']),
            ],
        ]);
    }

    public function destroy(State $state)
    {
        abort_if(Gate::denies('state_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $state->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
