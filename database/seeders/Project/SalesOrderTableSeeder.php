<?php

namespace Database\Seeders\Project;

use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Services\OrderService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SalesOrderTableSeeder extends Seeder
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
            $obj = SalesOrder::factory()->create();

            $product = SalesOrderItem::factory()->create();
            $product->sales_order_id = $obj->id;
            $product->save();

            OrderService::updateSalesOrderTotals($obj);

        }
    }
}
