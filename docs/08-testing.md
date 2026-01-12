# Testing Documentation

## Overview

The Laravel testing environment is configured with PHPUnit for backend testing, providing comprehensive test coverage for API endpoints, services, models, and business logic. The testing setup includes proper database management, authentication mocking, and external service mocking.

## Testing Environment Setup

### PHPUnit Configuration (`phpunit.xml`)

The testing environment is configured with:

```xml
<phpunit bootstrap="vendor/autoload.php" colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

### Test Environment Variables

```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="APP_KEY" value="base64:TEST_KEY_FOR_TESTING_ONLY"/>
    <env name="BCRYPT_ROUNDS" value="4"/>
    <env name="CACHE_DRIVER" value="array"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
    <env name="MAIL_MAILER" value="array"/>
    <env name="QUEUE_CONNECTION" value="sync"/>
    <env name="SESSION_DRIVER" value="array"/>
    <env name="JWT_SECRET" value="TEST_JWT_SECRET_FOR_TESTING_ONLY"/>
    <env name="STRIPE_KEY" value="pk_test_fake_key_for_testing"/>
    <env name="STRIPE_SECRET" value="sk_test_fake_secret_for_testing"/>
</php>
```

**Key Features:**
- **In-memory SQLite**: Fast database operations for testing
- **Array drivers**: Cache and session drivers for speed
- **Sync queue**: Immediate job processing
- **Array mailer**: Email testing without sending actual emails
- **Test credentials**: Safe JWT and Stripe keys for testing

## Base Test Classes

### Enhanced TestCase (`tests/TestCase.php`)

The base test case provides common functionality for all tests:

```php
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configure test environment
        config([
            'database.default' => 'testing',
            'cache.default' => 'array',
            'session.driver' => 'array',
            'queue.default' => 'sync',
        ]);
    }
}
```

#### Helper Methods

**Authentication Helpers:**
```php
protected function createAuthenticatedUser(array $attributes = []): User
protected function getAuthHeaders(User $user = null): array
protected function createTestCompany(User $user = null): Company
```

**Assertion Helpers:**
```php
protected function assertPaginationStructure($response): void
protected function assertErrorResponse($response, int $statusCode = 422): void
protected function assertSuccessResponse($response, int $statusCode = 200): void
```

**Service Mocking:**
```php
protected function mockExternalServices(): void
protected function seedBasicData(): void
```

## Test Categories

### 1. Feature Tests (`tests/Feature/`)

Feature tests verify complete API workflows and user interactions.

#### API Endpoint Testing Example (`SalesInvoiceTest.php`)

```php
class SalesInvoiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->user = User::factory()->create();
        $this->company = Company::factory()->create(['user_id' => $this->user->id]);
        $this->buyer = Buyer::factory()->create(['company_id' => $this->company->id]);
        // ... other test data
    }

    /** @test */
    public function it_can_list_sales_invoices()
    {
        // Create test data
        SalesInvoice::factory(3)->create([
            'company_id' => $this->company->id,
            'buyer_id' => $this->buyer->id,
        ]);

        // Make authenticated request
        $response = $this->getJson('/api/v1/sales-invoices', $this->authenticatedHeaders());

        // Assert response structure
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'invoice_number', 'date', 'grand_total']
                     ],
                     'links',
                     'meta'
                 ]);
    }
}
```

**Key Features:**
- **Database Refresh**: Clean database for each test
- **Factory Usage**: Generate realistic test data
- **Authentication**: JWT token-based API testing
- **Multi-tenant Testing**: Company-scoped data verification

#### Common Feature Test Patterns

**CRUD Operations Testing:**
```php
/** @test */
public function it_can_create_a_resource()
{
    $data = ['field' => 'value'];
    $response = $this->postJson('/api/v1/resource', $data, $this->getAuthHeaders());
    
    $response->assertStatus(201)
             ->assertJsonStructure(['data' => ['id', 'field']]);
    
    $this->assertDatabaseHas('resources', $data);
}

