<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Http\Resources\Admin\PermissionResource;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class PermissionsApiController extends Controller
{
    protected $className = Permission::class;

    protected $scopes = [];

    protected $with = [];

    protected $fetcher = 'advancedFilter';

    protected $filterMethods = ['index', 'getCsv', 'getPdf'];

    protected $fields = ['title'];

    protected $filters = [
        ['request' => 'f_permission_group', 'field' => 'permission_group_id', 'operator' => 'in'],
    ];

    use ControllerRequest;
    use ExportRequest;
    use SearchFilters;

    public function index()
    {
        abort_if(Gate::denies('permission_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return PermissionResource::collection(Permission::query()->with(['group:id,name'])->advancedFilter());
    }

    public function store(StorePermissionRequest $request)
    {
        abort_if(Gate::denies('permission_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $permission = Permission::create($request->validated());

        return (new PermissionResource($permission))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('permission_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [
                'permission_group' => PermissionGroup::get(['id', 'name']),
            ],
        ]);
    }

    public function show(Permission $permission)
    {
        abort_if(Gate::denies('permission_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new PermissionResource($permission);
    }

    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        abort_if(Gate::denies('permission_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $permission->update($request->validated());

        return (new PermissionResource($permission))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(Permission $permission)
    {
        abort_if(Gate::denies('permission_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new PermissionResource($permission),
        ]);
    }

    public function destroy(Permission $permission)
    {
        abort_if(Gate::denies('permission_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort(Response::HTTP_FORBIDDEN, 'Permission delete is not allowed');
        // $permission->delete();

        // return response(null, Response::HTTP_NO_CONTENT);
    }
}
