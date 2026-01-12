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
 *  *  Last modified: 07/01/25, 4:31 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Services;

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public static function getUserResponse($auth = null)
    {
        $auth = $auth ?: self::getDefaultGuard();
        $user = self::getUser($auth);

        // Set default language_id if not set
        if ($user->language_id === null) {
            $defaultLanguageId = config('system.defaults.language.id', 1);
            $user->language_id = $defaultLanguageId;
            $user->save();
        }

        $token = self::getTokenById($user->id, $auth);
        $newToken = $token;//auth($auth)->refresh($token);
        $user->api_token = $newToken;

        $payload = JWTAuth::manager()->getJWTProvider()->decode($token);

        $expiresIn = \Illuminate\Support\Carbon::parse($payload['exp'])->format(config('project.datetime_format'));

        $permissions = $user
            ->roles()
            ->with(['permissions:id,title'])
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('title')
            ->toArray();

        $roles = $user
            ->roles()
            ->select('id', 'title')
            ->get();

        $roleIds = $roles->pluck('id')->toArray();

        $user->role_ids = $roleIds;
        $user->roles = $roles;

        return [
            'status'    => 'success',
            'expires'   => $expiresIn,
            'user'      => $user,
            'abilities' => $permissions,
        ];
    }

    public static function getUser($auth = null)
    {
        $auth = $auth ?: self::getDefaultGuard();
        return auth($auth)->user();
    }

    public static function getTokenById($id, $auth = null)
    {
        $auth = $auth ?: self::getDefaultGuard();
        return auth($auth)->tokenById($id);
    }

    private static function getDefaultGuard()
    {
        return config('system.auth.admin', 'admin');
    }

    public static function getCompanyId()
    {
        $user = self::getUser();
        $settings = $user->settings;
        return $settings['company_id'] ?? null;
    }
}
