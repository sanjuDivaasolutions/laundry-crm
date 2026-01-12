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
 *  *  Last modified: 07/01/25, 4:10 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UpdateUserSettingsRequest;
use App\Http\Resources\Admin\UserResource;
use App\Models\Language;
use App\Models\Role;
use App\Models\User;
use App\Traits\SearchFilters;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Password;

class UsersApiController extends Controller
{
    protected $filterMethods = ['index'];

    protected $fields = ['name', 'email'];

    protected $filters = [
        ['request' => 'f_role_id', 'field' => 'roles.id', 'operator' => 'in'],
    ];

    use SearchFilters;

    public function index()
    {
        abort_if(Gate::denies('user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return UserResource::collection($this->getList());
    }

    private function getList()
    {
        return User::with(['roles', 'language'])
            ->advancedFilter();
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());
        $user->roles()->sync($request->input('roles.*.id', []));

        return (new UserResource($user))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(User $user)
    {
        abort_if(Gate::denies('user_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new UserResource($user->load(['roles']));
    }

    public function create(User $user)
    {
        abort_if(Gate::denies('user_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [
                'roles' => Role::get(['id', 'title']),
            ],
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());
        $user->roles()->sync($request->input('roles.*.id', []));

        return (new UserResource($user))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(User $user)
    {
        abort_if(Gate::denies('user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new UserResource($user->load(['roles'])),
        ]);
    }

    public function destroy(User $user)
    {
        abort_if(Gate::denies('user_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if(in_array(auth()->id(), config('system.auth.protected_user_id', [])), Response::HTTP_FORBIDDEN, 'You are not allowed to delete this user.');

        $user->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function fetch()
    {

        $user = User::with(['roles:id,title'])->find(auth()->id())->toArray();
        $user['type'] = @$user['roles'][0]['title'] ?? 'Admin';

        return $user;

    }

    public function updateSettings(UpdateUserSettingsRequest $request, User $user)
    {

        $validated = $request->validated();
        $validated['settings'] = json_decode($validated['settings'], true);
        $user->update($validated);

        $user_id = $user->id;
        $updated = User::find($user_id);

        return (new UserResource($updated))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function updateSetting(Request $request)
    {
        $user = auth()->user();
        $userSettings = $user->settings ?? [];
        $key = $request->input('key');
        $value = $request->input('value');
        $userSettings[$key] = $value;

        $user->settings = $userSettings;
        $user->save();

        return okResponse('Setting was updated successfully.');
    }

    public function updateLanguage(User $user)
    {
        $locale = request()->input('locale');
        $language = Language::query()->where('locale', $locale)->first();
        if ($language) {
            $user->update(['language_id' => $language->id]);

            return okResponse('Language was updated successfully.');
        }

        return errorResponse('Language was not found.');
    }

    public function updatePreference(Request $request)
    {
        $user = auth()->user();
        $key = $request->input('key');
        $value = $request->input('value');

        if ($key === 'language') {
            $language = Language::query()
                ->where('locale', $value)
                ->where('active', 1)
                ->first();

            if ($language) {
                $user->update(['language_id' => $language->id]);

                return okResponse([
                    'message' => 'Language preference updated successfully.',
                    'language' => $language,
                ]);
            }

            return errorResponse('Language not found or inactive.', 404);
        }

        return errorResponse('Invalid preference key.', 400);
    }

    public function passwordResetRequest(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'A reset link has been sent to the email address.',
                'status' => 'success',
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => 'Unable to send reset link. Please check your email address and try again.',
            'errors' => ['email' => [__($status)]],
            'status' => 'error',
        ], Response::HTTP_BAD_REQUEST);
    }
}
