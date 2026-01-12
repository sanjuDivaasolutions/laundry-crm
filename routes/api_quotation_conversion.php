<?php

use App\Http\Controllers\Api\QuotationConversionController;
use Illuminate\Support\Facades\Route;

// Quotation Conversion Routes
Route::prefix('quotations/{quotation}')->group(function () {
    Route::post('/convert-to-sales-order', [QuotationConversionController::class, 'convert'])
        ->name('quotations.convert-to-sales-order');
    
    Route::get('/conversion-preview', [QuotationConversionController::class, 'preview'])
        ->name('quotations.conversion-preview');
});