<?php

namespace Tests\Feature\QuotationConversion;

use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\Buyer;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\User;
use App\Services\QuotationConversionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationConversionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Buyer $buyer;
    protected Warehouse $warehouse;
    protected Quotation $quotation;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->buyer = Buyer::factory()->create();
        $this->warehouse = Warehouse::factory()->create();
        $this->product = Product::factory()->create();

        $this->quotation = Quotation::factory()->create([
            'buyer_id' => $this->buyer->id,
            'status' => 'approved'
        ]);

        // Add items to quotation
        $this->quotation->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 5,
            'unit_price' => 100,
            'discount_percentage' => 10,
            'tax_rate' => 15,
            'total' => 525
        ]);
    }

    public function test_it_can_convert_quotation_to_sales_order()
    {
        $conversionData = [
            'warehouse_id' => $this->warehouse->id,
            'customer_notes' => 'Test customer notes',
            'expected_delivery_date' => now()->addDays(7)->format('Y-m-d'),
            'convert_all_items' => true
        ];

        $service = new QuotationConversionService();
        $salesOrder = $service->convertToSalesOrder($this->quotation, $conversionData);

        $this->assertInstanceOf(SalesOrder::class, $salesOrder);
        $this->assertEquals($this->quotation->buyer_id, $salesOrder->buyer_id);
        $this->assertEquals($this->quotation->id, $salesOrder->quotation_id);
        $this->assertEquals($conversionData['warehouse_id'], $salesOrder->warehouse_id);
        $this->assertEquals($conversionData['customer_notes'], $salesOrder->customer_notes);
        $this->assertEquals('pending', $salesOrder->status);

        // Check that quotation status is updated
        $this->quotation->refresh();
        $this->assertEquals('converted', $this->quotation->status);

        // Check that sales order items are created
        $this->assertCount(1, $salesOrder->items);
        $salesOrderItem = $salesOrder->items->first();
        $this->assertEquals($this->product->id, $salesOrderItem->product_id);
        $this->assertEquals(5, $salesOrderItem->quantity);
        $this->assertEquals(100, $salesOrderItem->unit_price);
    }

    public function test_it_can_convert_selected_items_only()
    {
        // Add another item to quotation
        $product2 = Product::factory()->create();
        $this->quotation->items()->create([
            'product_id' => $product2->id,
            'quantity' => 3,
            'unit_price' => 50,
            'discount_percentage' => 0,
            'tax_rate' => 15,
            'total' => 172.5
        ]);

        $conversionData = [
            'warehouse_id' => $this->warehouse->id,
            'convert_all_items' => false,
            'selected_items' => [
                [
                    'id' => $this->quotation->items->first()->id,
                    'quantity' => 3,
                    'unit_price' => 90
                ]
            ]
        ];

        $service = new QuotationConversionService();
        $salesOrder = $service->convertToSalesOrder($this->quotation, $conversionData);

        // Check that only selected items are converted
        $this->assertCount(1, $salesOrder->items);
        $salesOrderItem = $salesOrder->items->first();
        $this->assertEquals(3, $salesOrderItem->quantity);
        $this->assertEquals(90, $salesOrderItem->unit_price);
    }

    public function test_it_generates_unique_sales_order_number()
    {
        $conversionData = [
            'warehouse_id' => $this->warehouse->id,
            'convert_all_items' => true
        ];

        $service = new QuotationConversionService();
        
        // Convert first quotation
        $salesOrder1 = $service->convertToSalesOrder($this->quotation, $conversionData);
        
        // Create another quotation and convert it
        $quotation2 = Quotation::factory()->create(['buyer_id' => $this->buyer->id]);
        $quotation2->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 75,
            'discount_percentage' => 5,
            'tax_rate' => 15,
            'total' => 159.75
        ]);
        
        $salesOrder2 = $service->convertToSalesOrder($quotation2, $conversionData);

        // Check that sales order numbers are different
        $this->assertNotEquals($salesOrder1->sales_order_number, $salesOrder2->sales_order_number);
        
        // Check that numbers follow the expected format
        $this->assertMatchesRegularExpression('/^SO\d{8}\d{4}$/', $salesOrder1->sales_order_number);
        $this->assertMatchesRegularExpression('/^SO\d{8}\d{4}$/', $salesOrder2->sales_order_number);
    }

    public function test_it_can_preview_conversion_data()
    {
        $service = new QuotationConversionService();
        $preview = $service->previewSalesOrder($this->quotation);

        $this->assertArrayHasKey('quotation_number', $preview);
        $this->assertArrayHasKey('customer', $preview);
        $this->assertArrayHasKey('items', $preview);
        $this->assertArrayHasKey('subtotal', $preview);
        $this->assertArrayHasKey('tax_amount', $preview);
        $this->assertArrayHasKey('total_amount', $preview);

        $this->assertEquals($this->quotation->quotation_number, $preview['quotation_number']);
        $this->assertEquals($this->buyer->name, $preview['customer']['name']);
        $this->assertCount(1, $preview['items']);
    }
}