<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Barcode Type
    |--------------------------------------------------------------------------
    |
    | This value determines the default barcode type that will be used when
    | generating new barcodes. Supported types: code128, code39, ean13
    |
    */
    'default_type' => env('BARCODE_DEFAULT_TYPE', 'code128'),

    /*
    |--------------------------------------------------------------------------
    | Auto Generate Barcodes
    |--------------------------------------------------------------------------
    |
    | When set to true, barcodes will be automatically generated for new
    | products that don't have a barcode assigned.
    |
    */
    'auto_generate' => env('BARCODE_AUTO_GENERATE', false),

    /*
    |--------------------------------------------------------------------------
    | Barcode Prefix
    |--------------------------------------------------------------------------
    |
    | Optional prefix to add to automatically generated barcodes.
    | This can help distinguish your products from others.
    |
    */
    'prefix' => env('BARCODE_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Barcode Length
    |--------------------------------------------------------------------------
    |
    | The length of the numeric part of auto-generated barcodes.
    | Total length will be prefix + numeric part.
    |
    */
    'length' => env('BARCODE_LENGTH', 8),

    /*
    |--------------------------------------------------------------------------
    | Supported Barcode Types
    |--------------------------------------------------------------------------
    |
    | List of supported barcode types with their configurations.
    |
    */
    'types' => [
        'code128' => [
            'name' => 'Code 128',
            'description' => 'High-density linear barcode, most versatile',
            'max_length' => 48,
            'charset' => 'ASCII 0-127',
        ],
        'code39' => [
            'name' => 'Code 39',
            'description' => 'Alphanumeric barcode, widely supported',
            'max_length' => 43,
            'charset' => 'A-Z, 0-9, and symbols (- . $ / + % space)',
        ],
        'ean13' => [
            'name' => 'EAN-13',
            'description' => 'Standard retail barcode (13 digits)',
            'max_length' => 13,
            'charset' => 'Numeric only (0-9)',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Scanner Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for barcode scanning functionality.
    |
    */
    'scanner' => [
        'camera_constraints' => [
            'width' => 320,
            'height' => 240,
            'facing_mode' => 'environment', // Use back camera by default
        ],
        'scan_timeout' => 500, // Milliseconds to wait before processing manual input
        'audio_feedback' => true, // Enable audio feedback for scans
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Generation Settings
    |--------------------------------------------------------------------------
    |
    | Settings for barcode image generation.
    |
    */
    'image' => [
        'default_format' => 'svg', // svg or png
        'cache_duration' => 3600, // Cache images for 1 hour
        'width' => 200,
        'height' => 50,
    ],
];
