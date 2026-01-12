# Laravel Backend Architecture

## Overview

The Laravel backend follows a layered architecture with clear separation of concerns, implementing modern patterns for maintainability and scalability. The system uses Laravel 9 with PHP 8.1+ and implements a multi-tenant structure with company scoping.

## Architecture Layers

### 1. Controllers Layer (`app/Http/Controllers/`)

#### API Controllers Structure
All API controllers are located in `app/Http/Controllers/Admin/` and follow a consistent pattern:

- **Base Controller**: `Controller.php` provides common functionality
- **Authentication Controllers**: `Auth/` directory contains login, reset password, and verification controllers
- **Business Module Controllers**: Each entity has its own API controller (e.g., `SalesInvoiceApiController.php`)

#### Controller Pattern
Controllers use several traits for consistent functionality:

```php
use SearchFilters;      // Advanced filtering capabilities
use ControllerRequest;  // Common request handling
use ExportRequest;      // Export functionality (CSV, PDF)
```

#### Key Controller Features
- **Gate-based Authorization**: Each method checks permissions using Laravel Gates
- **Resource Collections**: Consistent API responses using Eloquent Resources
- **Transaction Handling**: Database transactions for data integrity
- **Advanced Filtering**: Built-in filtering and pagination
- **Export Capabilities**: PDF and CSV generation

Example Controller Structure (`SalesInvoiceApiController.php`):
```php
class SalesInvoiceApiController extends Controller
{
    protected $className = SalesInvoice::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = SalesInvoiceResource::class;
    protected $fetcher = 'advancedFilter';
    protected $filterMethods = ['index', 'getCsv', 'getPdf'];
    
    // Standard CRUD operations
    public function index()     // List with filtering
    public function create()    // Form defaults
    public function store()     // Create new record
    public function show()      // View single record
    public function edit()      // Edit form data
    public function update()    // Update record
    public function destroy()   // Delete record
}
```

### 2. Models Layer (`app/Models/`)

#### Model Architecture
Models implement several key patterns and traits:

- **HasAdvancedFilter**: Advanced filtering and sorting capabilities
- **CompanyScopeTrait**: Multi-tenant company scoping
- **HasFactory**: Laravel factories for testing
- **Relationship Definitions**: Eloquent relationships
- **Attribute Casting**: Type casting for data consistency
- **Custom Accessors/Mutators**: Data transformation

#### Key Model Features

**Advanced Filtering System**:
```php
trait HasAdvancedFilter
{
    protected $orderable = ['id', 'name', 'created_at'];    // Sortable columns
    protected $filterable = ['name', 'status', 'type'];     // Filterable columns
    
    public function scopeAdvancedFilter($query)
    {
        // Handles sorting, filtering, pagination
        // Supports operators: contains, equals, in, between, date_range
    }
}
```

**Multi-tenant Scoping**:
```php
trait CompanyScopeTrait
{
    protected static function booted()
    {
        // Automatically scope queries by company_id
        static::addGlobalScope(new CompanyScope());
    }
}
```

**Example Model** (`SalesInvoice.php`):
```php
class SalesInvoice extends Model
{
    use HasAdvancedFilter, HasFactory, CompanyScopeTrait;
    
    // Define filterable and orderable columns
    protected $orderable = ['id', 'invoice_number', 'date', 'grand_total'];
    protected $filterable = ['invoice_number', 'buyer.code', 'date'];
    
    // Type casting
    protected $casts = [
        'date' => 'date',
        'is_taxable' => 'boolean',
        'sub_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];
    
    // Relationships
    public function buyer() { return $this->belongsTo(Buyer::class); }
    public function items() { return $this->hasMany(SalesInvoiceItem::class); }
    public function taxes() { return $this->morphMany(OrderTaxDetail::class, 'taxable'); }
}
```

### 3. Services Layer (`app/Services/`)

Business logic is encapsulated in service classes for reusability and maintainability:

#### Key Services

