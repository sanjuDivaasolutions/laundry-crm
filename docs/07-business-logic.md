# Business Logic & Services Documentation

## Overview

The business logic layer is implemented through dedicated service classes that encapsulate complex business operations, calculations, and integrations. These services provide a clean abstraction between controllers and models, ensuring maintainable and testable code.

## Service Architecture

### Core Principles
- **Single Responsibility**: Each service handles one domain area
- **Static Methods**: Most services use static methods for utility functions
- **Database Transactions**: Complex operations are wrapped in transactions
- **Error Handling**: Comprehensive exception handling and rollback mechanisms
- **Multi-tenant Aware**: All services respect company scoping

## Core Services

### 1. AuthService (`AuthService.php`)

**Purpose**: Manages authentication, user sessions, and security

**Key Methods:**
```php
class AuthService
{
    public static function authenticate($credentials): bool
    public static function generateToken($user): string
    public static function revokeToken($token): void
    public static function refreshToken($token): string
    public static function validatePermissions($user, $permission): bool
}
```

**Features:**
- JWT token management
- Permission validation
- Multi-factor authentication support
- Session management
- Password security enforcement

### 2. CompanyService (`CompanyService.php`)

**Purpose**: Multi-tenant company management and operations

**Key Methods:**
```php
class CompanyService
{
    public static function getCompanyCode($companyName): string
    public static function getDefaultCompanyEntry(): Company
    public static function switchCompanyContext($companyId): void
    public static function validateCompanyAccess($user, $company): bool
}
```

**Business Logic:**
- **Company Code Generation**: Automatic generation of unique company codes
  ```php
  // Example: "Acme Corporation" → "AC-001"
  // If "AC-001" exists, generates "AC-002"
  $initials = strtoupper($words[0][0] . $words[1][0]);
  $sequence = $this->getNextSequence($initials);
  return $initials . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
  ```
- **Company Context Management**: Handles multi-tenant switching
- **Default Company Resolution**: Determines user's primary company

### 3. InventoryService (`InventoryService.php`)

**Purpose**: Comprehensive inventory management and stock tracking

**Key Methods:**
```php
class InventoryService
{
    public static function updateProductStockInWarehouse($productId, $warehouseId): void
    public static function updateProductStockAcrossAllWarehouses($productId): void
    public static function updateStockBasedOnOrder($order, $orderType): void
    public static function adjustInventory($productId, $warehouseId, $adjustment): void
    public static function getAvailableStock($productId, $warehouseId): int
    public static function reserveStock($productId, $warehouseId, $quantity): bool
    public static function releaseReservedStock($productId, $warehouseId, $quantity): void
}
```

**Business Logic:**
- **Multi-level Stock Tracking**: Product → Warehouse → Shelf → Batch
- **Real-time Stock Updates**: Automatic stock recalculation on transactions
- **Stock Reservations**: Reserve inventory for pending orders
- **Batch Management**: FIFO/LIFO inventory movement
- **Multi-warehouse Operations**: Stock transfers and balancing

**Stock Calculation Flow:**
```php
// 1. Calculate total quantity from product inventories
$quantity = ProductInventory::where('product_id', $productId)
    ->where('warehouse_id', $warehouseId)
    ->sum('quantity');

// 2. Update product stock record
$stock = ProductStock::updateOrCreate([
    'product_id' => $productId,
    'warehouse_id' => $warehouseId
], [
    'on_hand' => $quantity,
    'available' => $quantity - $reserved,
    'modified' => now()
]);

// 3. Update shelf-level stocks
foreach ($shelves as $shelf) {
    ProductStockShelf::updateOrCreate([
        'product_id' => $productId,
        'shelf_id' => $shelf->id
    ], [
        'quantity' => $shelf->inventory_quantity
    ]);
}
```

### 4. InvoiceService (`InvoiceService.php`)

**Purpose**: Invoice processing, tax calculations, and payment management

**Key Methods:**
```php
class InvoiceService
{
    public static function calculateTaxes($invoice): array
    public static function updateTaxes($request, $invoice, $invoiceType): void
    public static function setupTaxes($invoice): void
    public static function getItemsObject($request): array
    public static function generateInvoiceNumber($type, $companyId): string
    public static function processPayment($invoice, $paymentData): Payment
}
```

