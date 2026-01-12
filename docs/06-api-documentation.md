# API Documentation

## Overview

The application provides a comprehensive RESTful API built on Laravel 9, following standard REST conventions with JWT-based authentication. All API endpoints are prefixed with `/api/v1` and use JSON for request and response data.

## Authentication

### JWT Token Authentication
The API uses JWT (JSON Web Token) authentication with the `php-open-source-saver/jwt-auth` package.

#### Authentication Flow
```http
POST /api/v1/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com",
            "api_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
            "settings": {}
        }
    }
}
```

#### Token Verification
```http
GET /api/v1/verify
Authorization: Bearer {token}
```

### Middleware Groups

#### Public Routes
- **Prefix**: `/api/v1`
- **Middleware**: `[]` (no authentication required)
- **Routes**: Login, password reset, locales

#### Protected Routes  
- **Prefix**: `/api/v1`
- **Middleware**: `['jwt.admin.verify']`
- **Routes**: All business operations

## API Structure

### Standard Resource Routes

Each business entity follows Laravel's resource route conventions:

```http
GET    /api/v1/{resource}           # Index - List all records
POST   /api/v1/{resource}           # Store - Create new record
GET    /api/v1/{resource}/create    # Create - Get form defaults
GET    /api/v1/{resource}/{id}      # Show - Get single record
GET    /api/v1/{resource}/{id}/edit # Edit - Get record for editing
PUT    /api/v1/{resource}/{id}      # Update - Update existing record
DELETE /api/v1/{resource}/{id}      # Destroy - Delete record
```

### Additional Route Patterns

#### Export Routes
```http
GET /api/v1/{resource}-csv         # Export to CSV
GET /api/v1/{resource}-pdf         # Export to PDF
```

#### Single PDF Generation
```http
GET /api/v1/{resource}-single-pdf/{id}  # Generate PDF for single record
```

## Core Business Endpoints

### 1. User Management

#### Users (`/api/v1/users`)
- **Resource**: Complete CRUD operations
- **Additional**: 
  - `POST /api/v1/user/settings/{user}` - Update user settings
  - `POST /api/v1/user/setting/update` - Update single setting
  - `POST /api/v1/user/language/{user}` - Update user language

#### Roles (`/api/v1/roles`)
- **Resource**: Complete CRUD operations
- **Export**: CSV export available

#### Permissions (`/api/v1/permissions`)
- **Resource**: Complete CRUD operations
- **Features**: Permission groups and role associations

### 2. Geographic Data

#### Countries (`/api/v1/countries`)
- **Resource**: Complete CRUD operations
- **Relationships**: States and cities

#### States (`/api/v1/states`)
- **Resource**: Complete CRUD operations
- **Relationships**: Belongs to country, has cities

#### Cities (`/api/v1/cities`)
- **Resource**: Complete CRUD operations
- **Relationships**: Belongs to state

### 3. Customer Management

#### Buyers (`/api/v1/buyers`)
```http
GET    /api/v1/buyers              # List customers
POST   /api/v1/buyers              # Create customer
GET    /api/v1/buyers/{id}         # Show customer details
PUT    /api/v1/buyers/{id}         # Update customer
DELETE /api/v1/buyers/{id}         # Delete customer
GET    /api/v1/buyers-csv          # Export customers to CSV
```

**Response Structure:**
```json
{
    "data": {
        "id": 1,
        "code": "BUY001",
        "display_name": "John Doe",
        "name": "John Doe",
        "active": true,
        "email": "john@example.com",
        "phone": "+1234567890",
        "billing_address": {...},
        "shipping_address": {...}
    }
}
```

#### Suppliers (`/api/v1/suppliers`)
- **Resource**: Complete CRUD operations  
- **Export**: CSV export available
- **Features**: Multi-address support

### 4. Product Management

#### Products (`/api/v1/products`)
```http
GET    /api/v1/products                    # List products
POST   /api/v1/products                    # Create product
GET    /api/v1/products/{id}               # Show product
PUT    /api/v1/products/{id}               # Update product
DELETE /api/v1/products/{id}               # Delete product
POST   /api/v1/products/media              # Upload product media
GET    /api/v1/product-transactions/{id}    # Get inventory transactions
```

#### Services (`/api/v1/services`)
- **Custom Routes**: Specialized service management
- **Features**: Service-specific pricing and features

#### Categories (`/api/v1/categories`)
- **Resource**: Product categorization
- **Export**: CSV export available

#### Features (`/api/v1/features`)
- **Resource**: Product feature management
- **Relationships**: Product-feature associations

### 5. Inventory Management

#### Warehouses (`/api/v1/warehouses`)
- **Resource**: Complete CRUD operations
- **Relationships**: Shelves and inventory

