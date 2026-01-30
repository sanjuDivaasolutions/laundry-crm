<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Service;
use App\Models\ServicePrice;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = 1; // Default tenant

        // Get services
        $washFoldService = Service::where('tenant_id', $tenantId)->where('name', 'Wash & Fold')->first();
        $dryCleaningService = Service::where('tenant_id', $tenantId)->where('name', 'Dry Cleaning')->first();
        $ironingService = Service::where('tenant_id', $tenantId)->where('name', 'Ironing')->first();
        $stainRemovalService = Service::where('tenant_id', $tenantId)->where('name', 'Stain Removal')->first();

        // Items data
        $items = [
            // Clothing items
            ['tenant_id' => $tenantId, 'name' => 'T-Shirt', 'code' => 'ITM-1001', 'description' => 'Standard cotton t-shirt', 'price' => 5.00, 'display_order' => 1, 'is_active' => true],
            ['tenant_id' => $tenantId, 'name' => 'Jeans', 'code' => 'ITM-1002', 'description' => 'Denim jeans', 'price' => 8.00, 'display_order' => 2, 'is_active' => true],
            ['tenant_id' => $tenantId, 'name' => 'Bedsheet', 'code' => 'ITM-1003', 'description' => 'Single/Double bedsheet', 'price' => 12.00, 'display_order' => 3, 'is_active' => true],
            ['tenant_id' => $tenantId, 'name' => 'Towel', 'code' => 'ITM-1004', 'description' => 'Bath towel', 'price' => 6.00, 'display_order' => 4, 'is_active' => true],

            // Formal items
            ['tenant_id' => $tenantId, 'name' => 'Suit (2-piece)', 'code' => 'ITM-2001', 'description' => 'Two-piece business suit', 'price' => 25.00, 'display_order' => 5, 'is_active' => true],
            ['tenant_id' => $tenantId, 'name' => 'Dress Shirt', 'code' => 'ITM-2002', 'description' => 'Formal dress shirt', 'price' => 8.00, 'display_order' => 6, 'is_active' => true],
            ['tenant_id' => $tenantId, 'name' => 'Blazer', 'code' => 'ITM-2003', 'description' => 'Formal blazer/jacket', 'price' => 15.00, 'display_order' => 7, 'is_active' => true],
            ['tenant_id' => $tenantId, 'name' => 'Trousers', 'code' => 'ITM-2004', 'description' => 'Formal trousers', 'price' => 10.00, 'display_order' => 8, 'is_active' => true],
            ['tenant_id' => $tenantId, 'name' => 'Evening Gown', 'code' => 'ITM-2005', 'description' => 'Formal evening gown', 'price' => 30.00, 'display_order' => 9, 'is_active' => true],

            // Other items
            ['tenant_id' => $tenantId, 'name' => 'Casual Shirt', 'code' => 'ITM-3001', 'description' => 'Casual button-up shirt', 'price' => 5.00, 'display_order' => 10, 'is_active' => true],
            ['tenant_id' => $tenantId, 'name' => 'Dress Pants', 'code' => 'ITM-3002', 'description' => 'Dress pants/slacks', 'price' => 6.00, 'display_order' => 11, 'is_active' => true],
            ['tenant_id' => $tenantId, 'name' => 'Skirt', 'code' => 'ITM-3003', 'description' => 'Formal or casual skirt', 'price' => 5.00, 'display_order' => 12, 'is_active' => true],
        ];

        foreach ($items as $itemData) {
            Item::create($itemData);
        }

        // Create service prices for each item
        $allItems = Item::where('tenant_id', $tenantId)->get();
        $allServices = Service::where('tenant_id', $tenantId)->get();

        foreach ($allItems as $item) {
            foreach ($allServices as $service) {
                // Calculate price based on item's base price and service type
                $price = $item->price;

                // Adjust price based on service
                if ($service->name === 'Dry Cleaning') {
                    $price *= 1.5; // Dry cleaning costs more
                } elseif ($service->name === 'Stain Removal') {
                    $price *= 1.3; // Stain removal is a premium service
                } elseif ($service->name === 'Ironing') {
                    $price *= 0.8; // Ironing is cheaper
                }

                ServicePrice::create([
                    'tenant_id' => $tenantId,
                    'item_id' => $item->id,
                    'service_id' => $service->id,
                    'price' => round($price, 2),
                    'is_active' => true,
                ]);
            }
        }
    }
}
