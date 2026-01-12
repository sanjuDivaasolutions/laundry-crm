<?php

namespace Tests\Feature\Controllers\API;

use App\Models\AgentCommission;
use App\Models\Company;
use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->company = Company::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_get_dashboard_data()
    {
        // Create some test data
        SalesOrder::factory()->count(5)->create([
            'company_id' => $this->company->id,
            'status' => 'confirmed',
            'grand_total' => 1000.00,
        ]);

        SalesInvoice::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'grand_total' => 1200.00,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/reports/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'sales_overview' => [
                    'total_sales',
                    'total_revenue',
                    'average_sale_value',
                ],
                'order_statistics' => [
                    'total_orders',
                    'total_amount',
                    'confirmed_orders',
                    'average_order_value',
                ],
                'commission_summary' => [
                    'total_commissions',
                    'total_commission_amount',
                    'pending_commissions',
                    'approved_commissions',
                ],
                'top_products',
                'recent_orders',
            ]);
    }

    public function test_can_get_profit_loss_report()
    {
        // Create test data
        SalesInvoice::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'grand_total' => 5000.00,
            'sub_total' => 4500.00,
            'tax_total' => 500.00,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/reports/profit-loss');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    [
                        'header',
                        'footer',
                        'data',
                    ],
                ],
                'headers',
                'title',
                'subtitle',
            ]);
    }

    public function test_can_get_sales_by_month_report()
    {
        // Create test data for different months
        SalesInvoice::factory()->create([
            'company_id' => $this->company->id,
            'date' => now()->subMonths(2)->format('Y-m-d'),
            'grand_total' => 1000.00,
        ]);

        SalesInvoice::factory()->create([
            'company_id' => $this->company->id,
            'date' => now()->subMonth()->format('Y-m-d'),
            'grand_total' => 1500.00,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/reports/sales/by-month');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'month',
                        'total',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_can_get_sales_by_product_report()
    {
        // Create test products and sales
        $products = Product::factory()->count(3)->create();

        foreach ($products as $product) {
            SalesInvoice::factory()
                ->hasItems(1, [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'rate' => 100.00,
                    'amount' => 500.00,
                ])
                ->create([
                    'company_id' => $this->company->id,
                    'grand_total' => 500.00,
                ]);
        }

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/reports/sales/by-product');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'salesInvoiceItem' => [
                            'quantity',
                            'rate',
                            'amount',
                        ],
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_can_get_stock_summary_report()
    {
        // Create test products with stock
        $products = Product::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/reports/stock/summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'total_stock',
                        'shelf_stock',
                    ],
                ],
                'links',
                'meta',
                'summary',
            ]);
    }

    public function test_can_get_commission_summary_report()
    {
        // Create test commissions
        $agent = \App\Models\Agent::factory()->create();

        AgentCommission::factory()->count(3)->create([
            'agent_id' => $agent->id,
            'status' => 'pending',
            'commission_amount' => 100.00,
        ]);

        AgentCommission::factory()->count(2)->create([
            'agent_id' => $agent->id,
            'status' => 'approved',
            'commission_amount' => 150.00,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/reports/commissions/summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_commissions',
                'total_commission_amount',
                'pending_commissions',
                'pending_amount',
                'approved_commissions',
                'approved_amount',
                'paid_commissions',
                'paid_amount',
            ]);
    }

    public function test_dashboard_data_filters_by_company()
    {
        // Create data for multiple companies
        $otherCompany = Company::factory()->create();

        SalesOrder::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'grand_total' => 1000.00,
        ]);

        SalesOrder::factory()->count(2)->create([
            'company_id' => $otherCompany->id,
            'grand_total' => 2000.00,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/reports/dashboard', [
                'company_id' => $this->company->id,
            ]);

        $response->assertStatus(200);

        // Should only include data from specified company
        $this->assertEquals(3, $response->json('order_statistics.total_orders'));
    }

    public function test_dashboard_data_filters_by_date_range()
    {
        $startDate = now()->subDays(30)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        // Create orders within date range
        SalesOrder::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'date' => now()->subDays(15)->format('Y-m-d'),
            'grand_total' => 1000.00,
        ]);

        // Create orders outside date range
        SalesOrder::factory()->count(2)->create([
            'company_id' => $this->company->id,
            'date' => now()->subDays(45)->format('Y-m-d'),
            'grand_total' => 1500.00,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/reports/dashboard', [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

        $response->assertStatus(200);

        // Should only include orders within date range
        $this->assertEquals(3, $response->json('order_statistics.total_orders'));
    }

    public function test_unauthorized_user_cannot_access_reports()
    {
        $response = $this->getJson('/api/v1/reports/dashboard');

        $response->assertStatus(401);
    }
}