#### Shelves (`/api/v1/shelves`)  
- **Resource**: Shelf management within warehouses
- **Features**: Location-based inventory tracking

#### Inventory Adjustments (`/api/v1/inventory-adjustments`)
- **Resource**: Stock adjustment operations
- **Features**: Reason tracking and audit trail

### 6. Purchase Operations

#### Purchase Orders (`/api/v1/purchase-orders`)
```http
GET    /api/v1/purchase-orders                        # List purchase orders
POST   /api/v1/purchase-orders                        # Create purchase order
GET    /api/v1/purchase-orders/{id}                   # Show purchase order
PUT    /api/v1/purchase-orders/{id}                   # Update purchase order
DELETE /api/v1/purchase-orders/{id}                   # Delete purchase order
GET    /api/v1/purchase-orders-single-pdf/{id}        # Generate PDF
GET    /api/v1/purchase-order-invoice/{id}            # Convert to invoice
```

#### Purchase Invoices (`/api/v1/purchase-invoices`)
- **Resource**: Complete CRUD operations
- **PDF**: Single invoice PDF generation
- **Features**: Tax calculation and payment tracking

#### Inwards (`/api/v1/inwards`)
- **Resource**: Goods receipt management
- **Features**: Batch tracking and shelf assignment
- **PDF**: Inward receipt PDF generation

### 7. Sales Operations

#### Quotations (`/api/v1/quotations`)
```http
GET    /api/v1/quotations                    # List quotations  
POST   /api/v1/quotations                    # Create quotation
GET    /api/v1/quotations/{id}               # Show quotation
PUT    /api/v1/quotations/{id}               # Update quotation
DELETE /api/v1/quotations/{id}               # Delete quotation
GET    /api/v1/quotations-single-pdf/{id}    # Generate PDF
POST   /api/v1/quotations-mark-status/{id}   # Update status
```

#### Sales Orders (`/api/v1/sales-orders`)
- **Resource**: Complete CRUD operations
- **PDF**: Order confirmation PDF
- **Conversion**: `GET /api/v1/sales-order-invoice/{id}` - Convert to invoice

#### Sales Invoices (`/api/v1/sales-invoices`)
```http
GET    /api/v1/sales-invoices                 # List invoices
POST   /api/v1/sales-invoices                 # Create invoice  
GET    /api/v1/sales-invoices/{id}            # Show invoice
PUT    /api/v1/sales-invoices/{id}            # Update invoice
DELETE /api/v1/sales-invoices/{id}            # Delete invoice
GET    /api/v1/sales-invoices-single-pdf/{id} # Generate PDF
```

**Invoice Response:**
```json
{
    "data": {
        "id": 1,
        "invoice_number": "SI001",
        "date": "2025-01-01",
        "due_date": "2025-01-31",
        "sub_total": 1000.00,
        "tax_total": 130.00,
        "grand_total": 1130.00,
        "buyer": {...},
        "items": [...],
        "taxes": [...]
    }
}
```

#### Service Invoices (`/api/v1/service-invoices`)
- **Custom Routes**: Service billing management
- **Features**: Service-specific tax handling

### 8. Contract Management

#### Contracts (`/api/v1/contracts`)
```http
GET    /api/v1/contracts                              # List contracts
POST   /api/v1/contracts                              # Create contract
GET    /api/v1/contracts/{id}                         # Show contract
PUT    /api/v1/contracts/{id}                         # Update contract  
DELETE /api/v1/contracts/{id}                         # Delete contract
POST   /api/v1/contracts-send-payment-link/{id}       # Send payment link
```

#### Contract Invoices (`/api/v1/contract-invoices`)
- **Specialized Routes**: Contract billing
- **Features**: 
  - `GET /api/v1/contract-invoices/{contract}` - List contract invoices
  - `GET /api/v1/contract-invoice-generate/{contract}` - Generate invoice

#### Contract Terms (`/api/v1/contract-terms`)
- **Resource**: Terms and conditions management

### 9. Financial Management

#### Payments (`/api/v1/payments`)
```http
GET    /api/v1/payments              # List payments
POST   /api/v1/payments              # Record payment
GET    /api/v1/payments/{id}         # Show payment
PUT    /api/v1/payments/{id}         # Update payment
DELETE /api/v1/payments/{id}         # Delete payment
GET    /api/v1/payments/{type}/{id}  # Get payments for specific record
```

#### Expenses (`/api/v1/expenses`)
- **Resource**: Expense management
- **Features**: Expense type categorization

#### Payment Terms (`/api/v1/payment-terms`)
- **Resource**: Payment terms configuration

#### Payment Modes (`/api/v1/payment-modes`)
- **Resource**: Payment method configuration

### 10. Logistics & Shipping

