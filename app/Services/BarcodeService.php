<?php

namespace App\Services;

use App\Models\Product;
// Temporarily commented out until package is installed
// use Picqer\Barcode\BarcodeGeneratorSVG;
// use Picqer\Barcode\BarcodeGeneratorPNG;

class BarcodeService
{
    /**
     * Generate barcode image
     *
     * @param string $data
     * @param string $type
     * @param string $format
     * @return string
     */
    public static function generateBarcode($data, $type = 'code128', $format = 'svg')
    {
        // Temporary implementation until Picqer package is installed
        if ($format === 'svg') {
            return self::generateSimpleSVGBarcode($data, $type);
        }

        // For now, return a placeholder for PNG
        return 'PNG barcode generation not available yet';
    }

    /**
     * Generate a simple SVG barcode representation
     * This is a temporary solution until the Picqer package is installed
     */
    private static function generateSimpleSVGBarcode($data, $type)
    {
        $width = 200;
        $height = 50;
        $barWidth = 2;

        // Create simple SVG with text representation
        $svg = '<svg width="' . $width . '" height="' . $height . '" xmlns="http://www.w3.org/2000/svg">';
        $svg .= '<rect width="100%" height="100%" fill="white"/>';

        // Draw simple bars based on data length
        $x = 10;
        for ($i = 0; $i < strlen($data); $i++) {
            $barHeight = 30;
            $svg .= '<rect x="' . $x . '" y="5" width="' . $barWidth . '" height="' . $barHeight . '" fill="black"/>';
            $x += $barWidth + 1;
        }

        // Add text below
        $svg .= '<text x="' . ($width / 2) . '" y="' . ($height - 5) . '" text-anchor="middle" font-family="monospace" font-size="10">' . htmlspecialchars($data) . '</text>';
        $svg .= '</svg>';

        return $svg;
    }

    /**
     * Find product by barcode
     *
     * @param string $barcode
     * @return Product|null
     */
    public static function findProductByBarcode($barcode)
    {
        return Product::where('barcode', $barcode)
            ->company()
            ->with(['unit_01:id,name', 'unit_02:id,name', 'category:id,name'])
            ->first();
    }

    /**
     * Generate unique barcode
     *
     * @param string $prefix
     * @return string
     */
    public static function generateUniqueBarcode($prefix = '')
    {
        do {
            $barcode = $prefix . str_pad(random_int(1, 99999999), 8, '0', STR_PAD_LEFT);
        } while (Product::where('barcode', $barcode)->exists());

        return $barcode;
    }

    /**
     * Validate barcode format
     *
     * @param string $barcode
     * @param string $type
     * @return bool
     */
    public static function validateBarcodeFormat($barcode, $type = 'code128')
    {
        return match($type) {
            'code39' => preg_match('/^[A-Z0-9\-\.\$\/\+\%\s]+$/', $barcode),
            'ean13' => preg_match('/^\d{13}$/', $barcode) && self::validateEAN13Checksum($barcode),
            'code128' => strlen($barcode) >= 1 && strlen($barcode) <= 48,
            default => false,
        };
    }

    /**
     * Validate EAN-13 checksum
     *
     * @param string $barcode
     * @return bool
     */
    private static function validateEAN13Checksum($barcode)
    {
        if (strlen($barcode) !== 13) {
            return false;
        }

        $checkDigit = intval(substr($barcode, -1));
        $barcode = substr($barcode, 0, 12);

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = intval($barcode[$i]);
            $sum += ($i % 2 === 0) ? $digit : $digit * 3;
        }

        $calculatedCheckDigit = (10 - ($sum % 10)) % 10;

        return $checkDigit === $calculatedCheckDigit;
    }

    /**
     * Auto-generate barcode for product
     *
     * @param Product $product
     * @return string|null
     */
    public static function autoGenerateForProduct(Product $product)
    {
        if ($product->barcode) {
            return $product->barcode;
        }

        $autoGenerate = config('barcode.auto_generate', false);
        if (!$autoGenerate) {
            return null;
        }

        $prefix = config('barcode.prefix', '');
        $barcode = self::generateUniqueBarcode($prefix);

        $product->update(['barcode' => $barcode]);

        return $barcode;
    }
}