/** @test */
public function it_validates_required_fields()
{
    $response = $this->postJson('/api/v1/resource', [], $this->getAuthHeaders());
    
    $response->assertStatus(422)
             ->assertJsonValidationErrors(['required_field']);
}
```

**Authentication Testing:**
```php
/** @test */
public function it_requires_authentication()
{
    $response = $this->getJson('/api/v1/protected-endpoint');
    $response->assertStatus(401);
}
```

### 2. Unit Tests (`tests/Unit/`)

Unit tests focus on individual classes, methods, and business logic.

#### Service Testing Example (`OrderServiceTest.php`)

```php
class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calculates_sales_order_totals_correctly()
    {
        // Arrange
        $invoice = SalesInvoice::factory()->create();
        SalesInvoiceItem::factory()->create([
            'sales_invoice_id' => $invoice->id,
            'amount' => 100.00,
        ]);

        // Act
        OrderService::updateSalesOrderTotals($invoice);

        // Assert
        $invoice->refresh();
        $this->assertEquals(100.00, $invoice->sub_total);
    }

    /** @test */
    public function it_calculates_commission_correctly()
    {
        // Test commission calculation logic
        $invoice = SalesInvoice::factory()->create(['commission' => 10]);
        SalesInvoiceItem::factory()->create([
            'sales_invoice_id' => $invoice->id,
            'amount' => 110.00, // Includes 10% commission
        ]);

        OrderService::updateSalesOrderTotals($invoice, 'sales');

        $invoice->refresh();
        $expectedCommission = 110.00 - (110.00 / 1.10);
        $this->assertEquals(round($expectedCommission, 2), $invoice->commission_total);
    }
}
```

#### Model Testing

```php
class SalesInvoiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_required_relationships()
    {
        $invoice = SalesInvoice::factory()->create();
        
        $this->assertInstanceOf(Buyer::class, $invoice->buyer);
        $this->assertInstanceOf(Collection::class, $invoice->items);
        $this->assertInstanceOf(Company::class, $invoice->company);
    }

    /** @test */
    public function it_calculates_totals_correctly()
    {
        $invoice = SalesInvoice::factory()->create();
        
        // Test model calculations and business logic
        $this->assertTrue($invoice->grand_total >= $invoice->sub_total);
    }
}
```

## Test Data Management

### Database Factories

Laravel factories generate realistic test data:

```php
// SalesInvoiceFactory.php
public function definition()
{
    return [
        'invoice_number' => 'SI-' . $this->faker->unique()->numberBetween(1000, 9999),
        'date' => $this->faker->date(),
        'sub_total' => $subtotal = $this->faker->numberBetween(100, 1000),
        'tax_total' => $tax = $subtotal * 0.13,
        'grand_total' => $subtotal + $tax,
        'company_id' => Company::factory(),
        'buyer_id' => Buyer::factory(),
        'user_id' => User::factory(),
    ];
}
```

### Database Seeding for Tests

```php
protected function seedBasicData(): void
{
    // Seed essential reference data
    if (!Role::exists()) {
        $this->seed(RolesTableSeeder::class);
        $this->seed(PermissionsTableSeeder::class);
    }
    
    // Seed countries, states, currencies, etc.
    $this->seed(CountriesTableSeeder::class);
    $this->seed(StatesTableSeeder::class);
}
```

## Testing Patterns

### 1. Authentication Testing

```php
class AuthenticationTest extends TestCase
{
    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        
        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);
        
        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => ['user', 'api_token']]);
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrong-password'
        ]);
        
        $response->assertStatus(401);
    }
}
```

### 2. Multi-tenant Testing

```php
class MultiTenantTest extends TestCase
{
    /** @test */
    public function user_can_only_access_their_company_data()
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        
        $user = User::factory()->create();
        $user->companies()->attach($company1);
        
        $invoice1 = SalesInvoice::factory()->create(['company_id' => $company1->id]);
        $invoice2 = SalesInvoice::factory()->create(['company_id' => $company2->id]);
        
        $response = $this->getJson('/api/v1/sales-invoices', $this->getAuthHeaders($user));
        
        $invoiceIds = collect($response->json('data'))->pluck('id');
        $this->assertContains($invoice1->id, $invoiceIds);
        $this->assertNotContains($invoice2->id, $invoiceIds);
    }
}
```

### 3. Service Layer Testing

```php
class InventoryServiceTest extends TestCase
{
    /** @test */
    public function it_updates_stock_correctly_after_sale()
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();
        
