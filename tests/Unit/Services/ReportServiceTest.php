<?php

namespace Tests\Unit\Services;

use App\Models\Buyer;
use App\Models\Company;
use App\Models\Inward;
use App\Models\InwardItem;
use App\Models\Product;
use App\Models\ProductOpening;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_realized_cogs_using_fifo_batches(): void
    {
        $dateFormat = config('project.date_format', 'Y-m-d');

        $company = Company::factory()->create();
        $user = User::factory()->create();

        $supplier = Supplier::create([
            'code' => 'SUP-UNIT',
            'display_name' => 'Unit Supplier',
            'name' => 'Unit Supplier',
            'active' => true,
        ]);

        $warehouse = Warehouse::create([
            'name' => 'Main Warehouse',
            'code' => 'WH-UNIT',
        ]);

        $buyer = Buyer::create([
            'code' => 'BUY-UNIT',
            'display_name' => 'Unit Buyer',
            'name' => 'Unit Buyer',
            'active' => true,
        ]);

        $product = Product::create([
            'code' => 'PROD-UNIT',
            'type' => 'product',
            'name' => 'Unit Product',
            'sku' => 'PROD-SKU',
            'active' => true,
            'has_inventory' => true,
            'company_id' => $company->id,
        ]);

        // Purchase batches: Jan 5 (100 @10), Feb 2 (50 @12), Mar 10 (80 @11 CAD with rate 1.25)
        $this->createInwardWithItem($company, $supplier, $warehouse, $user, $product, Carbon::parse('2025-01-05'), 100, 10.00, 1.0);
        $this->createInwardWithItem($company, $supplier, $warehouse, $user, $product, Carbon::parse('2025-02-02'), 50, 12.00, 1.0);
        $this->createInwardWithItem($company, $supplier, $warehouse, $user, $product, Carbon::parse('2025-03-10'), 80, 11.00, 1.25);

        // Sales: Jan 20 (90 units), Mar 15 (40 units), Apr 5 (60 units)
        $this->createSaleWithItem($company, $buyer, $product, Carbon::parse('2025-01-20'), 90);
        $this->createSaleWithItem($company, $buyer, $product, Carbon::parse('2025-03-15'), 40);
        $this->createSaleWithItem($company, $buyer, $product, Carbon::parse('2025-04-05'), 60);

        $filters = [
            'date' => [
                'start' => Carbon::parse('2025-01-01'),
                'end' => Carbon::parse('2025-04-30'),
            ],
            'company_id' => [$company->id],
        ];

        $method = new \ReflectionMethod(ReportService::class, 'calculateCOGS');
        $method->setAccessible(true);

        $realizedCogs = $method->invoke(null, $filters);

        // FIFO expectation: (90*10) + (10*10 + 30*12) + (20*12 + 40*8.8) = 1952.00
        $this->assertEqualsWithDelta(1952.00, $realizedCogs, 0.01);
    }

    public function test_it_returns_zero_cogs_when_no_matching_purchases_exist(): void
    {
        $company = Company::factory()->create();
        $buyer = Buyer::create([
            'code' => 'BUY-ZERO',
            'display_name' => 'Zero Buyer',
            'name' => 'Zero Buyer',
            'active' => true,
        ]);
        $product = Product::create([
            'code' => 'PROD-ZERO',
            'type' => 'product',
            'name' => 'Zero Product',
            'sku' => 'PROD-ZERO',
            'active' => true,
            'has_inventory' => true,
            'company_id' => $company->id,
        ]);

        $this->createSaleWithItem($company, $buyer, $product, Carbon::parse('2025-01-10'), 25);

        $filters = [
            'date' => [
                'start' => Carbon::parse('2025-01-01'),
                'end' => Carbon::parse('2025-03-31'),
            ],
            'company_id' => [$company->id],
        ];

        $method = new \ReflectionMethod(ReportService::class, 'calculateCOGS');
        $method->setAccessible(true);

        $realizedCogs = $method->invoke(null, $filters);

        $this->assertSame(0.0, $realizedCogs);
    }

    public function test_it_calculates_cogs_using_opening_stock_when_no_purchases_exist(): void
    {
        $company = Company::factory()->create();
        $buyer = Buyer::create([
            'code' => 'BUY-OPENING',
            'display_name' => 'Opening Buyer',
            'name' => 'Opening Buyer',
            'active' => true,
        ]);
        $warehouse = Warehouse::create([
            'name' => 'Opening Warehouse',
            'code' => 'WH-OPENING',
        ]);

        $product = Product::create([
            'code' => 'PROD-OPENING',
            'type' => 'product',
            'name' => 'Opening Product',
            'sku' => 'PROD-OPENING',
            'active' => true,
            'has_inventory' => true,
            'company_id' => $company->id,
        ]);

        ProductOpening::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'opening_stock' => 100,
            'opening_stock_value' => 1500,
        ]);

        $this->createSaleWithItem($company, $buyer, $product, Carbon::parse('2025-02-10'), 30);

        $filters = [
            'date' => [
                'start' => Carbon::parse('2025-01-01'),
                'end' => Carbon::parse('2025-03-31'),
            ],
            'company_id' => [$company->id],
        ];

        $method = new \ReflectionMethod(ReportService::class, 'calculateCOGS');
        $method->setAccessible(true);

        $realizedCogs = $method->invoke(null, $filters);

        $this->assertEqualsWithDelta(450.00, $realizedCogs, 0.01);
    }

    public function test_it_handles_multiple_products_and_interleaved_sales(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create();
        $supplier = Supplier::create([
            'code' => 'SUP-MULTI',
            'display_name' => 'Multi Supplier',
            'name' => 'Multi Supplier',
            'active' => true,
        ]);
        $warehouse = Warehouse::create([
            'name' => 'Multi Warehouse',
            'code' => 'WH-MULTI',
        ]);
        $buyer = Buyer::create([
            'code' => 'BUY-MULTI',
            'display_name' => 'Multi Buyer',
            'name' => 'Multi Buyer',
            'active' => true,
        ]);

        $productA = Product::create([
            'code' => 'PROD-A',
            'type' => 'product',
            'name' => 'Product A',
            'sku' => 'PROD-A',
            'active' => true,
            'has_inventory' => true,
            'company_id' => $company->id,
        ]);
        $productB = Product::create([
            'code' => 'PROD-B',
            'type' => 'product',
            'name' => 'Product B',
            'sku' => 'PROD-B',
            'active' => true,
            'has_inventory' => true,
            'company_id' => $company->id,
        ]);

        // Product A purchases
        $this->createInwardWithItem($company, $supplier, $warehouse, $user, $productA, Carbon::parse('2025-01-01'), 60, 10.00, 1.0);
        $this->createInwardWithItem($company, $supplier, $warehouse, $user, $productA, Carbon::parse('2025-02-15'), 40, 11.00, 1.0);

        // Product B purchase (in foreign currency)
        $this->createInwardWithItem($company, $supplier, $warehouse, $user, $productB, Carbon::parse('2025-01-20'), 30, 13.20, 1.65); // normalized cost = 8.0

        // Interleaved sales
        $this->createSaleWithItem($company, $buyer, $productA, Carbon::parse('2025-02-01'), 50);
        $this->createSaleWithItem($company, $buyer, $productB, Carbon::parse('2025-02-10'), 20);
        $this->createSaleWithItem($company, $buyer, $productA, Carbon::parse('2025-03-05'), 30);

        $filters = [
            'date' => [
                'start' => Carbon::parse('2025-01-01'),
                'end' => Carbon::parse('2025-04-30'),
            ],
            'company_id' => [$company->id],
        ];

        $method = new \ReflectionMethod(ReportService::class, 'calculateCOGS');
        $method->setAccessible(true);

        $realizedCogs = $method->invoke(null, $filters);

        // Product A: (50 * 10) + (10 * 10 + 20 * 11) = 820
        // Product B: (20 * 8) = 160
        $this->assertEqualsWithDelta(980.00, $realizedCogs, 0.01);
    }

    public function test_it_skips_inbound_batches_with_invalid_quantities_or_rates(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create();
        $supplier = Supplier::create([
            'code' => 'SUP-INVALID',
            'display_name' => 'Invalid Supplier',
            'name' => 'Invalid Supplier',
            'active' => true,
        ]);
        $warehouse = Warehouse::create([
            'name' => 'Invalid Warehouse',
            'code' => 'WH-INVALID',
        ]);
        $buyer = Buyer::create([
            'code' => 'BUY-INVALID',
            'display_name' => 'Invalid Buyer',
            'name' => 'Invalid Buyer',
            'active' => true,
        ]);
        $product = Product::create([
            'code' => 'PROD-INVALID',
            'type' => 'product',
            'name' => 'Product Invalid',
            'sku' => 'PROD-INVALID',
            'active' => true,
            'has_inventory' => true,
            'company_id' => $company->id,
        ]);

        // Invalid batches (zero quantity, zero rate) should be ignored
        $this->createInwardWithItem($company, $supplier, $warehouse, $user, $product, Carbon::parse('2025-01-05'), 0, 10.00, 1.0);
        $this->createInwardWithItem($company, $supplier, $warehouse, $user, $product, Carbon::parse('2025-01-10'), 20, 0.00, 1.0);

        // Valid batch
        $this->createInwardWithItem($company, $supplier, $warehouse, $user, $product, Carbon::parse('2025-01-15'), 40, 8.00, 1.0);

        $this->createSaleWithItem($company, $buyer, $product, Carbon::parse('2025-02-01'), 20);

        $filters = [
            'date' => [
                'start' => Carbon::parse('2025-01-01'),
                'end' => Carbon::parse('2025-03-31'),
            ],
            'company_id' => [$company->id],
        ];

        $method = new \ReflectionMethod(ReportService::class, 'calculateCOGS');
        $method->setAccessible(true);

        $realizedCogs = $method->invoke(null, $filters);

        $this->assertEqualsWithDelta(160.00, $realizedCogs, 0.01);
    }

    public function test_it_limits_cost_to_available_batches_when_sales_exceed_stock(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create();
        $supplier = Supplier::create([
            'code' => 'SUP-OVERSELL',
            'display_name' => 'Oversell Supplier',
            'name' => 'Oversell Supplier',
            'active' => true,
        ]);
        $warehouse = Warehouse::create([
            'name' => 'Oversell Warehouse',
            'code' => 'WH-OVERSELL',
        ]);
        $buyer = Buyer::create([
            'code' => 'BUY-OVERSELL',
            'display_name' => 'Oversell Buyer',
            'name' => 'Oversell Buyer',
            'active' => true,
        ]);
        $product = Product::create([
            'code' => 'PROD-OVERSELL',
            'type' => 'product',
            'name' => 'Oversell Product',
            'sku' => 'PROD-OVERSELL',
            'active' => true,
            'has_inventory' => true,
            'company_id' => $company->id,
        ]);

        $this->createInwardWithItem($company, $supplier, $warehouse, $user, $product, Carbon::parse('2025-01-05'), 100, 5.00, 1.0);

        // Sell more than we have on hand (150 > 100). FIFO should cap cost at available stock (100 * 5)
        $this->createSaleWithItem($company, $buyer, $product, Carbon::parse('2025-01-10'), 150);

        $filters = [
            'date' => [
                'start' => Carbon::parse('2025-01-01'),
                'end' => Carbon::parse('2025-01-31'),
            ],
            'company_id' => [$company->id],
        ];

        $method = new \ReflectionMethod(ReportService::class, 'calculateCOGS');
        $method->setAccessible(true);

        $realizedCogs = $method->invoke(null, $filters);

        $this->assertEqualsWithDelta(500.00, $realizedCogs, 0.01);
    }

    public function test_it_ignores_batches_belonging_to_other_companies(): void
    {
        $companyWithSale = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $user = User::factory()->create();
        $supplier = Supplier::create([
            'code' => 'SUP-CROSS',
            'display_name' => 'Cross Supplier',
            'name' => 'Cross Supplier',
            'active' => true,
        ]);
        $warehouse = Warehouse::create([
            'name' => 'Cross Warehouse',
            'code' => 'WH-CROSS',
        ]);
        $buyer = Buyer::create([
            'code' => 'BUY-CROSS',
            'display_name' => 'Cross Buyer',
            'name' => 'Cross Buyer',
            'active' => true,
        ]);
        $product = Product::create([
            'code' => 'PROD-CROSS',
            'type' => 'product',
            'name' => 'Cross Product',
            'sku' => 'PROD-CROSS',
            'active' => true,
            'has_inventory' => true,
            'company_id' => $companyWithSale->id,
        ]);

        // Purchase recorded under another company even though it references the same product
        $this->createInwardWithItem($otherCompany, $supplier, $warehouse, $user, $product, Carbon::parse('2025-01-05'), 100, 7.00, 1.0);

        $this->createSaleWithItem($companyWithSale, $buyer, $product, Carbon::parse('2025-01-10'), 20);

        $filters = [
            'date' => [
                'start' => Carbon::parse('2025-01-01'),
                'end' => Carbon::parse('2025-01-31'),
            ],
            'company_id' => [$companyWithSale->id],
        ];

        $method = new \ReflectionMethod(ReportService::class, 'calculateCOGS');
        $method->setAccessible(true);

        $realizedCogs = $method->invoke(null, $filters);

        $this->assertSame(0.0, $realizedCogs);
    }

    public function test_it_ignores_purchases_after_the_reporting_period(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create();
        $supplier = Supplier::create([
            'code' => 'SUP-FUTURE',
            'display_name' => 'Future Supplier',
            'name' => 'Future Supplier',
            'active' => true,
        ]);
        $warehouse = Warehouse::create([
            'name' => 'Future Warehouse',
            'code' => 'WH-FUTURE',
        ]);
        $buyer = Buyer::create([
            'code' => 'BUY-FUTURE',
            'display_name' => 'Future Buyer',
            'name' => 'Future Buyer',
            'active' => true,
        ]);
        $product = Product::create([
            'code' => 'PROD-FUTURE',
            'type' => 'product',
            'name' => 'Future Product',
            'sku' => 'PROD-FUTURE',
            'active' => true,
            'has_inventory' => true,
            'company_id' => $company->id,
        ]);

        // Purchase arrives after report end date
        $this->createInwardWithItem($company, $supplier, $warehouse, $user, $product, Carbon::parse('2025-02-10'), 100, 6.00, 1.0);

        $this->createSaleWithItem($company, $buyer, $product, Carbon::parse('2025-01-15'), 30);

        $filters = [
            'date' => [
                'start' => Carbon::parse('2025-01-01'),
                'end' => Carbon::parse('2025-01-31'),
            ],
            'company_id' => [$company->id],
        ];

        $method = new \ReflectionMethod(ReportService::class, 'calculateCOGS');
        $method->setAccessible(true);

        $realizedCogs = $method->invoke(null, $filters);

        $this->assertSame(0.0, $realizedCogs);
    }

    public function test_it_aggregates_breakdowns_when_invoice_date_uses_configured_format(): void
    {
        $company = Company::factory()->create();
        $buyer = Buyer::create([
            'code' => 'BUY-FALLBACK',
            'display_name' => 'Fallback Buyer',
            'name' => 'Fallback Buyer',
            'active' => true,
        ]);
        $product = Product::create([
            'code' => 'PROD-FALLBACK',
            'type' => 'product',
            'name' => 'Fallback Product',
            'sku' => 'PROD-FALLBACK',
            'active' => true,
            'has_inventory' => true,
            'company_id' => $company->id,
        ]);

        $invoiceDate = Carbon::parse('2025-02-15 14:30:00');

        $invoice = SalesInvoice::create([
            'invoice_number' => 'SI-' . uniqid(),
            'company_id' => $company->id,
            'buyer_id' => $buyer->id,
            'date' => $invoiceDate->format(config('project.date_format')),
            'currency_rate' => 1,
            'sub_total' => 140,
            'tax_total' => 0,
            'tax_rate' => 0,
            'grand_total' => 140,
            'is_taxable' => false,
            'payment_status' => 'pending',
            'created_at' => $invoiceDate,
            'updated_at' => $invoiceDate,
        ]);

        SalesInvoiceItem::create([
            'sales_invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'quantity' => 7,
            'rate' => 20,
            'amount' => 140,
        ]);

        request()->replace([]);

        $originalGuard = config('system.auth.admin');
        config()->set('system.auth.admin', 'web');

        $user = User::factory()->create([
            'settings' => ['company_id' => $company->id],
        ]);
        $this->actingAs($user, 'web');

        try {
            $result = ReportService::getProductSaleDetails($product->id);
        } finally {
            auth('web')->logout();
            config()->set('system.auth.admin', $originalGuard);
        }

        $this->assertSame(7.0, $result['total_quantity']);
        $this->assertSame(140.0, $result['total_amount']);

        $this->assertNotEmpty($result['monthly_breakdown']);
        $this->assertEquals(7.0, array_sum(array_column($result['monthly_breakdown'], 'quantity')));
        $this->assertEquals(140.0, array_sum(array_column($result['monthly_breakdown'], 'amount')));
        $this->assertSame($invoiceDate->format('M Y'), $result['monthly_breakdown'][0]['label']);

        $this->assertNotEmpty($result['weekly_breakdown']);
        $this->assertEquals(7.0, array_sum(array_column($result['weekly_breakdown'], 'quantity')));
        $this->assertEquals(140.0, array_sum(array_column($result['weekly_breakdown'], 'amount')));
    }

    private function createInwardWithItem(
        Company $company,
        Supplier $supplier,
        Warehouse $warehouse,
        User $user,
        Product $product,
        Carbon $date,
        float $quantity,
        float $unitRate,
        float $currencyRate
    ): void {
        $dateFormat = config('project.date_format', 'Y-m-d');

        $inward = Inward::create([
            'invoice_number' => 'IN-' . uniqid(),
            'date' => $date->format($dateFormat),
            'remark' => null,
            'currency_rate' => $currencyRate,
            'sub_total' => $quantity * $unitRate,
            'tax_total' => 0,
            'tax_rate' => 0,
            'grand_total' => $quantity * $unitRate,
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
        ]);

        InwardItem::create([
            'inward_id' => $inward->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'rate' => $unitRate,
            'amount' => $quantity * $unitRate,
        ]);
    }

    private function createSaleWithItem(
        Company $company,
        Buyer $buyer,
        Product $product,
        Carbon $date,
        float $quantity
    ): void {
        $dateFormat = config('project.date_format', 'Y-m-d');

        $invoice = SalesInvoice::create([
            'invoice_number' => 'SI-' . uniqid(),
            'company_id' => $company->id,
            'buyer_id' => $buyer->id,
            'date' => $date->format($dateFormat),
            'currency_rate' => 1,
            'sub_total' => $quantity * 20,
            'tax_total' => 0,
            'tax_rate' => 0,
            'grand_total' => $quantity * 20,
            'is_taxable' => false,
            'payment_status' => 'pending',
        ]);

        SalesInvoiceItem::create([
            'sales_invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'rate' => 20,
            'amount' => $quantity * 20,
        ]);
    }
}
