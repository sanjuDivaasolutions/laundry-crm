<?php

namespace Tests\Feature;

use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\Buyer;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationToSalesOrderConversionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->company = Company::factory()->create();
        $this->buyer = Buyer::factory()->create();
    }

    public function test_it_can_convert_quotation_to_sales_order()
    {
        // Create a quotation
        $quotation = Quotation::factory()->create([
            'company_id' => $this->company->id,
            'buyer_id' => $this->buyer->id,
            'order_no' => 'Q-2024-001',
            'sub_total' => 1000,
            'tax_total' => 100,
            'grand_total' => 1100,
        ]);

        // Add items to quotation
        $quotation->items()->create([
            'product_id' => 1,
            'unit_id' => 1,
            'title' => 'Test Product',
            'sku' => 'TEST-001',
            'rate' => 100,
            'quantity' => 10,
            'amount' => 1000,
        ]);

        // Convert to sales order
        $salesOrder = $quotation->convertToSalesOrder();

        // Assertions
        $this->assertInstanceOf(SalesOrder::class, $salesOrder);
        $this->assertEquals($quotation->company_id, $salesOrder->company_id);
        $this->assertEquals($quotation->buyer_id, $salesOrder->buyer_id);
        $this->assertEquals($quotation->order_no, $salesOrder->quotation_no);
        $this->assertEquals($quotation->sub_total, $salesOrder->sub_total);
        $this->assertEquals($quotation->tax_total, $salesOrder->tax_total);
        $this->assertEquals($quotation->grand_total, $salesOrder->grand_total);

        // Check that items were copied
        $this->assertCount(1, $salesOrder->items);
        $salesOrderItem = $salesOrder->items->first();
        $this->assertEquals('Test Product', $salesOrderItem->description);
        $this->assertEquals(100, $salesOrderItem->rate);
        $this->assertEquals(10, $salesOrderItem->quantity);
        $this->assertEquals(1000, $salesOrderItem->amount);

        // Check that quotation status was updated
        $quotation->refresh();
        $this->assertEquals('converted', $quotation->status->status);
    }

    public function test_it_generates_unique_sales_order_number()
    {
        $quotation = Quotation::factory()->create([
            'company_id' => $this->company->id,
            'buyer_id' => $this->buyer->id,
        ]);

        $salesOrder = $quotation->convertToSalesOrder();

        $this->assertNotEmpty($salesOrder->so_number);
        $this->assertStringStartsWith('SO', $salesOrder->so_number);
    }
}