        // Create initial inventory
        ProductInventory::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 100
        ]);
        
        // Create sales order that reduces inventory
        $salesOrder = SalesOrder::factory()->create();
        SalesOrderItem::factory()->create([
            'sales_order_id' => $salesOrder->id,
            'product_id' => $product->id,
            'quantity' => 10
        ]);
        
        // Execute service method
        InventoryService::updateStockBasedOnOrder($salesOrder, 'sales');
        
        // Assert stock was reduced
        $stock = ProductStock::where('product_id', $product->id)
                            ->where('warehouse_id', $warehouse->id)
                            ->first();
        
        $this->assertEquals(90, $stock->on_hand);
    }
}
```

### 4. API Validation Testing

```php
class ValidationTest extends TestCase
{
    /** @test */
    public function it_validates_sales_invoice_creation()
    {
        $response = $this->postJson('/api/v1/sales-invoices', [], $this->getAuthHeaders());
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'invoice_number',
                     'date',
                     'buyer_id',
                     'items'
                 ]);
    }

    /** @test */
    public function it_validates_invoice_items()
    {
        $data = [
            'invoice_number' => 'SI-001',
            'date' => now()->format('Y-m-d'),
            'buyer_id' => Buyer::factory()->create()->id,
            'items' => [
                ['product_id' => 999, 'quantity' => 0] // Invalid data
            ]
        ];
        
        $response = $this->postJson('/api/v1/sales-invoices', $data, $this->getAuthHeaders());
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'items.0.product_id',
                     'items.0.quantity'
                 ]);
    }
}
```

## Mock Testing

### External Service Mocking

```php
class ContractServiceTest extends TestCase
{
    /** @test */
    public function it_creates_stripe_subscription_for_contract()
    {
        // Mock Stripe service
        $this->mock(StripeService::class, function ($mock) {
            $mock->shouldReceive('createCustomer')
                 ->once()
                 ->andReturn('cus_test123');
                 
            $mock->shouldReceive('createSubscription')
                 ->once()
                 ->andReturn((object)['id' => 'sub_test123']);
        });
        
        $contract = Contract::factory()->create();
        
        $result = ContractService::createSubscription($contract);
        
        $this->assertEquals('sub_test123', $result->id);
    }
}
```

### Database Mocking

```php
class ReportServiceTest extends TestCase
{
    /** @test */
    public function it_generates_profit_loss_report()
    {
        // Create test data with specific dates
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();
        
        SalesInvoice::factory()->create([
            'date' => $startDate->addDays(5),
            'grand_total' => 1000
        ]);
        
        $report = ReportService::getProfitLoss();
        
        $this->assertArrayHasKey('revenue', $report);
        $this->assertArrayHasKey('expenses', $report);
        $this->assertArrayHasKey('net_profit', $report);
    }
}
```

## Performance Testing

### Database Query Testing

```php
class QueryPerformanceTest extends TestCase
{
    /** @test */
    public function it_loads_sales_invoices_efficiently()
    {
        // Create large dataset
        SalesInvoice::factory(100)->create();
        
        // Monitor query count
        DB::enableQueryLog();
        
        $response = $this->getJson('/api/v1/sales-invoices', $this->getAuthHeaders());
        
        $queries = DB::getQueryLog();
        
        // Assert reasonable query count
        $this->assertLessThan(5, count($queries));
        
        $response->assertStatus(200);
    }
}
```

## Running Tests

### Basic Test Commands

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run specific test file
php artisan test tests/Feature/SalesInvoiceTest.php

# Run with coverage
php artisan test --coverage

# Run specific test method
php artisan test --filter=it_can_create_a_sales_invoice
```

### Continuous Integration

Tests are designed to run in CI/CD environments:

```bash
# CI Test Command
php artisan test --parallel --coverage-clover=coverage.xml
```

## Test Coverage Goals

### Coverage Targets
- **Controllers**: 90%+ coverage of API endpoints
- **Services**: 95%+ coverage of business logic
- **Models**: 85%+ coverage of relationships and scopes
- **Overall**: 80%+ total application coverage

### Critical Areas
- Authentication and authorization
- Multi-tenant data isolation
- Financial calculations (taxes, commissions)
- Inventory management
- API validation
- External service integrations

This comprehensive testing strategy ensures reliable application behavior, maintains code quality, and provides confidence for deployments and refactoring efforts.