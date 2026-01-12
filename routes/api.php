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

use App\Services\InventoryService;
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

    //Storage:link
    Route::get('storage-link', function () {
        \Illuminate\Support\Facades\Artisan::call('storage:link');
        dd('Storage link created successfully.');
    });

    //Optimize
    Route::get('optimize', function () {
        Artisan::call('optimize:clear');
        dd('Optimize successfully.');
    });

    //Reinstall Permissions
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
        LanguageService::updateLanguageData();
        return response()->json(['message' => 'Language terms updated successfully.']);
    });

    Route::get('fix-products-opening-stock', function () {
        $products = \App\Models\Product::query()->onlyProducts()->get();
        $controller = new \App\Http\Controllers\Admin\ProductApiController();
        foreach ($products as $product) {
            $controller->fixOpeningStock($product);
            InventoryService::updateProductStockAcrossAllWarehouses($product->id);
        }
        return response()->json(['message' => 'Opening stock fixed successfully.']);
    });

    Route::get('fix-broken-inventory', function () {
        $inventory = \App\Models\ProductInventory::query()
            ->where('reason', '!=', 'opening')
            ->whereDoesntHave('inventoryable')
            ->get();

        dd($inventory->pluck('id'));
    });

    Route::get('update-si-taxes', function () {
        \App\Services\DatabaseService::executeTransaction(function () {
            \App\Services\InvoiceService::upgradeSITaxes();
            return response()->json(['message' => 'Sales invoice taxes updated successfully.']);
        });
    });

    Route::get('update-inward-taxes', function () {
        \App\Services\DatabaseService::executeTransaction(function () {
            \App\Services\InvoiceService::upgradeInwardTaxes();
            return response()->json(['message' => 'Inward taxes updated successfully.']);
        });
    });

    Route::get('update-expense-taxes', function () {
        \App\Services\DatabaseService::executeTransaction(function () {
            $expenses = \App\Models\Expense::query()
                ->get();

            foreach ($expenses as $expense) {
                if (!$expense->state_id) {
                    $expense->state_id = 1;
                    $expense->save();
                }
                \App\Services\InvoiceService::setupTaxes($expense);
            }

            return response()->json(['message' => 'Expense taxes updated successfully.']);
        });
    });

    Route::get('install-canada-taxation', function () {

        //Truncate existing data
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // Truncate taxation-related tables
        DB::table('tax_classes')->truncate();
        DB::table('tax_rates')->truncate();
        DB::table('tax_rules')->truncate();
        DB::table('order_tax_details')->truncate();
        DB::table('geo_zones')->truncate();
        DB::table('geo_zone_state')->truncate();
        DB::table('cities')->truncate();
        DB::table('states')->truncate();
        DB::table('countries')->truncate();

        // Enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        Artisan::call('db:seed', ['--class' => \Database\Seeders\CanadaTaxation\CanadaTaxationSeeder::class]);
        return response()->json(['message' => 'Canada taxation installed successfully.']);
    });

    Route::post('newsletter-subscribe', 'SubscriberApiController@subscribe')->name('newsletter-subscribe-post');

    // Test route
    Route::get('test', function() { return response()->json(['message' => 'test works']); });

    // Public barcode routes (for image generation)
    Route::get('barcodes/generate', 'BarcodeApiController@generateBarcode')->name('barcodes-generate-public');
    Route::get('barcodes/{productId}/image', 'BarcodeApiController@getBarcodeImage')->name('barcodes-image-public');

});

Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'App\Http\Controllers\Admin', 'middleware' => ['jwt.admin.verify']], function () {
    Route::get('verify', 'Auth\VerifyController');

    Route::get('abilities', 'Auth\AbilitiesController@index');

    Route::resource('permissions', 'PermissionsApiController');

    Route::get('query/{type}', 'SearchApiController@search')->name('query');
    Route::get('options/{type}', 'SearchApiController@options')->name('options');
    Route::get('bulk-options/{types}', 'SearchApiController@bulkOptions')->name('bulk-options');
    Route::get('keys', 'SearchApiController@keys')->withoutMiddleware(['jwt.admin.verify'])->name('keys');

    Route::get('dashboard-modules', 'DashboardApiController@index')->name('dashboard-modules');
    Route::get('dashboard-data', 'DashboardApiController@fetchData')->name('dashboard-data');
    Route::get('dashboard-cards', 'DashboardApiController@cards')->name('dashboard-cards');

    Route::post('import/{type}/{id?}', 'ImportApiController@import')->name('import');

    Route::post('media-upload', 'MediaApiController@upload')->name('media-upload');

    Route::get('reports/{type}', 'ReportApiController@index')->name('reports');
    Route::get('reports/product-sale-details/{productId}', 'ReportApiController@getProductSaleDetails')->name('reports-product-details');
    Route::get('reports/product-inward-details/{productId}', 'ReportApiController@getProductInwardDetails')->name('reports-product-inward-details');
    Route::get('reports/agent-commission-details/{agentId}', 'ReportApiController@getAgentCommissionDetails')->name('reports-agent-commission-details');
    Route::get('reports/csv/{type}', 'ReportApiController@getCsv')->name('reports-csv');
    Route::get('reports/pdf/{type}', 'ReportApiController@getPdf')->name('reports-pdf');
    
    // Enhanced Reports
    // Route::get('reports/profit-loss', 'ReportController@profitLoss');
    // Route::get('reports/sales/by-product', 'ReportController@salesByProduct');
    // Route::get('reports/sales/by-month', 'ReportController@salesByMonth');
    // Route::get('reports/stock/summary', 'ReportController@stockSummary');
    // Route::get('reports/commissions/summary', 'ReportController@commissionSummary');
    // Route::get('reports/dashboard', 'ReportController@dashboard');

    // Barcode routes
    Route::get('products/barcode/{barcode}', 'BarcodeApiController@findProductByBarcode')->name('products-by-barcode');
    Route::post('barcodes/generate', 'BarcodeApiController@generateBarcode')->name('barcodes-generate');
    Route::get('barcodes/generate', 'BarcodeApiController@generateBarcode')->name('barcodes-generate-get');
    Route::get('barcodes/{productId}/image', 'BarcodeApiController@getBarcodeImage')->name('barcodes-image');
    Route::post('barcodes/validate', 'BarcodeApiController@validateBarcode')->name('barcodes-validate');
    Route::post('barcodes/generate-unique', 'BarcodeApiController@generateUniqueBarcode')->name('barcodes-generate-unique');

    // POS Routes
    Route::prefix('pos')->name('pos.')->group(function() {
        Route::get('products', 'PosController@getProducts')->name('products');
        Route::get('categories', 'PosController@getCategories')->name('categories');
        Route::get('customers', 'PosController@getCustomers')->name('customers');
        Route::post('customers', 'PosController@createCustomer')->name('create-customer');
        Route::get('payment-modes', 'PosController@getPaymentModes')->name('payment-modes');
        Route::get('products/{productId}/stock', 'PosController@getProductStock')->name('product-stock');
        Route::post('sales', 'PosController@processSale')->name('process-sale');
        Route::get('sales/summary', 'PosController@getSalesSummary')->name('sales-summary');
        Route::get('barcode/{barcode}', 'PosController@searchByBarcode')->name('barcode-search');
        Route::get('orders', 'PosController@getOrders')->name('orders');
        Route::get('orders/{id}', 'PosController@getOrder')->name('order-details');
        
        // Session routes
        Route::get('session/active', 'PosController@getActiveSession')->name('session-active');
        Route::get('session/orders', 'PosController@getSessionOrders')->name('session-orders');
        Route::post('session/open', 'PosController@openSession')->name('session-open');
        Route::post('session/close', 'PosController@closeSession')->name('session-close');
        Route::get('session/{id}', 'PosController@getSession')->name('session-details');
        Route::get('sessions', 'PosController@getSessions')->name('sessions');
    });

    Route::resource('users', 'UsersApiController');
    Route::post('user/settings/{user}', 'UsersApiController@updateSettings');
    Route::post('user/setting/update', 'UsersApiController@updateSetting');
    Route::post('user/language/{user}', 'UsersApiController@updateLanguage');
    Route::post('user/preference', 'UsersApiController@updatePreference');

    //Role
    Route::resource('roles', 'RoleApiController');
    //Route::get('roles-pdf', 'RoleApiController@getPdf');
    Route::get('roles-csv', 'RoleApiController@getCsv');

    // Country
    Route::resource('countries', 'CountryApiController');

    // State
    Route::resource('states', 'StateApiController');

    // City
    Route::resource('cities', 'CityApiController');

    //Language
    Route::resource('languages', 'LanguageApiController');
    //Route::get('languages-pdf', 'LanguageApiController@getPdf');
    Route::get('languages-csv', 'LanguageApiController@getCsv');
});

Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'App\Http\Controllers\Admin', 'middleware' => ['jwt.admin.verify']], function () {

    //Unit
    Route::resource('units', 'UnitApiController');
    //Route::get('units-pdf', 'UnitApiController@getPdf');
    Route::get('units-csv', 'UnitApiController@getCsv');

    //Warehouse
    Route::resource('warehouses', 'WarehouseApiController');
    //Route::get('warehouses-pdf', 'WarehouseApiController@getPdf');
    Route::get('warehouses-csv', 'WarehouseApiController@getCsv');

    //Shelf
    Route::resource('shelves', 'ShelfApiController');
    //Route::get('shelves-pdf', 'ShelfApiController@getPdf');
    Route::get('shelves-csv', 'ShelfApiController@getCsv');


    //Buyer
    Route::resource('buyers', 'BuyerApiController');
    //Route::get('buyers-pdf', 'BuyerApiController@getPdf');
    Route::get('buyers-csv', 'BuyerApiController@getCsv');

    //Currency
    Route::resource('currencies', 'CurrencyApiController');
    //Route::get('currencies-pdf', 'CurrencyApiController@getPdf');
    Route::get('currencies-csv', 'CurrencyApiController@getCsv');
    //Payment Term
    Route::resource('payment-terms', 'PaymentTermApiController');
    //Route::get('payment-terms-pdf', 'PaymentTermApiController@getPdf');
    Route::get('payment-terms-csv', 'PaymentTermApiController@getCsv');

    //Supplier
    Route::resource('suppliers', 'SupplierApiController');
    Route::resource('agents', 'SupplierApiController');
    //Route::get('suppliers-pdf', 'SupplierApiController@getPdf');
    Route::get('suppliers-csv', 'SupplierApiController@getCsv');
    Route::get('agents-csv', 'SupplierApiController@getCsv');
    
    // Enhanced Agent Management
    // Route::get('agents/{agent}/commission-summary', 'AgentApiController@getCommissionSummary');
    // Route::post('agents/commissions/approve', 'AgentApiController@approveCommissions');
    // Route::post('agents/commissions/mark-paid', 'AgentApiController@markCommissionsAsPaid');

    //Category
    Route::resource('categories', 'CategoryApiController');
    //Route::get('categories-pdf', 'CategoryApiController@getPdf');
    Route::get('categories-csv', 'CategoryApiController@getCsv');

    // Product
    Route::post('products/media', 'ProductApiController@storeMedia')->name('products.storeMedia');
    Route::resource('products', 'ProductApiController');
    //Route::get('products-pdf', 'ProductApiController@getPdf');
    Route::get('products-csv', 'ProductApiController@getCsv');
    Route::get('product-transactions/{product}', 'ProductApiController@getInventoryList');

    //Service
    //Route::resource('services', 'ServiceApiController');
    Route::get('services', 'ServiceApiController@index');
    Route::get('services/create', 'ServiceApiController@create');
    Route::post('services', 'ServiceApiController@store');
    Route::get('services/{product}', 'ServiceApiController@show');
    Route::get('services/{product}/edit', 'ServiceApiController@edit');
    Route::put('services/{product}', 'ServiceApiController@update');
    Route::delete('services/{product}', 'ServiceApiController@destroy');
    //Route::get('services-pdf', 'ServiceApiController@getPdf');
    Route::get('services-csv', 'ServiceApiController@getCsv');

    //Feature
    Route::resource('features', 'FeatureApiController');
    //Route::get('features-pdf', 'FeatureApiController@getPdf');
    Route::get('features-csv', 'FeatureApiController@getCsv');

    //Purchase Order
    Route::resource('purchase-orders', 'PurchaseOrderApiController');
    //Route::get('purchase-orders-pdf', 'PurchaseOrderApiController@getPdf');
    Route::get('purchase-orders-csv', 'PurchaseOrderApiController@getCsv');
    Route::get('purchase-orders-single-pdf/{purchaseOrder}', 'PurchaseOrderApiController@getSinglePdf');
    Route::get('purchase-order-invoice/{purchaseOrder}', 'PurchaseOrderApiController@getPoInvoice');

    //Purchase Invoice
    Route::resource('purchase-invoices', 'PurchaseInvoiceApiController');
    //Route::get('purchase-invoices-pdf', 'PurchaseInvoiceApiController@getPdf');
    Route::get('purchase-invoices-csv', 'PurchaseInvoiceApiController@getCsv');
    Route::get('purchase-invoices-single-pdf/{purchaseInvoice}', 'PurchaseInvoiceApiController@getSinglePdf');

    //Inward
    Route::resource('inwards', 'InwardApiController');
    //Route::get('inwards-pdf', 'InwardApiController@getPdf');
    Route::get('inwards-csv', 'InwardApiController@getCsv');
    Route::get('inwards-single-pdf/{inward}', 'InwardApiController@getSinglePdf');

    //Sale Order
    Route::resource('sales-orders', 'SalesOrderApiController');
    Route::get('sales-orders-single-pdf/{salesOrder}', 'SalesOrderApiController@getSinglePdf');
    //Route::get('sale-orders-pdf', 'SaleOrderApiController@getPdf');
    Route::get('sales-orders-csv', 'SalesOrderApiController@getCsv');
    Route::get('sales-order-invoice/{salesOrder}', 'SalesOrderApiController@getSoInvoice');
    
    // Enhanced Order Management
    Route::post('sales-orders/{salesOrder}/convert-to-invoice', 'SalesOrderApiController@convertToInvoice');
    Route::patch('sales-orders/{salesOrder}/status', 'SalesOrderApiController@updateStatus');
    Route::get('sales-orders/statistics', 'SalesOrderApiController@getOrderStatistics');


    //Sales Invoice
    Route::resource('sales-invoices', 'SalesInvoiceApiController');
    Route::match(['post', 'patch'], 'sales-invoices/{salesInvoice}/payment-status', 'SalesInvoiceApiController@updatePaymentStatus');
    //Route::get('sales-invoices-pdf', 'SalesInvoiceApiController@getPdf');
    Route::get('sales-invoices-csv', 'SalesInvoiceApiController@getCsv');
    Route::get('sales-invoices-single-pdf/{salesInvoice}', 'SalesInvoiceApiController@getSinglePdf');

    //Service Invoice
    //Route::resource('service-invoices', 'ServiceInvoiceApiController');
    Route::get('service-invoices', 'ServiceInvoiceApiController@index');
    Route::post('service-invoices', 'ServiceInvoiceApiController@store');
    Route::get('service-invoices/create', 'ServiceInvoiceApiController@create');
    Route::get('service-invoices/{salesInvoice}', 'ServiceInvoiceApiController@show');
    Route::get('service-invoices/{salesInvoice}/edit', 'ServiceInvoiceApiController@edit');
    Route::put('service-invoices/{salesInvoice}', 'ServiceInvoiceApiController@update');
    Route::delete('service-invoices/{salesInvoice}', 'ServiceInvoiceApiController@destroy');

    //Route::get('service-invoices-pdf', 'ServiceInvoiceApiController@getPdf');
    Route::get('service-invoices-csv', 'ServiceInvoiceApiController@getCsv');

    //Package
    Route::resource('packages', 'PackageApiController');
    //Route::get('packages-pdf', 'PackageApiController@getPdf');
    Route::get('packages-csv', 'PackageApiController@getCsv');
    Route::get('packages-single-pdf/{package}', 'PackageApiController@getSinglePdf');
    Route::get('sales-invoice-items/{salesInvoice}', 'PackageApiController@getSalesInvoiceItems');
    //Shipment
    Route::resource('shipments', 'ShipmentApiController');
    //Route::get('shipments-pdf', 'ShipmentApiController@getPdf');
    Route::get('shipments-csv', 'ShipmentApiController@getCsv');

    //Payment
    Route::resource('payments', 'PaymentApiController');
    Route::get('payments/{type}/{id}', 'PaymentApiController@getPayments');
    //Route::get('payments-pdf', 'PaymentApiController@getPdf');
    Route::get('payments-csv', 'PaymentApiController@getCsv');

    //Sales Return
    Route::resource('sales-returns', 'SalesReturnApiController');
    //Route::get('sales-returns-pdf', 'SalesReturnApiController@getPdf');
    Route::get('sales-returns-csv', 'SalesReturnApiController@getCsv');

    //Package Type
    Route::resource('package-types', 'PackageTypeApiController');
    //Route::get('package-types-pdf', 'PackageTypeApiController@getPdf');
    Route::get('package-types-csv', 'PackageTypeApiController@getCsv');

    //Shipment Mode
    Route::resource('shipment-modes', 'ShipmentModeApiController');
    //Route::get('shipment-modes-pdf', 'ShipmentModeApiController@getPdf');
    Route::get('shipment-modes-csv', 'ShipmentModeApiController@getCsv');

    //Company
    Route::resource('companies', 'CompanyApiController');
    //Route::get('companies-pdf', 'CompanyApiController@getPdf');
    Route::get('companies-csv', 'CompanyApiController@getCsv');

    //Contract
    Route::resource('contracts', 'ContractApiController');
    //Route::get('contracts-pdf', 'ContractApiController@getPdf');
    Route::get('contracts-csv', 'ContractApiController@getCsv');
    Route::post('contracts-send-payment-link/{contract}', 'ContractApiController@sendPaymentLink');

    //Contract Invoices
    Route::get('contract-invoices/{contract}', 'ContractInvoiceApiController@index');
    Route::delete('contract-invoices/{salesInvoice}', 'ContractInvoiceApiController@destroy');

    Route::get('contract-invoice-generate/{contract}', 'ContractInvoiceApiController@generate');

    //Contract Term
    Route::resource('contract-terms', 'ContractTermApiController');
    //Route::get('contract-terms-pdf', 'ContractTermApiController@getPdf');
    Route::get('contract-terms-csv', 'ContractTermApiController@getCsv');

    //Expense Type
    Route::resource('expense-types', 'ExpenseTypeApiController');
    //Route::get('expense-types-pdf', 'ExpenseTypeApiController@getPdf');
    Route::get('expense-types-csv', 'ExpenseTypeApiController@getCsv');

    //Payment Mode
    Route::resource('payment-modes', 'PaymentModeApiController');
    //Route::get('payment-modes-pdf', 'PaymentModeApiController@getPdf');
    Route::get('payment-modes-csv', 'PaymentModeApiController@getCsv');

    //Expense
    Route::resource('expenses', 'ExpenseApiController');
    //Route::get('expenses-pdf', 'ExpenseApiController@getPdf');
    Route::get('expenses-csv', 'ExpenseApiController@getCsv');

    //Quotation
    Route::resource('quotations', 'QuotationApiController');
    //Route::get('quotations-pdf', 'QuotationApiController@getPdf');
    Route::get('quotations-csv', 'QuotationApiController@getCsv');
    Route::get('quotations-single-pdf/{quotation}', 'QuotationApiController@getSinglePdf');

    Route::post('quotations-mark-status/{quotation}', 'QuotationApiController@markStatus');
    Route::post('quotations-convert-to-sales-order/{quotation}', 'QuotationApiController@convertToSalesOrder');

    //Package
    Route::resource('packages', 'PackageApiController');
    //Route::get('packages-pdf', 'PackageApiController@getPdf');
    Route::get('packages-csv', 'PackageApiController@getCsv');

    //Adjustment
    Route::resource('inventory-adjustments', 'InventoryAdjustmentApiController');
    //Route::get('inventory-adjustments-pdf', 'InventoryAdjustmentApiController@getPdf');
    Route::get('inventory-adjustments-csv', 'InventoryAdjustmentApiController@getCsv');

    //Message
    Route::resource('messages', 'MessageApiController');
    //Route::get('messages-pdf', 'MessageApiController@getPdf');
    Route::get('messages-csv', 'MessageApiController@getCsv');

    //Subscriber
    Route::resource('subscribers', 'SubscriberApiController');
    //Route::get('subscribers-pdf', 'SubscriberApiController@getPdf');
    Route::get('subscribers-csv', 'SubscriberApiController@getCsv');

});

Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'App\Http\Controllers\Admin', 'middleware' => []], function () {
    Route::post('/stripe-webhook', function (Request $request) {
        \App\Services\StripeService::handleWebhook($request);
    })->name('stripe-webhook');
});