#### Packages (`/api/v1/packages`)
```http
GET    /api/v1/packages                           # List packages
POST   /api/v1/packages                           # Create package
GET    /api/v1/packages/{id}                      # Show package
PUT    /api/v1/packages/{id}                      # Update package
DELETE /api/v1/packages/{id}                      # Delete package
GET    /api/v1/packages-single-pdf/{id}           # Generate packing slip
GET    /api/v1/sales-invoice-items/{invoice_id}   # Get items for packaging
```

#### Shipments (`/api/v1/shipments`)
- **Resource**: Shipment tracking
- **Features**: Tracking number management

### 11. Search & Options API

#### Universal Search (`/api/v1/query/{type}`)
```http
GET /api/v1/query/products?s=search_term
GET /api/v1/query/buyers?s=customer_name
```

#### Options API (`/api/v1/options/{type}`)
```http
GET /api/v1/options/buyers           # Get buyer options for dropdowns
GET /api/v1/options/products         # Get product options
GET /api/v1/bulk-options/{types}     # Get multiple option types
```

**Response Structure:**
```json
{
    "data": [
        {
            "id": 1,
            "text": "Display Text",
            "value": "option_value"
        }
    ]
}
```

### 12. Reporting API

#### Reports (`/api/v1/reports/{type}`)
```http
GET /api/v1/reports/sales-by-month       # Sales report by month
GET /api/v1/reports/profit-loss          # Profit & loss report
GET /api/v1/reports/inventory-summary    # Inventory report
GET /api/v1/reports/csv/{type}           # Export report to CSV
GET /api/v1/reports/pdf/{type}           # Export report to PDF
```

### 13. Dashboard API

#### Dashboard Data (`/api/v1/dashboard-data`)
```http
GET /api/v1/dashboard-data
```

**Response:**
```json
{
    "data": {
        "sales_summary": {...},
        "recent_orders": [...],
        "inventory_alerts": [...],
        "financial_metrics": {...}
    }
}
```

### 14. Import/Export API

#### Data Import (`/api/v1/import/{type}/{id?}`)
```http
POST /api/v1/import/products
POST /api/v1/import/sales-invoices
POST /api/v1/import/purchase-invoices
```

#### Media Upload (`/api/v1/media-upload`)
```http
POST /api/v1/media-upload
Content-Type: multipart/form-data

{
    "file": [uploaded file],
    "type": "product",
    "model_id": 123
}
```

## Response Formats

### Standard Success Response
```json
{
    "status": "success",
    "data": {...}
}
```

### Collection Response (with pagination)
```json
{
    "data": [...],
    "links": {
        "first": "...",
        "last": "...",
        "prev": null,
        "next": "..."
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 5,
        "per_page": 10,
        "to": 10,
        "total": 50
    }
}
```

### Error Response
```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "field_name": ["Error message"]
    }
}
```

### Advanced Filtering

All list endpoints support advanced filtering via query parameters:

```http
GET /api/v1/sales-invoices?sort=date&order=desc&limit=25&s=search_term
GET /api/v1/products?f[0][column]=name&f[0][operator]=contains&f[0][query_1]=search
```

**Filter Parameters:**
- `sort`: Column to sort by
- `order`: Sort direction (asc/desc)
- `limit`: Records per page
- `s`: Global search term
- `f`: Array of filter objects

**Filter Operators:**
- `contains`: Text contains
- `equals`: Exact match
- `in`: Value in list
- `between`: Range query
- `date_range`: Date range
- `check_bool`: Boolean check

## Rate Limiting

- **Authentication endpoints**: 5 attempts per minute per IP
- **General API**: 60 requests per minute per authenticated user
- **File uploads**: 10 uploads per minute per user

## Error Codes

- **200**: Success
- **201**: Created
- **202**: Accepted  
- **400**: Bad Request
- **401**: Unauthorized
- **403**: Forbidden
- **404**: Not Found
- **422**: Validation Error
- **429**: Rate Limited
- **500**: Server Error

## Webhooks

### Stripe Webhooks (`/api/v1/stripe-webhook`)
```http
POST /api/v1/stripe-webhook
Content-Type: application/json
Stripe-Signature: signature

{
    "type": "payment_intent.succeeded",
    "data": {...}
}
```

## Development & Testing Routes

**Note**: These routes are for development/maintenance and should be restricted in production:

```http
GET /api/v1/storage-link              # Create storage symlink
GET /api/v1/optimize                  # Clear application caches
GET /api/v1/reinstall-permissions     # Reinstall permissions system
GET /api/v1/update-language-terms     # Update translations
```

This API provides comprehensive functionality for managing all aspects of the sign rental ERP system with consistent patterns, proper authentication, and extensive filtering capabilities.