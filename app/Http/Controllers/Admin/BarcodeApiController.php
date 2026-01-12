<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BarcodeService;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BarcodeApiController extends Controller
{
    /**
     * Find product by barcode
     *
     * @param string $barcode
     * @return \Illuminate\Http\JsonResponse
     */
    public function findProductByBarcode($barcode)
    {
        $product = BarcodeService::findProductByBarcode($barcode);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Get primary unit (prioritize unit_01, fallback to unit_02)
        $unit = $product->unit_01 ? $product->unit_01->name : ($product->unit_02 ? $product->unit_02->name : null);

        return response()->json([
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'barcode' => $product->barcode,
            'rate' => $product->selling_price ?? 0,
            'unit' => $unit,
            'category' => $product->category?->name,
            'type' => $product->type,
            'has_inventory' => $product->has_inventory,
        ]);
    }

    /**
     * Generate barcode image
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generateBarcode(Request $request)
    {
        // Handle both GET and POST requests
        $data = $request->input('data') ?? $request->get('data');
        $type = $request->input('type') ?? $request->get('type', 'code128');
        $format = $request->input('format') ?? $request->get('format', 'svg');

        // Validate the input
        if (empty($data)) {
            return response()->json(['message' => 'Data parameter is required'], 400);
        }

        if (!in_array($type, ['code128', 'code39', 'ean13'])) {
            $type = 'code128';
        }

        if (!in_array($format, ['svg', 'png'])) {
            $format = 'svg';
        }

        try {
            $barcode = BarcodeService::generateBarcode($data, $type, $format);

            $contentType = $format === 'png' ? 'image/png' : 'image/svg+xml';

            return response($barcode)
                ->header('Content-Type', $contentType)
                ->header('Cache-Control', 'public, max-age=3600'); // Cache for 1 hour
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to generate barcode: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get barcode image for specific product
     *
     * @param int $productId
     * @return \Illuminate\Http\Response
     */
    public function getBarcodeImage($productId)
    {
        $product = Product::findOrFail($productId);

        if (!$product->barcode) {
            return response()->json(['message' => 'Product has no barcode'], 404);
        }

        try {
            $barcode = BarcodeService::generateBarcode(
                $product->barcode,
                $product->barcode_type ?? 'code128',
                'svg'
            );

            return response($barcode)
                ->header('Content-Type', 'image/svg+xml')
                ->header('Cache-Control', 'public, max-age=3600'); // Cache for 1 hour
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to generate barcode image'], 500);
        }
    }

    /**
     * Validate barcode format
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateBarcode(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
            'type' => 'nullable|string|in:code128,code39,ean13',
        ]);

        $isValid = BarcodeService::validateBarcodeFormat(
            $request->barcode,
            $request->type ?? 'code128'
        );

        return response()->json([
            'valid' => $isValid,
            'barcode' => $request->barcode,
            'type' => $request->type ?? 'code128',
        ]);
    }

    /**
     * Generate unique barcode
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateUniqueBarcode(Request $request)
    {
        $request->validate([
            'prefix' => 'nullable|string|max:10',
        ]);

        $barcode = BarcodeService::generateUniqueBarcode($request->prefix ?? '');

        return response()->json([
            'barcode' => $barcode,
            'prefix' => $request->prefix ?? '',
        ]);
    }
}