<?php

namespace Database\Seeders\Project;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Services\OrderService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PurchaseInvoiceTableSeeder extends Seeder
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
            $obj = PurchaseInvoice::factory()->create();

            $product = PurchaseInvoiceItem::factory()->create();
            $product->purchase_invoice_id = $obj->id;
            $product->save();

            OrderService::updatePurchaseOrderTotals($obj);

        }
    }
}