**Tax Calculation Engine:**
```php
// Multi-tier tax calculation
public static function calculateTaxes($invoice): array
{
    $taxDetails = [];
    $taxableAmount = $invoice->sub_total;
    
    // Get applicable tax rates based on:
    // - Customer location (state/province)
    // - Product tax class
    // - Company tax settings
    
    $taxRates = TaxRate::getApplicableRates(
        $invoice->state_id,
        $invoice->buyer->tax_class_id ?? null,
        $invoice->company_id
    );
    
    foreach ($taxRates as $taxRate) {
        $taxAmount = $taxableAmount * ($taxRate->rate / 100);
        $taxDetails[] = [
            'tax_rate_id' => $taxRate->id,
            'amount' => $taxAmount,
            'priority' => $taxRate->priority
        ];
        
        // Compound tax calculation if applicable
        if ($taxRate->compound) {
            $taxableAmount += $taxAmount;
        }
    }
    
    return $taxDetails;
}
```

**Invoice Numbering System:**
- Company-specific prefixes
- Sequential numbering with zero-padding
- Separate sequences for different invoice types
- Support for financial year resets

### 5. OrderService (`OrderService.php`)

**Purpose**: Order processing, total calculations, and commission management

**Key Methods:**
```php
class OrderService
{
    public static function updateSalesOrderTotals($order, $type = 'sales'): void
    public static function updatePurchaseOrderTotals($order): void
    public static function calculateCommission($order): float
    public static function processOrderWorkflow($order, $action): void
}
```

**Commission Calculation Logic:**
```php
public static function updateSalesOrderTotals($order, $type = 'sales'): void
{
    // Calculate base totals
    $subTotal = $order->items()->sum('amount');
    $taxTotal = $order->taxes()->sum('amount');
    
    // Commission calculation (reverse calculation)
    if ($type === 'sales' && $order->commission > 0) {
        // If sub_total includes commission:
        // base_amount = sub_total / (1 + commission/100)
        // commission_total = sub_total - base_amount
        $baseAmount = $subTotal / (1 + ($order->commission / 100));
        $commissionTotal = round($subTotal - $baseAmount, 2);
        $order->commission_total = $commissionTotal;
    }
    
    $order->update([
        'sub_total' => $subTotal,
        'tax_total' => $taxTotal,
        'grand_total' => $subTotal + $taxTotal
    ]);
}
```

### 6. ReportService (`ReportService.php`)

**Purpose**: Business intelligence, analytics, and report generation

**Key Methods:**
```php
class ReportService
{
    public static function getProfitLoss(): array
    public static function getSalesByMonth(): Collection
    public static function getSalesByProduct(): Collection
    public static function getInventorySummary(): array
    public static function getCustomerAnalytics(): array
    public static function generateDashboardMetrics(): array
}
```

**Profit & Loss Calculation:**
```php
public static function getProfitLoss(): array
{
    $filters = self::getFilters(); // Date range, company, etc.
    
    // Revenue calculation
    $revenue = SalesInvoice::query()
        ->when($filters['company_id'], fn($q) => $q->whereIn('company_id', $filters['company_id']))
        ->when($filters['date'], fn($q) => $q->whereBetween('date', $filters['date']))
        ->selectRaw('sum(grand_total / currency_rate) as total')
        ->first()->total ?? 0;
    
    // Cost of Goods Sold
    $cogs = Inward::query()
        ->when($filters['company_id'], fn($q) => $q->whereIn('company_id', $filters['company_id']))
        ->when($filters['date'], fn($q) => $q->whereBetween('date', $filters['date']))
        ->sum('grand_total');
    
    // Operating Expenses by Category
    $expenses = Expense::query()
        ->with('expenseType:id,name')
        ->when($filters['company_id'], fn($q) => $q->whereIn('company_id', $filters['company_id']))
        ->when($filters['date'], fn($q) => $q->whereBetween('date', $filters['date']))
        ->selectRaw('expense_type_id, sum(amount) as total')
        ->groupBy('expense_type_id')
        ->get();
    
    return [
        'revenue' => $revenue,
        'cogs' => $cogs,
        'gross_profit' => $revenue - $cogs,
        'expenses' => $expenses,
        'net_profit' => $revenue - $cogs - $expenses->sum('total')
    ];
}
```

### 7. StripeService (`StripeService.php`)

**Purpose**: Payment processing and subscription management via Stripe

