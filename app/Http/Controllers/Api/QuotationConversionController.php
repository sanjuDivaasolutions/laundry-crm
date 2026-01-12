<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Quotation\ConvertQuotationRequest;
use App\Http\Resources\SalesOrderResource;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Services\QuotationConversionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QuotationConversionController extends Controller
{
    protected QuotationConversionService $conversionService;

    public function __construct(QuotationConversionService $conversionService)
    {
        $this->conversionService = $conversionService;
    }

    /**
     * Convert quotation to sales order
     *
     * @param ConvertQuotationRequest $request
     * @param Quotation $quotation
     * @return JsonResponse
     */
    public function convert(ConvertQuotationRequest $request, Quotation $quotation): JsonResponse
    {
        try {
            $salesOrder = $this->conversionService->convertToSalesOrder(
                $quotation,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Quotation converted to sales order successfully',
                'data' => new SalesOrderResource($salesOrder)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to convert quotation to sales order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get preview of sales order data before conversion
     *
     * @param Quotation $quotation
     * @return JsonResponse
     */
    public function preview(Quotation $quotation): JsonResponse
    {
        try {
            $preview = $this->conversionService->previewSalesOrder($quotation);

            return response()->json([
                'success' => true,
                'data' => $preview
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate preview',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}