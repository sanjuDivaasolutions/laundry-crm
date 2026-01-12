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
 *  *  Last modified: 12/12/24, 5:40 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace Database\Seeders\Project;

use App\Models\Product;
use App\Models\ProductInventory;
use App\Models\ProductOpening;
use App\Models\ProductPrice;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProductTableSeeder extends Seeder
{
    public function run()
    {
        $warehouseId = config('system.defaults.warehouse.id');
        
        // North USA Over-the-Counter Products
        $products = [
            // Pain Relief & Fever
            ['name' => 'Tylenol Extra Strength 500mg', 'code' => 'OTC-001', 'category_id' => 1, 'purchase' => 8.99, 'sale' => 12.99, 'stock' => 50],
            ['name' => 'Advil Ibuprofen 200mg', 'code' => 'OTC-002', 'category_id' => 1, 'purchase' => 9.49, 'sale' => 13.99, 'stock' => 45],
            ['name' => 'Aleve Naproxen Sodium 220mg', 'code' => 'OTC-003', 'category_id' => 1, 'purchase' => 10.99, 'sale' => 15.99, 'stock' => 40],
            ['name' => 'Aspirin 325mg Low Dose', 'code' => 'OTC-004', 'category_id' => 1, 'purchase' => 5.99, 'sale' => 8.99, 'stock' => 60],
            
            // Cold & Flu
            ['name' => 'Mucinex DM Extended Release', 'code' => 'OTC-005', 'category_id' => 1, 'purchase' => 15.99, 'sale' => 22.99, 'stock' => 35],
            ['name' => 'Robitussin Cough Syrup', 'code' => 'OTC-006', 'category_id' => 1, 'purchase' => 11.49, 'sale' => 16.99, 'stock' => 30],
            ['name' => 'Sudafed PE Sinus Congestion', 'code' => 'OTC-007', 'category_id' => 1, 'purchase' => 8.99, 'sale' => 12.99, 'stock' => 40],
            ['name' => 'Theraflu Multi-Symptom', 'code' => 'OTC-008', 'category_id' => 1, 'purchase' => 9.99, 'sale' => 14.99, 'stock' => 25],
            ['name' => 'Vicks VapoRub Ointment', 'code' => 'OTC-009', 'category_id' => 1, 'purchase' => 7.49, 'sale' => 10.99, 'stock' => 50],
            
            // Allergy & Sinus
            ['name' => 'Claritin 24 Hour Allergy Relief', 'code' => 'OTC-010', 'category_id' => 1, 'purchase' => 18.99, 'sale' => 26.99, 'stock' => 30],
            ['name' => 'Zyrtec Allergy Relief 10mg', 'code' => 'OTC-011', 'category_id' => 1, 'purchase' => 17.99, 'sale' => 24.99, 'stock' => 35],
            ['name' => 'Benadryl Allergy Ultratabs', 'code' => 'OTC-012', 'category_id' => 1, 'purchase' => 6.99, 'sale' => 9.99, 'stock' => 45],
            ['name' => 'Flonase Allergy Relief Spray', 'code' => 'OTC-013', 'category_id' => 1, 'purchase' => 16.99, 'sale' => 23.99, 'stock' => 25],
            
            // Digestive Health
            ['name' => 'Pepto-Bismol Liquid', 'code' => 'OTC-014', 'category_id' => 1, 'purchase' => 7.99, 'sale' => 11.99, 'stock' => 40],
            ['name' => 'Tums Extra Strength Antacid', 'code' => 'OTC-015', 'category_id' => 1, 'purchase' => 5.49, 'sale' => 7.99, 'stock' => 55],
            ['name' => 'Imodium A-D Anti-Diarrheal', 'code' => 'OTC-016', 'category_id' => 1, 'purchase' => 8.99, 'sale' => 12.99, 'stock' => 30],
            ['name' => 'Prilosec OTC Acid Reducer', 'code' => 'OTC-017', 'category_id' => 1, 'purchase' => 19.99, 'sale' => 27.99, 'stock' => 20],
            ['name' => 'Gas-X Extra Strength', 'code' => 'OTC-018', 'category_id' => 1, 'purchase' => 7.49, 'sale' => 10.99, 'stock' => 35],
            
            // Vitamins & Supplements
            ['name' => 'Centrum Multivitamin Adults', 'code' => 'OTC-019', 'category_id' => 2, 'purchase' => 12.99, 'sale' => 18.99, 'stock' => 40],
            ['name' => 'Vitamin D3 2000 IU', 'code' => 'OTC-020', 'category_id' => 2, 'purchase' => 8.99, 'sale' => 12.99, 'stock' => 50],
            ['name' => 'Vitamin C 1000mg', 'code' => 'OTC-021', 'category_id' => 2, 'purchase' => 9.99, 'sale' => 14.99, 'stock' => 45],
            ['name' => 'Fish Oil Omega-3 1200mg', 'code' => 'OTC-022', 'category_id' => 2, 'purchase' => 15.99, 'sale' => 22.99, 'stock' => 30],
            ['name' => 'Calcium + Vitamin D', 'code' => 'OTC-023', 'category_id' => 2, 'purchase' => 10.99, 'sale' => 15.99, 'stock' => 35],
            ['name' => 'Melatonin 5mg Sleep Aid', 'code' => 'OTC-024', 'category_id' => 2, 'purchase' => 7.99, 'sale' => 11.99, 'stock' => 40],
            
            // First Aid
            ['name' => 'Band-Aid Adhesive Bandages', 'code' => 'OTC-025', 'category_id' => 3, 'purchase' => 4.99, 'sale' => 7.99, 'stock' => 60],
            ['name' => 'Neosporin Antibiotic Ointment', 'code' => 'OTC-026', 'category_id' => 3, 'purchase' => 6.99, 'sale' => 9.99, 'stock' => 50],
            ['name' => 'Hydrogen Peroxide 3%', 'code' => 'OTC-027', 'category_id' => 3, 'purchase' => 2.99, 'sale' => 4.99, 'stock' => 70],
            ['name' => 'Rubbing Alcohol 70%', 'code' => 'OTC-028', 'category_id' => 3, 'purchase' => 3.49, 'sale' => 5.49, 'stock' => 65],
            ['name' => 'Gauze Pads Sterile 4x4', 'code' => 'OTC-029', 'category_id' => 3, 'purchase' => 5.99, 'sale' => 8.99, 'stock' => 45],
            
            // Personal Care
            ['name' => 'Listerine Mouthwash Cool Mint', 'code' => 'OTC-030', 'category_id' => 4, 'purchase' => 5.99, 'sale' => 8.99, 'stock' => 40],
            ['name' => 'Crest 3D White Toothpaste', 'code' => 'OTC-031', 'category_id' => 4, 'purchase' => 4.49, 'sale' => 6.99, 'stock' => 55],
            ['name' => 'Dove Body Wash', 'code' => 'OTC-032', 'category_id' => 4, 'purchase' => 6.99, 'sale' => 9.99, 'stock' => 45],
            ['name' => 'Head & Shoulders Shampoo', 'code' => 'OTC-033', 'category_id' => 4, 'purchase' => 7.99, 'sale' => 11.99, 'stock' => 40],
            ['name' => 'Degree Antiperspirant Deodorant', 'code' => 'OTC-034', 'category_id' => 4, 'purchase' => 4.99, 'sale' => 7.49, 'stock' => 50],
            ['name' => 'Gillette Fusion Razors', 'code' => 'OTC-035', 'category_id' => 4, 'purchase' => 12.99, 'sale' => 18.99, 'stock' => 25],
        ];

        foreach ($products as $index => $productData) {
            $product = Product::create([
                'name' => $productData['name'],
                'code' => $productData['code'],
                'sku' => $productData['code'], // Use code as SKU
                'type' => 'product',
                'description' => $productData['name'] . ' - Over the counter product',
                'manufacturer' => 'Generic Health',
                'is_returnable' => true,
                'category_id' => $productData['category_id'],
                'unit_01_id' => 1, // Default unit
                'unit_02_id' => 1,
                'company_id' => 1,
                'supplier_id' => 1,
                'user_id' => 1,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            ProductPrice::create([
                'purchase_price'    => $productData['purchase'],
                'sale_price'        => $productData['sale'],
                'lowest_sale_price' => $productData['sale'] * 0.9,
                'unit_id'           => $product->unit_01_id,
                'product_id'        => $product->id,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            $openingValue = $productData['purchase'] * $productData['stock'];

            ProductOpening::create([
                'product_id'          => $product->id,
                'warehouse_id'        => $warehouseId,
                'opening_stock'       => $productData['stock'],
                'opening_stock_value' => $openingValue,
            ]);

            ProductInventory::create([
                'reason'       => 'opening',
                'quantity'     => $productData['stock'],
                'rate'         => $productData['purchase'],
                'amount'       => $openingValue,
                'warehouse_id' => $warehouseId,
                'shelf_id'     => null,
                'product_id'   => $product->id,
                'unit_id'      => $product->unit_01_id,
                'batch_id'     => null,
                'user_id'      => 1,
                'date'         => Carbon::now()->format(config('project.date_format')),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }
}
