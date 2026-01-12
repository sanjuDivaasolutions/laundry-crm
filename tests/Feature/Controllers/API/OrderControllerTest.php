<?php

namespace Tests\Feature\Controllers\API;

use App\Models\Agent;
use App\Models\Buyer;
use App\Models\Company;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Company $company;

    protected Buyer $buyer;

    protected Agent $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->company = Company::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $this->buyer = Buyer::factory()->create();
        $this->agent = Agent::factory()->create();
    }

    public function test_can_list_orders()
    {
        SalesOrder::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/sales-orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'order_number',
                        'buyer_id',
                        'grand_total',
                        'status',
                    ],
                ],
            ]);
    }

    public function test_can_create_order()
    {
        $product = Product::factory()->create();

        $orderData = [
            'order_number' => 'SO-001',
            'buyer_id' => $this->buyer->id,
            'agent_id' => $this->agent->id,
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'order_type' => 'product',
            'date' => now()->format('Y-m-d'),
            'sub_total' => 1000.00,
            'tax_total' => 100.00,
            'grand_total' => 1100.00,
            'status' => 'pending',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 500.00,
                    'tax_total' => 50.00,
                    'sub_total' => 1000.00,
                    'grand_total' => 1050.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/sales-orders', $orderData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'order_number' => 'SO-001',
                'grand_total' => 1100.00,
                'status' => 'pending',
            ]);

        $this->assertDatabaseHas('sales_orders', [
            'order_number' => 'SO-001',
            'buyer_id' => $this->buyer->id,
            'agent_id' => $this->agent->id,
        ]);

        $this->assertDatabaseHas('sales_order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_can_view_order()
    {
        $order = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/sales-orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $order->id,
                'order_number' => $order->order_number,
                'grand_total' => $order->grand_total,
            ]);
    }

    public function test_can_update_order()
    {
        $order = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $updateData = [
            'status' => 'confirmed',
            'notes' => 'Updated order notes',
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/sales-orders/{$order->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'confirmed',
                'notes' => 'Updated order notes',
            ]);

        $this->assertDatabaseHas('sales_orders', [
            'id' => $order->id,
            'status' => 'confirmed',
            'notes' => 'Updated order notes',
        ]);
    }

    public function test_can_delete_order()
    {
        $order = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/sales-orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Order deleted successfully']);

        $this->assertSoftDeleted('sales_orders', [
            'id' => $order->id,
        ]);
    }

    public function test_can_convert_order_to_invoice()
    {
        $order = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'status' => 'confirmed',
            'grand_total' => 1100.00,
        ]);

        $invoiceData = [
            'date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'notes' => 'Invoice notes',
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/sales-orders/{$order->id}/convert-to-invoice", $invoiceData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('sales_invoices', [
            'sales_order_id' => $order->id,
            'buyer_id' => $order->buyer_id,
            'grand_total' => 1100.00,
        ]);

        $this->assertDatabaseHas('sales_orders', [
            'id' => $order->id,
            'status' => 'converted',
        ]);
    }

    public function test_can_update_order_status()
    {
        $order = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $statusData = [
            'status' => 'confirmed',
            'notes' => 'Status updated',
        ];

        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/sales-orders/{$order->id}/status", $statusData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'confirmed',
                'notes' => 'Status updated',
            ]);

        $this->assertDatabaseHas('sales_orders', [
            'id' => $order->id,
            'status' => 'confirmed',
            'notes' => 'Status updated',
        ]);
    }

    public function test_can_get_order_statistics()
    {
        SalesOrder::factory()->count(5)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'status' => 'confirmed',
            'grand_total' => 1000.00,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/sales-orders/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_orders',
                'total_amount',
                'confirmed_orders',
                'average_order_value',
            ]);
    }

    public function test_cannot_convert_non_confirmed_order()
    {
        $order = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'status' => 'pending', // Not confirmed
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/sales-orders/{$order->id}/convert-to-invoice");

        $response->assertStatus(422);
    }

    public function test_validation_fails_for_invalid_order_data()
    {
        $invalidData = [
            'order_number' => '',
            'buyer_id' => 999, // Non-existent buyer
            'grand_total' => -100, // Invalid: negative
            'status' => 'invalid-status',
            'items' => [], // Invalid: empty items
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/sales-orders', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'order_number',
                'buyer_id',
                'grand_total',
                'status',
                'items',
            ]);
    }
}
