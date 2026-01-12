<?php

/*
 *
 *  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 16/01/25, 11:54 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\Admin\RoleEditResource;
use App\Http\Resources\Admin\RoleResource;
use App\Models\Role;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class RoleApiController extends Controller
{
    protected $className = Role::class;

    protected $scopes = [];

    protected $with = [];

    protected $exportResource = RoleResource::class;

    protected $fetcher = 'advancedFilter';

    protected $processListMethod = 'getProcessedList';

    protected $filterMethods = ['index', 'getCsv', 'getPdf'];

    protected $fields = ['title'];

    protected $filters = [
        // ['request'=>'','field'=>'','operator'=>'in'],
    ];

    use ControllerRequest;
    use ExportRequest;
    use SearchFilters;

    public function index()
    {
        abort_if(Gate::denies('role_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return RoleResource::collection(Role::query()->whereNotIn('id', config('system.auth.hidden_role_id', []))->advancedFilter());
    }

    public function store(StoreRoleRequest $request)
    {
        $role = Role::create($request->validated());
        $role->permissions()->sync($request->input('permissions', []));

        return (new RoleResource($role))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('role_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(Role $role)
    {
        abort_if(Gate::denies('role_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new RoleResource($role);
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $role->update($request->validated());
        $role->permissions()->sync($request->input('permissions', []));

        return (new RoleResource($role))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(Role $role)
    {
        abort_if(Gate::denies('role_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new RoleEditResource($role->load(['permissions'])),
            'meta' => [],
        ]);
    }

    public function destroy(Role $role)
    {
        abort_if(Gate::denies('role_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $protectedRoleIds = array_merge(config('system.auth.admin_role_id', []), config('system.auth.non_admin_role_id', []));
        if (in_array($role->id, (array) $protectedRoleIds)) {
            return response('This role is protected and cannot be deleted.', Response::HTTP_FORBIDDEN);
        }
        $role->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
