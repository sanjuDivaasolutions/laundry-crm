<?php

namespace Database\Seeders\Project;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Services\OrderService;
use Database\Factories\OrderItemFactory;
use Database\Factories\PurchaseOrderItemFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PurchaseOrderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $count = 100;
        for ($i = 0; $i < $count; $i++) {
            $obj = PurchaseOrder::factory()->create();

            $product = PurchaseOrderItem::factory()->create();
            $product->purchase_order_id = $obj->id;
            $product->save();

            OrderService::updatePurchaseOrderTotals($obj);

        }
    }
}
