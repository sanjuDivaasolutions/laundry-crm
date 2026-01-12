<?php

namespace Tests\Feature;

use App\Models\Buyer;
use App\Models\Company;
use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\PaymentTerm;
use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class SalesInvoiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $company;
    protected $buyer;
    protected $product;
    protected $warehouse;
    protected $paymentTerm;
    protected $state;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->company = Company::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->warehouse = Warehouse::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $this->company->update(['warehouse_id' => $this->warehouse->id]);

        $this->buyer = Buyer::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $this->product = Product::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $this->paymentTerm = PaymentTerm::factory()->create();
        
        $this->state = State::factory()->create();
    }

    protected function authenticatedHeaders(): array
    {
        $token = JWTAuth::fromUser($this->user);
        return [
            'Authorization' => "Bearer $token",
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    public function test_it_can_list_sales_invoices()
    {
        // Create test sales invoices
        SalesInvoice::factory(3)->create([
            'company_id' => $this->company->id,
            'buyer_id' => $this->buyer->id,
            'warehouse_id' => $this->warehouse->id,
            'payment_term_id' => $this->paymentTerm->id,
            'state_id' => $this->state->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/sales-invoices', $this->authenticatedHeaders());

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
                             'invoice_number',
                             'date',
                             'grand_total',
                         ]
                     ],
                     'links',
                     'meta'
                 ]);
    }

    public function test_it_can_create_a_sales_invoice()
    {
        $invoiceData = [
            'invoice_number' => 'SI-' . $this->faker->unique()->numberBetween(1000, 9999),
            'date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'buyer_id' => $this->buyer->id,
            'warehouse_id' => $this->warehouse->id,
            'payment_term_id' => $this->paymentTerm->id,
            'state_id' => $this->state->id,
            'type' => 'p',
            'order_type' => 'product',
            'is_taxable' => true,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'rate' => 100.00,
                    'amount' => 200.00,
                ]
            ],
            'taxes' => []
        ];

        $response = $this->postJson('/api/v1/sales-invoices', $invoiceData, $this->authenticatedHeaders());

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'invoice_number',
                         'date',
                         'buyer_id',
                         'grand_total',
                     ]
                 ]);

        $this->assertDatabaseHas('sales_invoices', [
            'invoice_number' => $invoiceData['invoice_number'],
            'buyer_id' => $this->buyer->id,
        ]);
    }

    public function test_it_can_show_a_sales_invoice()
    {
        $invoice = SalesInvoice::factory()->create([
            'company_id' => $this->company->id,
            'buyer_id' => $this->buyer->id,
            'warehouse_id' => $this->warehouse->id,
            'payment_term_id' => $this->paymentTerm->id,
            'state_id' => $this->state->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson("/api/v1/sales-invoices/{$invoice->id}", $this->authenticatedHeaders());

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'invoice_number',
                         'date',
                         'buyer_id',
                         'grand_total',
                     ]
                 ]);
    }

    public function test_it_can_update_a_sales_invoice()
    {
        $invoice = SalesInvoice::factory()->create([
            'company_id' => $this->company->id,
            'buyer_id' => $this->buyer->id,
            'warehouse_id' => $this->warehouse->id,
            'payment_term_id' => $this->paymentTerm->id,
            'state_id' => $this->state->id,
            'user_id' => $this->user->id,
        ]);

        $updateData = [
            'invoice_number' => $invoice->invoice_number,
            'date' => $invoice->date,
            'due_date' => $invoice->due_date,
            'buyer_id' => $this->buyer->id,
            'warehouse_id' => $this->warehouse->id,
            'payment_term_id' => $this->paymentTerm->id,
            'state_id' => $this->state->id,
            'type' => 'p',
            'order_type' => 'product',
            'is_taxable' => true,
            'remark' => 'Updated remark',
            'items' => [],
            'taxes' => []
        ];

        $response = $this->putJson("/api/v1/sales-invoices/{$invoice->id}", $updateData, $this->authenticatedHeaders());

        $response->assertStatus(202);

        $this->assertDatabaseHas('sales_invoices', [
            'id' => $invoice->id,
            'remark' => 'Updated remark',
        ]);
    }

    public function test_it_can_delete_a_sales_invoice()
    {
        $invoice = SalesInvoice::factory()->create([
            'company_id' => $this->company->id,
            'buyer_id' => $this->buyer->id,
            'warehouse_id' => $this->warehouse->id,
            'payment_term_id' => $this->paymentTerm->id,
            'state_id' => $this->state->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/v1/sales-invoices/{$invoice->id}", [], $this->authenticatedHeaders());

        $response->assertStatus(204);

        $this->assertDatabaseMissing('sales_invoices', [
            'id' => $invoice->id,
        ]);
    }

    public function test_it_requires_authentication_for_sales_invoice_operations()
    {
        $response = $this->getJson('/api/v1/sales-invoices');

        $response->assertStatus(401);
    }

    public function test_it_validates_required_fields_when_creating_sales_invoice()
    {
        $response = $this->postJson('/api/v1/sales-invoices', [], $this->authenticatedHeaders());

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'invoice_number',
                     'date',
                     'buyer_id',
                 ]);
    }

    public function test_it_can_generate_pdf_for_sales_invoice()
    {
        $invoice = SalesInvoice::factory()->create([
            'company_id' => $this->company->id,
            'buyer_id' => $this->buyer->id,
            'warehouse_id' => $this->warehouse->id,
            'payment_term_id' => $this->paymentTerm->id,
            'state_id' => $this->state->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->get("/api/v1/sales-invoices-single-pdf/{$invoice->id}", $this->authenticatedHeaders());

        $response->assertStatus(200)
                 ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_it_can_export_sales_invoices_to_csv()
    {
        SalesInvoice::factory(3)->create([
            'company_id' => $this->company->id,
            'buyer_id' => $this->buyer->id,
            'warehouse_id' => $this->warehouse->id,
            'payment_term_id' => $this->paymentTerm->id,
            'state_id' => $this->state->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->get('/api/v1/sales-invoices-csv', $this->authenticatedHeaders());

        $response->assertStatus(200)
                 ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}