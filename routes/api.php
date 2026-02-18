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
 *  *  Last modified: 11/02/25, 6:36 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

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
| NOTE: Tenant-related routes are in routes/tenant_api.php
|
*/

Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'App\Http\Controllers\Admin', 'middleware' => []], function () {
    // Locales
    Route::get('locales/languages', 'LocalesController@languages')->name('locales.languages');
    Route::get('locales/messages', 'LocalesController@messages')->name('locales.messages');

    // Login with tenant context resolution (identify.tenant middleware sets tenant from subdomain)
    Route::post('login', 'Auth\LoginController')->middleware(['identify.tenant', 'throttle:5,1']);

    // Password Reset Routes
    Route::post('forgot_password', 'UsersApiController@passwordResetRequest')->name('password.email');
    Route::post('reset_password', 'Auth\ResetPasswordController@reset')->name('password.update');

    // Admin utility routes - should only be used via CLI or protected environment
    // These are intentionally removed from public API access for security.
    // Use `php artisan storage:link`, `php artisan optimize:clear`, and `php artisan db:seed` instead.

    // Test route
    Route::get('test', function () {
        return response()->json(['message' => 'test works']);
    });
});

/*
|--------------------------------------------------------------------------
| Tenant Registration Routes (Public)
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'v1/register', 'as' => 'api.register.'], function () {
    Route::get('check-subdomain', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'checkSubdomain'])->name('check-subdomain');
    Route::get('suggest-subdomain', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'suggestSubdomain'])->name('suggest-subdomain');
    Route::get('timezones', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'getTimezones'])->name('timezones');
    Route::get('currencies', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'getCurrencies'])->name('currencies');
    Route::post('/', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'register'])->name('register');
    Route::get('verify-email/{id}/{hash}', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'verifyEmail'])->name('verify-email');
    Route::post('resend-verification', [\App\Http\Controllers\Auth\TenantRegistrationController::class, 'resendVerification'])->name('resend-verification');
});

/*
|--------------------------------------------------------------------------
| Admin Plan Management Routes
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'v1/admin', 'as' => 'api.admin.', 'middleware' => ['jwt.admin.verify']], function () {
    Route::get('plans', [\App\Http\Controllers\Api\PlanController::class, 'adminIndex'])->name('plans.index');
    Route::post('plans', [\App\Http\Controllers\Api\PlanController::class, 'store'])->name('plans.store');
    Route::put('plans/{id}', [\App\Http\Controllers\Api\PlanController::class, 'update'])->name('plans.update');
    Route::delete('plans/{id}', [\App\Http\Controllers\Api\PlanController::class, 'destroy'])->name('plans.destroy');
});

/*
|--------------------------------------------------------------------------
| Authenticated Application Routes
|--------------------------------------------------------------------------
*/
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

    // Customers
    Route::resource('customers', 'CustomerApiController');
    Route::get('customers-csv', 'CustomerApiController@getCsv');

    // Orders
    Route::resource('orders', 'OrderApiController');
    Route::get('orders-csv', 'OrderApiController@getCsv');

    // Items (Master catalog)
    Route::resource('items', 'ItemApiController');
    Route::get('items-csv', 'ItemApiController@getCsv');

    // Services
    Route::resource('services', 'ServiceApiController');
    Route::get('services-csv', 'ServiceApiController@getCsv');

    // Delivery Scheduling
    Route::prefix('deliveries')->name('deliveries.')->group(function () {
        Route::get('today', 'DeliveryScheduleApiController@today')->name('today');
        Route::get('/', 'DeliveryScheduleApiController@index')->name('index');
        Route::get('{delivery}/edit', 'DeliveryScheduleApiController@edit')->name('edit');
        Route::post('/', 'DeliveryScheduleApiController@store')->name('store');
        Route::put('{delivery}', 'DeliveryScheduleApiController@update')->name('update');
        Route::delete('{delivery}', 'DeliveryScheduleApiController@destroy')->name('destroy');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('daily', 'ReportApiController@daily')->name('daily');
        Route::get('weekly', 'ReportApiController@weekly')->name('weekly');
        Route::get('monthly', 'ReportApiController@monthly')->name('monthly');
        Route::get('revenue-trend', 'ReportApiController@revenueTrend')->name('revenue-trend');
        Route::get('top-services', 'ReportApiController@topServices')->name('top-services');
        Route::get('top-customers', 'ReportApiController@topCustomers')->name('top-customers');
        Route::get('payment-methods', 'ReportApiController@paymentMethods')->name('payment-methods');
        Route::get('status-distribution', 'ReportApiController@statusDistribution')->name('status-distribution');
    });

    // Exports (Excel/PDF - all queued)
    Route::prefix('exports')->name('exports.')->group(function () {
        Route::post('{module}/{format}', 'ExportApiController@export')->name('queue');
        Route::get('download/{filename}', 'ExportApiController@download')->name('download-file');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', 'NotificationApiController@index')->name('index');
        Route::get('unread-count', 'NotificationApiController@unreadCount')->name('unread-count');
        Route::post('{id}/read', 'NotificationApiController@markAsRead')->name('read');
        Route::post('read-all', 'NotificationApiController@markAllAsRead')->name('read-all');
    });

    // Activity Logs
    Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
        Route::get('/', 'ActivityLogApiController@index')->name('index');
        Route::get('{id}', 'ActivityLogApiController@show')->name('show');
    });

    // POS Board
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('board', 'OrderBoardApiController@getBoardData')->name('board');
        Route::get('statistics', 'OrderBoardApiController@getStatistics')->name('statistics');
        Route::get('items', 'OrderBoardApiController@getItems')->name('items');
        Route::get('history', 'OrderBoardApiController@getHistory')->name('history');
        Route::get('customers/search', 'OrderBoardApiController@searchCustomers')->name('customers.search');
        Route::post('orders', 'OrderBoardApiController@store')->name('orders.store');
        Route::get('orders/{order}', 'OrderBoardApiController@show')->name('orders.show');
        Route::put('orders/{order}/status', 'OrderBoardApiController@updateStatus')->name('orders.status');
        Route::put('orders/{order}', 'OrderBoardApiController@update')->name('orders.update');
        Route::post('orders/{order}/pay', 'OrderBoardApiController@processPayment')->name('orders.pay');
        Route::delete('orders/{order}', 'OrderBoardApiController@destroy')->name('orders.destroy');
    });
});
