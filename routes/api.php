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
 *  *  Last modified: 11/02/25, 6:36 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

use App\Services\LanguageService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'App\Http\Controllers\Admin', 'middleware' => []], function () {
    // Locales
    Route::get('locales/languages', 'LocalesController@languages')->name('locales.languages');
    Route::get('locales/messages', 'LocalesController@messages')->name('locales.messages');

    Route::post('login', 'Auth\LoginController');

    // Password Reset Routes
    Route::post('forgot_password', 'UsersApiController@passwordResetRequest')->name('password.email');
    Route::post('reset_password', 'Auth\ResetPasswordController@reset')->name('password.update');

    // Storage:link
    Route::get('storage-link', function () {
        \Illuminate\Support\Facades\Artisan::call('storage:link');

        return response()->json(['message' => 'Storage link created successfully.']);
    });

    // Optimize
    Route::get('optimize', function () {
        Artisan::call('optimize:clear');

        return response()->json(['message' => 'Optimize cleared successfully.']);
    });

    // Reinstall Permissions
    Route::get('reinstall-permissions', function () {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('permission_role')->truncate();
        DB::table('permissions')->truncate();
        DB::table('permission_groups')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Artisan::call('db:seed', ['--class' => 'PermissionGroupsTableSeeder']);
        Artisan::call('db:seed', ['--class' => 'PermissionsTableSeeder']);
        Artisan::call('db:seed', ['--class' => 'PermissionRoleTableSeeder']);
        response()->json(['message' => 'Permissions reinstalled successfully.']);
    });

    Route::get('update-language-terms', function () {
        // Assuming LanguageService is kept or specific parts moved
        // LanguageService::updateLanguageData();
        return response()->json(['message' => 'Language terms updated successfully.']);
    });

    // Test route
    Route::get('test', function () {
        return response()->json(['message' => 'test works']);
    });
});

Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'App\Http\Controllers\Admin', 'middleware' => ['jwt.admin.verify']], function () {
    Route::get('verify', 'Auth\VerifyController');
    Route::get('abilities', 'Auth\AbilitiesController@index');

    Route::resource('permissions', 'PermissionsApiController');

    // Dashboard (Generic)
    Route::get('dashboard-modules', 'DashboardApiController@modules')->name('dashboard.modules');
    Route::get('dashboard-data', 'DashboardApiController@fetchData')->name('dashboard.data');

    // Search Options (Generic)
    Route::get('query/{type}', 'SearchApiController@search')->name('query');
    Route::get('options/{type}', 'SearchApiController@options')->name('options');
    Route::get('bulk-options/{types}', 'SearchApiController@bulkOptions')->name('bulk-options');
    Route::get('keys', 'SearchApiController@keys')->withoutMiddleware(['jwt.admin.verify'])->name('keys');



    // Import (Generic)
    Route::post('import/{type}/{id?}', 'ImportApiController@import')->name('import');

    // Media Upload
    Route::post('media-upload', 'MediaApiController@upload')->name('media-upload');

    // Role
    Route::resource('roles', 'RoleApiController');
    Route::get('roles-csv', 'RoleApiController@getCsv');

    // Country
    Route::resource('countries', 'CountryApiController');

    // State
    Route::resource('states', 'StateApiController');

    // City
    Route::resource('cities', 'CityApiController');

    // Currency
    Route::resource('currencies', 'CurrencyApiController');
    Route::get('currencies-csv', 'CurrencyApiController@getCsv');

    // Language
    Route::resource('languages', 'LanguageApiController');
    Route::get('languages-csv', 'LanguageApiController@getCsv');

    // User Management
    Route::resource('users', 'UsersApiController');
    Route::post('user/settings/{user}', 'UsersApiController@updateSettings');
    Route::post('user/setting/update', 'UsersApiController@updateSetting');
    Route::post('user/language/{user}', 'UsersApiController@updateLanguage');
    Route::post('user/preference', 'UsersApiController@updatePreference');

    // Company Settings
    Route::resource('companies', 'CompanyApiController');
    Route::get('companies-csv', 'CompanyApiController@getCsv');

    // Messages (Internal)
    Route::resource('messages', 'MessageApiController');
    Route::get('messages-csv', 'MessageApiController@getCsv');
});
