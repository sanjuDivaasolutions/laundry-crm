<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCityRequest;
use App\Http\Requests\UpdateCityRequest;
use App\Http\Resources\Admin\CityResource;
use App\Models\City;
use App\Models\State;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class CityApiController extends Controller
{
    protected array $filterMethods = ['index'];
    protected array $fields = ['name', 'state.name', 'state.country.name'];

    use SearchFilters;

    public function index()
    {
        abort_if(Gate::denies('city_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return CityResource::collection(City::query()
            ->with([
                'state:id,name,country_id',
                'state.country:id,name'
            ])
            ->advancedFilter());
    }

    public function store(StoreCityRequest $request)
    {
        $city = City::create($request->validated());

        return (new CityResource($city))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('city_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [
                'state' => State::get(['id', 'name']),
            ],
        ]);
    }

    public function show(City $city)
    {
        abort_if(Gate::denies('city_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new CityResource($city->load(['state']));
    }

    public function update(UpdateCityRequest $request, City $city)
    {
        $city->update($request->validated());

        return (new CityResource($city))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(City $city)
    {
        abort_if(Gate::denies('city_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new CityResource($city->load(['state'])),
            'meta' => [
                'state' => State::get(['id', 'name']),
            ],
        ]);
    }

    public function destroy(City $city)
    {
        abort_if(Gate::denies('city_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $city->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