**Key Methods:**
```php
class StripeService
{
    public static function createCustomer($buyer): string
    public static function createSubscription($contract): Subscription
    public static function processPayment($amount, $paymentMethod): PaymentIntent
    public static function handleWebhook($request): void
    public static function cancelSubscription($subscriptionId): void
    public static function updateSubscription($subscriptionId, $items): void
}
```

**Contract Billing Integration:**
- Automatic Stripe product/price creation for contract items
- Subscription management with prorated billing
- Webhook handling for payment confirmations
- Invoice generation triggered by successful payments

### 8. DatabaseService (`DatabaseService.php`)

**Purpose**: Database operations, transactions, and data integrity

**Key Methods:**
```php
class DatabaseService
{
    public static function executeTransaction(callable $callback): mixed
    public static function backupDatabase(): string
    public static function restoreDatabase($backupFile): bool
    public static function optimizeTables(): void
    public static function cleanupExpiredRecords(): int
}
```

**Transaction Management:**
```php
public static function executeTransaction(callable $callback): mixed
{
    DB::beginTransaction();
    try {
        $result = $callback();
        DB::commit();
        return $result;
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

### 9. ImportService (`ImportService.php`)

**Purpose**: Data import processing and validation

**Key Features:**
- Excel/CSV file processing
- Data validation and sanitization
- Batch processing for large datasets
- Error reporting with line-by-line details
- Template validation

**Supported Import Types:**
- Products with inventory data
- Sales invoices with items
- Purchase invoices with items
- Bank transactions
- Customer/supplier data

### 10. MediaService (`MediaService.php`)

**Purpose**: File upload, media management, and image processing

**Integration with Spatie Media Library:**
- Multiple file format support
- Image resizing and optimization
- Document storage and retrieval
- Media collection management
- CDN integration support

## Utility Services

### 11. LanguageService (`LanguageService.php`)

**Purpose**: Multi-language support and translation management

**Features:**
- Dynamic translation loading
- Language term synchronization
- Missing translation detection
- Export/import of translation files

### 12. UtilityService (`UtilityService.php`)

**Purpose**: Common utility functions and helpers

**Common Operations:**
- Date formatting and parsing
- Currency conversion
- Number formatting
- String manipulation
- Validation helpers

### 13. QueryService (`QueryService.php`)

**Purpose**: Advanced search and filtering operations

**Features:**
- Dynamic query building
- Search across multiple models
- Relationship-based filtering
- Performance optimization for complex queries

## Service Integration Patterns

### Controller Service Usage
```php
class SalesInvoiceApiController extends Controller
{
    public function store(StoreSalesInvoiceRequest $request)
    {
        $salesInvoice = null;
        
        DatabaseService::executeTransaction(function () use ($request, &$salesInvoice) {
            // Create invoice
            $salesInvoice = SalesInvoice::create($request->validated());
            
            // Update related data
            $this->updateItems($request, $salesInvoice);
            $this->updateTaxes($request, $salesInvoice);
            
            // Calculate totals
            OrderService::updateSalesOrderTotals($salesInvoice);
            
            // Update inventory
            InventoryService::updateStockBasedOnOrder($salesInvoice, 'sales');
        });
        
        return new SalesInvoiceResource($salesInvoice);
    }
    
    private function updateTaxes($request, $invoice)
    {
        InvoiceService::updateTaxes($request, $invoice, SalesInvoice::class);
    }
}
```

### Service Dependencies
Services are designed to work together in complex workflows:

1. **Order Processing Flow:**
   - `OrderService` → `InvoiceService` → `InventoryService` → `ReportService`

2. **Contract Management:**
   - `ContractService` → `StripeService` → `InvoiceService` → `DatabaseService`

3. **Import Operations:**
   - `ImportService` → `ProductService` → `InventoryService` → `MediaService`

## Performance Considerations

### Caching Strategy
- **ModelCacheService**: Caches frequently accessed reference data
- **Query optimization**: Service methods include proper eager loading
- **Batch operations**: Bulk updates for inventory and calculations

### Background Processing
- **Queue Integration**: Long-running operations use Laravel queues
- **Event-driven updates**: Inventory updates triggered by model events
- **Async reporting**: Large reports generated asynchronously

This service layer architecture provides a robust foundation for complex business operations while maintaining code organization, testability, and performance.