**OrderService** (`OrderService.php`):
- Handles order total calculations
- Commission calculations
- Tax computations
- Supports both sales and purchase orders

```php
class OrderService
{
    public static function updateSalesOrderTotals($obj, $type = 'sales')
    {
        // Calculate sub totals, tax totals, commission totals
        // Update order totals with proper rounding
    }
}
```

**InventoryService**:
- Stock level management
- Warehouse operations
- Shelf-level tracking
- Stock adjustments

**InvoiceService**:
- Invoice processing
- Tax calculations
- PDF generation
- Payment processing

**CompanyService**:
- Multi-tenant company management
- Default company selection
- Company-scoped operations

**ReportService**:
- Business intelligence
- Report generation
- Data analytics

### 4. Resources Layer (`app/Http/Resources/`)

API responses are standardized using Eloquent Resources:

#### Resource Types
- **Single Resources**: For individual model responses
- **Collection Resources**: For paginated lists
- **List Resources**: Optimized for data tables

#### Example Resource Structure
```php
class SalesInvoiceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'date' => $this->date,
            'grand_total' => $this->grand_total,
            'buyer' => $this->whenLoaded('buyer'),
            'items' => $this->whenLoaded('items'),
        ];
    }
}
```

### 5. Request Validation (`app/Http/Requests/`)

Form validation is handled by dedicated request classes:

```php
class StoreSalesInvoiceRequest extends FormRequest
{
    public function rules()
    {
        return [
            'invoice_number' => 'required|unique:sales_invoices',
            'date' => 'required|date',
            'buyer_id' => 'required|exists:buyers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
        ];
    }
}
```

### 6. Traits (`app/Traits/`)

Reusable functionality is implemented as traits:

#### Key Traits

**SearchFilters**:
- Advanced search capabilities
- Date range filtering
- Multi-field search

**ControllerRequest**:
- Common controller methods
- Request processing
- Response formatting

**ExportRequest**:
- CSV export functionality
- PDF generation
- Excel export

**CompanyScopeTrait**:
- Multi-tenant scoping
- Company-based data isolation

### 7. Scopes (`app/Scopes/`)

Global query scopes for consistent data filtering:

- **CompanyScope**: Automatically filter by company_id
- **DepartmentScope**: Department-based filtering
- **BuyerScope**: Customer-specific data access

## Database Architecture

### Migration System
- Structured migration files with proper foreign key constraints
- Relationship tables with pivot functionality
- Index optimization for frequently queried columns

### Key Database Features
- **Multi-company Support**: Company-scoped data isolation
- **Soft Deletes**: Maintains data integrity
- **Audit Trail**: Activity logging for changes
- **Batch Tracking**: Inventory batch management
- **Polymorphic Relationships**: Flexible entity relationships

## Authentication & Authorization

### JWT Authentication
- Token-based authentication using `php-open-source-saver/jwt-auth`
- Refresh token support
- Role-based access control

### Permission System
- Gate-based authorization
- Role and permission management
- Company-scoped permissions

## Configuration

### Key Config Files
- `config/project.php`: Project-specific settings
- `config/system.php`: System defaults
- `config/inventory.php`: Inventory management settings
- `config/date-scopes.php`: Date filtering configurations

## Error Handling & Logging

### Exception Handling
- Custom exception handlers
- API-friendly error responses
- Proper HTTP status codes

### Logging
- Clockwork integration for debugging
- Activity logging for audit trails
- Error tracking and monitoring

## Performance Optimizations

### Model Caching
- Implemented using `genealabs/laravel-model-caching`
- Automatic cache invalidation
- Query result caching

### Query Optimization
- Eager loading relationships
- Proper indexing
- Query scoping

### Asset Optimization
- Vite build system
- Asset versioning
- CDN support

## Security Features

### Data Protection
- SQL injection prevention
- XSS protection
- CSRF protection
- Input validation

### Access Control
- Role-based permissions
- Company data isolation
- API rate limiting

This Laravel backend provides a solid foundation for the ERP system with modern patterns, comprehensive functionality, and excellent maintainability.