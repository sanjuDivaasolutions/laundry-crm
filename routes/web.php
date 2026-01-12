<?php

/*
 *
 *  *  Copyright (c) 2024 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 17/10/24, 2:31 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

use App\Models\Contract;
use App\Services\StripeService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/admin', function () {
    return view('admin');
});

Route::get('/', function () {
    // return view('welcome');
    return redirect('/admin');
});

Route::get('/subscription-checkout/{contract}', function (Contract $contract) {
    if (! StripeService::isValidContract($contract)) {
        return redirect()->route('contract-invalid');
    }

    return StripeService::createContractSubscription($contract);
})->name('subscription-checkout');

Route::get('/contract-success', function () {
    return view('front-end.contract-success');
})->name('contract-success');

Route::get('/contract-cancel', function () {
    $message = 'Failed! Your subscription could not be activated.';

    return view('front-end.contract-failed', compact('message'));
})->name('contract-cancel');

Route::get('/contract-invalid', function () {
    $message = 'Failed! Your subscription is not stripe managed.';

    return view('front-end.contract-failed', compact('message'));
})->name('contract-invalid');

Route::get('/newsletter-subscribe/{company}', 'App\Http\Controllers\Web\SubscribeController@index')->name('newsletter-subscribe');

Route::get('/reset-password', function () {
    return view('admin');
})->name('password.reset');
